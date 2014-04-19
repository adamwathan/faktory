<?php namespace AdamWathan\Facktory;

use Faker\Factory as Faker;
use Illuminate\Support\Collection;

class Factory
{
	protected $model;
	protected $definition;

	public function __construct($model, $definition)
	{
		$this->model = $model;
		$this->definition = $definition;
	}

	public function build($attributes)
	{
		$instance = $this->newModel();
		$factory_attributes = $this->generateAttributes();
		$attributes = array_merge($factory_attributes, $attributes);
		foreach ($attributes as $attribute => $value) {
			$instance->{$attribute} = $value;
		}
		return $instance;
	}

	protected function newModel()
	{
		return new $this->model;
	}

	protected function generateAttributes()
	{
		$attributes = [];
		foreach ($this->definition as $attribute => $type) {
			$attributes[$attribute] = $this->generateAttribute($type);
		}
		return $attributes;
	}

	protected function generateAttribute($type)
	{
		list($type, $args) = $this->extractArguments($type);
		$faker = Faker::create();
		return call_user_func_array([$faker, $type], $args);
	}

	protected function extractArguments($type)
	{
		if (! is_array($type)) {
			return [$type, []];
		}
		reset($type);
		$key = key($type);
		return [$key, $type[$key]];
	}
}
