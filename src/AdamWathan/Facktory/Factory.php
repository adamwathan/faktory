<?php namespace AdamWathan\Facktory;

class Factory
{
	protected $model;
	protected $attributes;

	public function __construct($model)
	{
		$this->model = $model;
	}

	public function __set($key, $value)
	{
		$this->setAttribute($key, $value);
	}

	protected function setAttribute($key, $value)
	{
		$this->attributes[$key] = $value;
	}

	public function build($override_attributes)
	{
		$instance = $this->newModel();
		$attributes = array_merge($this->attributes, $override_attributes);
		foreach ($attributes as $attribute => $value) {
			$instance->{$attribute} = $value;
		}
		return $instance;
	}

	protected function newModel()
	{
		return new $this->model;
	}
}
