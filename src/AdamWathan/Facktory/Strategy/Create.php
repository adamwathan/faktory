<?php namespace AdamWathan\Facktory\Strategy;

use AdamWathan\Facktory\Relationship\BelongsTo;
use AdamWathan\Facktory\Relationship\HasOne;
use AdamWathan\Facktory\Relationship\HasMany;
use AdamWathan\Facktory\Relationship\Relationship;

class Create extends Strategy
{
    public function newInstance()
    {
        $this->createPrecedents();
        $instance = $this->newModel();
        foreach ($this->independentAttributes() as $attribute => $value) {
            $instance->{$attribute} = $this->getAttributeValue($value);
        }
        $instance->save();
        $this->createDependents($instance);
        return $instance;
    }

    protected function createPrecedents()
    {
        foreach ($this->attributes as $attribute => $value) {
            if ($value instanceof BelongsTo) {
                $this->createPrecedent($value);
                $this->unsetAttribute($attribute);
            }
        }
    }

    protected function createPrecedent($relationship)
    {
        $precedent = $relationship->create();
        $this->setAttribute($relationship->foreign_key, $precedent->getKey());
    }

    protected function independentAttributes()
    {
        $result = [];
        foreach ($this->attributes as $attribute => $value) {
            if (! $value instanceof Relationship) {
                $result[$attribute] = $value;
            }
        }
        return $result;
    }

    protected function createDependents($instance)
    {
        foreach ($this->dependentRelationships() as $relationship) {
            $relationship->create($instance);
        }
    }

    protected function dependentRelationships()
    {
        $result = [];
        foreach ($this->attributes as $attribute => $value) {
            if ($this->isDependentRelationship($value)) {
                $result[] = $value;
            }
        }
        return $result;
    }

    protected function isDependentRelationship($value)
    {
        return $value instanceof HasMany || $value instanceof HasOne;
    }
}
