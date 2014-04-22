<?php namespace AdamWathan\Facktory\Strategy;

use AdamWathan\Facktory\Relationship\BelongsTo;

class Build extends Strategy
{
    public function newInstance()
    {
        $this->createPrecedents();
        $instance = $this->newModel();
        foreach ($this->attributes as $attribute => $value) {
            $instance->{$attribute} = $this->getAttributeValue($value);
        }
        return $instance;
    }

    protected function createPrecedents()
    {
        foreach ($this->attributes as $attribute => $value) {
            if (! $value instanceof BelongsTo) {
                continue;
            }
            $precedent = $this->createPrecedent($value);
            $this->setAttribute($attribute, $precedent);
        }
    }

    protected function setAttribute($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
    }

    protected function createPrecedent($relationship)
    {
        $instance = $relationship->factory->build($relationship->attributes);
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
