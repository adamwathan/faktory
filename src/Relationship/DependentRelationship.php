<?php namespace AdamWathan\Faktory\Relationship;

abstract class DependentRelationship extends Relationship
{
    public function create($instance)
    {
        $this->attributes[$this->getForeignKey()] = $instance->getKey();
        return $this->createRelated();
    }

    abstract protected function createRelated();
}
