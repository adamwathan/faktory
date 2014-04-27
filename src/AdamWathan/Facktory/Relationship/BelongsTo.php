<?php namespace AdamWathan\Facktory\Relationship;

class BelongsTo extends Relationship
{
	public function build()
	{
        return $this->factoryLoader->__invoke()->build($this->attributes);
	}

	public function create()
	{
        return $this->factoryLoader->__invoke()->create($this->attributes);
	}

    protected function relatedModelBase()
    {
    	$model = $this->factoryLoader->__invoke()->getModel();
    	return $this->extractClassBase($model);
    }
}
