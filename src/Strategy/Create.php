<?php namespace Vehikl\Facktory\Strategy;

use Vehikl\Facktory\Relationship\BelongsTo;
use Vehikl\Facktory\Relationship\HasOne;
use Vehikl\Facktory\Relationship\HasMany;
use Vehikl\Facktory\Relationship\Relationship;
use Vehikl\Facktory\Relationship\DependentRelationship;

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
        $this->setAttribute($relationship->getForeignKey(), $precedent->getKey());
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
        return $value instanceof DependentRelationship;
    }
}
