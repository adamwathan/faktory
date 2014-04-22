<?php namespace AdamWathan\Facktory;

class Factory
{
    protected $model;
    protected $attributes;
    protected $relationships = [];
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
        $instance = $this->newModel();
        $attributes = array_merge($this->attributes, $override_attributes);
        foreach ($attributes as $attribute => $value) {
            $instance->{$attribute} = $this->getAttributeValue($value);
        }
        $this->sequence++;
        return $instance;
    }

    protected function getAttributeValue($value)
    {
        if (is_callable($value)) {
            return $value($this, $this->sequence);
        }
        return $value;
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

    public function create($override_attributes)
    {
        $instance = $this->build($override_attributes);
        $instance->save();
        $this->createRelationships($instance);
        return $instance;
    }

    protected function createRelationships($instance)
    {
        foreach ($this->relationships as $relationship) {
            $method = 'create'.ucfirst($relationship['type']);
            $this->{$method}($relationship, $instance);
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

    public function sequence($attribute, $callback)
    {
        $this->setAttribute($attribute, $callback);
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

    public function hasMany($name, $foreign_key, $count, $attributes = [])
    {
        $relationship = [
            'type' => 'hasMany',
            'name' => $name,
            'foreign_key' => $foreign_key,
            'count' => $count,
            'attributes' => $attributes,
        ];
        $this->addRelationship($relationship);
    }

    protected function addRelationship($relationship)
    {
        $this->relationships[] = $relationship;
    }
}
