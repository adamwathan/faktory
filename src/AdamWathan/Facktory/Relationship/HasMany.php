<?php namespace AdamWathan\Facktory\Relationship;

class HasMany extends Relationship
{
	public $factory;
	public $foreign_key;
	public $count;
	public $attributes;

	public function __construct($factory, $foreign_key, $count, $attributes)
	{
		$this->factory = $factory;
		$this->foreign_key = $foreign_key;
		$this->count = $count;
		$this->attributes = $attributes;
	}

	public function build()
	{
        return $this->factory->__invoke()->buildList($this->count, $this->attributes);
	}

	public function create($instance)
	{
		$this->attributes[$this->foreign_key] = $instance->getKey();
        return $this->factory->__invoke()->createList($this->count, $this->attributes);
	}
}
