<?php namespace Vehikl\Faktory\Relationship;

class HasMany extends DependentRelationship
{
    protected $quantity;

    public function __construct($related_model, $factoryLoader, $quantity, $foreign_key = null, $attributes = [])
    {
        parent::__construct($related_model, $factoryLoader, $foreign_key, $attributes);
        $this->quantity = $quantity;
    }

    public function build()
    {
        return $this->factory->buildList($this->quantity, $this->attributes);
    }

    protected function createRelated()
    {
        return $this->factory->createList($this->quantity, $this->attributes);
    }

    public function quantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }
}
