<?php namespace AdamWathan\Facktory\Strategy;

class Create extends Strategy
{
    public function newInstance()
    {
        $precedents = $this->createPrecedents();
        $instance = $this->newModel();
        foreach ($this->attributes as $attribute => $value) {
            $instance->{$attribute} = $this->getAttributeValue($value);
        }
        return $instance;
    }

    protected function createPrecedents()
    {
        $precedents = [];
        foreach ($this->attributes as $attribute => $value) {
            if (! $value instanceof BelongsTo) {
                continue;
            }
            $precedents[] = $this->createPrecedent($value);
            $this->unsetAttribute($attribute);
        }
        return $precedents;
    }

    protected function unsetAttribute($attribute)
    {
        unset($this->attributes[$attribute]);
    }

    protected function getAttributeValue($value)
    {
        if (is_callable($value)) {
            return $value($this, $this->sequence);
        }
        return $value;
    }
}
