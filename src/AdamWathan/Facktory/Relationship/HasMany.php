<?php namespace AdamWathan\Facktory\Relationship;

class HasMany extends DependentRelationship
{
	protected $count;

	public function __construct($factoryLoader, $foreign_key, $count, $attributes)
	{
		parent::__construct($factoryLoader, $foreign_key, $attributes);
		$this->count = $count;
	}

	public function build()
	{
        return $this->factoryLoader->__invoke()->buildList($this->count, $this->attributes);
	}

	public function create($instance)
	{
		$this->attributes[$this->foreign_key] = $instance->getKey();
        return $this->factoryLoader->__invoke()->createList($this->count, $this->attributes);
	}
}
