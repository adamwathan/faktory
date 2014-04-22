<?php namespace AdamWathan\Facktory\Strategy;

use AdamWathan\Facktory\Relationship\Relationship;

class Build extends Strategy
{
    public function newInstance()
    {
        $this->buildRelationships();
        $instance = $this->newModel();
        foreach ($this->attributes as $attribute => $value) {
            $instance->{$attribute} = $this->getAttributeValue($value);
        }
        return $instance;
    }

    protected function buildRelationships()
    {
        foreach ($this->attributes as $attribute => $value) {
            if (! $value instanceof Relationship) {
                continue;
            }
            $relationship = $this->buildRelationship($value);
            $this->setAttribute($attribute, $relationship);
        }
    }

    protected function setAttribute($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
    }

    protected function buildRelationship($relationship)
    {
        return $relationship->build();
    }

    protected function getAttributeValue($value)
    {
        if (is_callable($value)) {
            return $value($this, $this->sequence);
        }
        return $value;
    }
}
