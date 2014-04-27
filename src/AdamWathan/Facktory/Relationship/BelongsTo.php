<?php namespace AdamWathan\Facktory\Relationship;

class BelongsTo extends Relationship
{
    public function build()
    {
        return $this->factory->build($this->attributes);
    }

    public function create()
    {
        return $this->factory->create($this->attributes);
    }

    protected function getRelatedModel()
    {
        return $this->factory->getModel();
    }
}
