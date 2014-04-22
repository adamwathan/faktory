<?php namespace AdamWathan\Facktory\Strategy;

class Create extends Strategy
{
    public function newInstance()
    {
        $instance = $this->newModel();
        foreach ($this->attributes as $attribute => $value) {
            $instance->{$attribute} = $this->getAttributeValue($value);
        }
        return $instance;
    }

    protected function getAttributeValue($value)
    {
        if (is_callable($value)) {
            return $value($this, $this->sequence);
        }
        return $value;
    }
}
