<?php namespace AdamWathan\Facktory\Relationship;

abstract class DependentRelationship extends Relationship
{
	abstract public function create($instance);
}
