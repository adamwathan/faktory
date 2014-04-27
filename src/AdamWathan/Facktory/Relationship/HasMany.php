<?php namespace AdamWathan\Facktory\Relationship;

class HasMany extends DependentRelationship
{
	protected $quantity;

	public function __construct($factoryLoader, $quantity, $foreign_key, $attributes)
	{
		parent::__construct($factoryLoader, $foreign_key, $attributes);
		$this->quantity = $quantity;
	}

	public function build()
	{
        return $this->factoryLoader->__invoke()->buildList($this->quantity, $this->attributes);
	}

	public function create($instance)
	{
		$this->attributes[$this->foreign_key] = $instance->getKey();
        return $this->factoryLoader->__invoke()->createList($this->quantity, $this->attributes);
	}

	public function quantity($quantity)
	{
		$this->quantity = $quantity;
		return $this;
	}
}
