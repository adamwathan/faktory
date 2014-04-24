<?php namespace AdamWathan\Facktory\Relationship;

abstract class Relationship
{
	protected $factoryLoader;
	protected $foreign_key;
	protected $attributes;

	public function __construct($factoryLoader, $foreign_key, $attributes)
	{
		$this->factoryLoader = $factoryLoader;
		$this->foreign_key = $foreign_key;
		$this->attributes = $attributes;
	}

	public function foreignKey()
	{
		return $this->foreign_key;
	}

	public function attributes($attributes)
	{
		$this->attributes = array_merge($this->attributes, $attributes);
	}

	abstract public function build();
}
