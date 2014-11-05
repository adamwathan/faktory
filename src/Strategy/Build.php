<?php namespace AdamWathan\Faktory\Strategy;

use AdamWathan\Faktory\Relationship\Relationship;

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

    protected function buildRelationship($relationship)
    {
        return $relationship->build();
    }
}
