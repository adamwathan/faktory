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
    protected $dependentRelationships = [];
    protected $precedentRelationships = [];
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

        $precedents = $this->createPrecedentRelationships();
        foreach ($precedents as $precedent) {
            $override_attributes[$precedent['foreign_key']] = $precedent['model']->getKey();
        }
        $instance = $this->build($override_attributes);
        $instance->save();
        $this->createDependentRelationships($instance);
        return $instance;
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
        return array_merge($this->attributes, $override_attributes);
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

    protected function createPrecedentRelationships()
    {
        $precedents = [];
        foreach ($this->precedentRelationships as $relationship) {
            $model = $this->coordinator->create($relationship['name'], $relationship['attributes']);
            $precedents[] = ['model' => $model, 'foreign_key' => $relationship['foreign_key']];
        }
        return $precedents;
    }

    protected function createDependentRelationships($instance)
    {
        foreach ($this->dependentRelationships as $relationship) {
            $this->createHasMany($relationship, $instance);
        }
    }

    protected function createHasMany($relationship, $instance)
    {
        $attributes = $relationship['attributes'];
        $attributes[$relationship['foreign_key']] = $instance->getKey();
        $this->coordinator->createList($relationship['name'], $relationship['count'], $attributes);
    }

    public function createList($count, $override_attributes)
    {
        $override_attributes = $this->expandAttributesForList($override_attributes, $count);
        return array_map(function($i) use ($override_attributes) {
            return $this->create($override_attributes[$i]);
        }, range(0, $count - 1));
    }

    protected function newModel($attributes = [])
    {
        $model = $this->model;
        return new $model($attributes);
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
        $factory = $this->coordinator->getLazyFactoryCallback($name);
        return new BelongsTo($factory, $foreign_key, $attributes);
    }

    public function hasMany($name, $foreign_key, $count, $attributes = [])
    {
        $factory = $this->coordinator->getLazyFactoryCallback($name);
        return new HasMany($factory, $foreign_key, $count, $attributes);
    }

    public function hasOne($name, $foreign_key, $attributes = [])
    {
        $factory = $this->coordinator->getLazyFactoryCallback($name);
        return new HasOne($factory, $foreign_key, $attributes);
    }
}
