<?php namespace AdamWathan\Facktory;

use AdamWathan\Facktory\Strategy\Build as BuildStrategy;
use AdamWathan\Facktory\Strategy\Create as CreateStrategy;
use AdamWathan\Facktory\Relationship\BelongsTo;
use AdamWathan\Facktory\Relationship\HasMany;
use AdamWathan\Facktory\Relationship\HasOne;

class Factory
{
    protected $model;
    protected $attributes;
    protected $coordinator;
    protected $sequence = 1;

    public function __construct($model, $attributes = [])
    {
        $this->model = $model;
        $this->attributes = $attributes;
    }

    public static function make($model, $attributes = [])
    {
        return new static($model, $attributes);
    }

    public function setCoordinator($coordinator)
    {
        $this->coordinator = $coordinator;
    }

    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    protected function getAttribute($key)
    {
        if (is_callable($this->attributes[$key])) {
            return $this->attributes[$key]($this, $this->sequence);
        }
        return $this->attributes[$key];
    }

    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    protected function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function build($override_attributes)
    {
        $strategy = BuildStrategy::make($this->model, $this->sequence);
        return $this->newInstance($strategy, $override_attributes);
    }

    public function create($override_attributes)
    {
        $strategy = CreateStrategy::make($this->model, $this->sequence);
        return $this->newInstance($strategy, $override_attributes);
    }

    protected function newInstance($strategy, $override_attributes)
    {
        $strategy->attributes($this->mergeAttributes($override_attributes));
        $instance = $strategy->newInstance();
        $this->sequence++;
        return $instance;
    }

    protected function mergeAttributes($override_attributes)
    {
        if (is_callable($override_attributes)) {
            $override_attributes = $this->getOverridesFromClosure($override_attributes);
        }
        return array_merge($this->attributes, $override_attributes);
    }

    protected function getOverridesFromClosure($closure)
    {
        $that = clone $this;
        $closure($that);
        return $that->attributes;
    }

    public function buildList($count, $override_attributes)
    {
        $override_attributes = $this->expandAttributesForList($override_attributes, $count);
        return array_map(function($i) use ($override_attributes) {
            return $this->build($override_attributes[$i]);
        }, range(0, $count - 1));
    }

    protected function expandAttributesForList($attributes, $count)
    {
        return array_map(function($i) use ($attributes) {
            return $this->extractAttributesForIndex($i, $attributes);
        }, range(0, $count - 1));
    }

    protected function extractAttributesForIndex($i, $attributes)
    {
        return array_map(function($value) use ($i) {
            return is_array($value) ? $value[$i] : $value;
        }, $attributes);
    }

    public function createList($count, $override_attributes)
    {
        $override_attributes = $this->expandAttributesForList($override_attributes, $count);
        return array_map(function($i) use ($override_attributes) {
            return $this->create($override_attributes[$i]);
        }, range(0, $count - 1));
    }

    public function add($name, $definitionCallback)
    {
        $callback = function($f) use ($definitionCallback) {
            $f->setAttributes($this->attributes);
            $definitionCallback($f);
        };
        $this->coordinator->add([$name, $this->model], $callback);
    }

    public function belongsTo($name, $foreign_key, $attributes = [])
    {
        $factoryLoader = $this->coordinator->getFactoryLoader($name);
        return new BelongsTo($this->model, $factoryLoader, $foreign_key, $attributes);
    }

    public function hasMany($name, $count, $foreign_key = null, $attributes = [])
    {
        $factoryLoader = $this->coordinator->getFactoryLoader($name);
        return new HasMany($this->model, $factoryLoader, $count, $foreign_key, $attributes);
    }

    public function hasOne($name, $foreign_key, $attributes = [])
    {
        $factoryLoader = $this->coordinator->getFactoryLoader($name);
        return new HasOne($this->model, $factoryLoader, $foreign_key, $attributes);
    }
}
