<?php namespace AdamWathan\Facktory\Relationship;

class HasMany extends DependentRelationship
{
	protected $amount;

	public function __construct($factoryLoader, $amount, $foreign_key, $attributes)
	{
		parent::__construct($factoryLoader, $foreign_key, $attributes);
		$this->amount = $amount;
	}

	public function build()
	{
        return $this->factoryLoader->__invoke()->buildList($this->amount, $this->attributes);
	}

	public function create($instance)
	{
		$this->attributes[$this->foreign_key] = $instance->getKey();
        return $this->factoryLoader->__invoke()->createList($this->amount, $this->attributes);
	}

	public function amount($amount)
	{
		$this->amount = $amount;
		return $this;
	}
}
