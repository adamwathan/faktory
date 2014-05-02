<?php namespace Vehikl\Facktory\Relationship;

abstract class Relationship
{
    protected $factory;
    protected $foreign_key;
    protected $attributes;
    protected $related_model;

    public function __construct($related_model, $factory, $foreign_key = null, $attributes = [])
    {
        $this->related_model = $related_model;
        $this->factory = $factory;
        $this->foreign_key = $foreign_key;
        $this->attributes = $attributes;
    }

    public function foreignKey($key)
    {
        $this->foreign_key = $key;
        return $this;
    }

    public function getForeignKey()
    {
        if (! is_null($this->foreign_key)) {
            return $this->foreign_key;
        }
        return $this->guessForeignKey();
    }

    protected function guessForeignKey()
    {
        return $this->snakeCase($this->relatedModelBase()).'_id';
    }

    protected function snakeCase($value)
    {
        return ctype_lower($value) ? $value : strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $value));
    }

    protected function relatedModelBase()
    {
        return $this->extractClassBase($this->getRelatedModel());
    }

    protected function getRelatedModel()
    {
        return $this->related_model;
    }

    protected function extractClassBase($class)
    {
        $class_pieces = explode('\\', $class);
        return array_pop($class_pieces);
    }

    public function attributes($attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    abstract public function build();
}
