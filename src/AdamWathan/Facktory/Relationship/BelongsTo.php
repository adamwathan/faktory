<?php namespace AdamWathan\Facktory\Relationship;

class BelongsTo extends Relationship
{
	public $factory;
	public $foreign_key;
	public $attributes;

	public function __construct($factory, $foreign_key, $attributes)
	{
		$this->factory = $factory;
		$this->foreign_key = $foreign_key;
		$this->attributes = $attributes;
	}

	public function build()
	{
        return $this->factory->__invoke()->build($this->attributes);
	}

	public function create()
	{
        return $this->factory->__invoke()->create($this->attributes);
	}
}
