<?php namespace AdamWathan\Facktory\Strategy;

use AdamWathan\Facktory\Relationship\BelongsTo;
use AdamWathan\Facktory\Relationship\HasOne;
use AdamWathan\Facktory\Relationship\HasMany;

class Create extends Strategy
{
    public function newInstance()
    {
        $precedents = $this->createPrecedents();
        $instance = $this->newModel();
        foreach ($this->attributes as $attribute => $value) {
            $instance->{$attribute} = $this->getAttributeValue($value);
        }
        $instance->save();
        return $instance;
    }

    protected function createPrecedents()
    {
        $precedents = [];
        foreach ($this->attributes as $attribute => $value) {
            if (! $value instanceof BelongsTo) {
                continue;
            }
            $precedent = $this->createPrecedent($value);
            $this->setAttribute($value->foreign_key, $precedent->getKey());
            $this->unsetAttribute($attribute);
        }
        return $precedents;
    }

    protected function setAttribute($attribute, $value)
    {
        $this->attributes[$attribute] = $value;
    }

    protected function unsetAttribute($attribute)
    {
        unset($this->attributes[$attribute]);
    }

    protected function createPrecedent($relationship)
    {
        return $relationship->create();
    }

    protected function getAttributeValue($value)
    {
        if (is_callable($value)) {
            return $value($this, $this->sequence);
        }
        return $value;
    }
}
