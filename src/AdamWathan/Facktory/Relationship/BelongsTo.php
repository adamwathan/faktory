<?php namespace AdamWathan\Facktory\Relationship;

class BelongsTo
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
}
