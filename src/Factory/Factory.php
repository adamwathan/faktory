<?php namespace AdamWathan\Faktory\Factory;

use AdamWathan\Faktory\Strategy\Build as BuildStrategy;
use AdamWathan\Faktory\Strategy\Create as CreateStrategy;
use AdamWathan\Faktory\Relationship\BelongsTo;
use AdamWathan\Faktory\Relationship\HasMany;
use AdamWathan\Faktory\Relationship\HasOne;

class Factory
{
    protected $model;
    protected $attributes;
    protected $factory_repository;
    protected $sequence = 1;

    public function __construct($model, $factory_repository)
    {
        $this->model = $model;
        $this->factory_repository = $factory_repository;
    }

    public static function make($model, $factory_repository)
    {
        return new static($model, $factory_repository);
    }

    public function getModel()
    {
        return $this->model;
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

    public function buildMany($count, $override_attributes)
    {
        $override_attributes = $this->expandAttributesForList($override_attributes, $count);
        return array_map(function ($i) use ($override_attributes) {
            return $this->build($override_attributes[$i]);
        }, range(0, $count - 1));
    }

    protected function expandAttributesForList($attributes, $count)
    {
        return array_map(function ($i) use ($attributes) {
            return $this->extractAttributesForIndex($i, $attributes);
        }, range(0, $count - 1));
    }

    protected function extractAttributesForIndex($i, $attributes)
    {
        return array_map(function ($value) use ($i) {
            return is_array($value) ? $value[$i] : $value;
        }, $attributes);
    }

    public function createMany($count, $override_attributes)
    {
        $override_attributes = $this->expandAttributesForList($override_attributes, $count);
        return array_map(function ($i) use ($override_attributes) {
            return $this->create($override_attributes[$i]);
        }, range(0, $count - 1));
    }

    public function define($name, $definitionCallback)
    {
        $callback = function ($f) use ($definitionCallback) {
            $f->setAttributes($this->attributes);
            $definitionCallback($f);
        };
        $this->factory_repository->define($this->model, $name, $callback);
    }

    public function belongsTo($name, $foreign_key = null, $attributes = [])
    {
        $factory = $this->factory_repository->getFactory($name);
        return new BelongsTo($this->model, $factory, $foreign_key, $attributes);
    }

    public function hasMany($name, $count, $foreign_key = null, $attributes = [])
    {
        $factory = $this->factory_repository->getFactory($name);
        return new HasMany($this->model, $factory, $count, $foreign_key, $attributes);
    }

    public function hasOne($name, $foreign_key = null, $attributes = [])
    {
        $factory = $this->factory_repository->getFactory($name);
        return new HasOne($this->model, $factory, $foreign_key, $attributes);
    }
}
