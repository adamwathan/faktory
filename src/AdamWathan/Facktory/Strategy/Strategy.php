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

    protected function setAttribute($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
    }

    protected function unsetAttribute($attribute)
    {
        unset($this->attributes[$attribute]);
    }

    public function __get($key)
    {
        return $this->getAttributeValue($this->attributes[$key]);
    }

    protected function getAttributeValue($value)
    {
        if (is_callable($value)) {
            return $value($this, $this->sequence);
        }
        return $value;
    }

    abstract public function newInstance();
}
