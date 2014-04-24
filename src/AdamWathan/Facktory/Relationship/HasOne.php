<?php namespace AdamWathan\Facktory\Relationship;

class HasOne extends DependentRelationship
{
	public function build()
	{
		return $this->factoryLoader->__invoke()->build($this->attributes);
	}

	public function create($instance)
	{
		$this->attributes[$this->foreign_key] = $instance->getKey();
        return $this->factoryLoader->__invoke()->create($this->attributes);
	}
}