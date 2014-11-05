<?php namespace Vehikl\Faktory\Relationship;

class HasOne extends DependentRelationship
{
    public function build()
    {
        return $this->factory->build($this->attributes);
    }

    protected function createRelated()
    {
        return $this->factory->create($this->attributes);
    }
}
