<?php namespace AdamWathan\Facktory\Strategy;

abstract class Strategy
{
    protected $model;
    protected $sequence;
    protected $attributes;

    public function __construct($model, $sequence)
    {
        $this->model = $model;
        $this->sequence = $sequence;
    }

    public static function make($model, $sequence)
    {
        return new static($model, $sequence);
    }

    public function attributes($attributes)
    {
        $this->attributes = $attributes;
    }

    protected function newModel()
    {
        return new $this->model;
    }

    public function __get($key)
    {
        return $this->getAttributeValue($this->attributes[$key]);
    }

    abstract public function newInstance();
    abstract protected function getAttributeValue($value);
}
