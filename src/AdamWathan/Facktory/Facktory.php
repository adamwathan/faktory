<?php namespace AdamWathan\Facktory;

use Faker\Factory as Faker;
use Illuminate\Support\Collection;

class Facktory
{
	public static $factories = [];

	public static function add($model, $attributes)
	{
		static::$factories[$model] = $attributes;
	}

	public static function build($model, $attributes = [])
	{
		$instance = new $model;
		$factory = static::$factories[$model];
		$factory_attributes = static::generateAttributes($factory);
		$attributes = array_merge($factory_attributes, $attributes);
		foreach ($attributes as $attribute => $value) {
			$instance->{$attribute} = $value;
		}
		return $instance;
	}

	protected static function generateAttributes($attributes)
	{
		$result = [];
		foreach ($attributes as $attribute => $type) {
			$result[$attribute] = static::generateAttribute($type);
		}
		return $result;
	}

	protected static function generateAttribute($type)
	{
		if (static::isRelationship($type)) {
			return static::generateRelationship($type);
		}
		list($type, $args) = static::extractArguments($type);
		$faker = Faker::create();
		return call_user_func_array([$faker, $type], $args);
	}

	protected static function isRelationship($type)
	{
		if (! is_array($type)) {
			return false;
		}
		list($key, $params) = static::extractArguments($type);
		if ($key === ':hasMany') {
			return true;
		}
	}

	protected static function generateRelationship($relationship)
	{
		list($relationshipType, list($model, $count)) = static::extractArguments($relationship);
		// dd($relationshipType, $model, $count);
		$result = new Collection;
		foreach (range(1, $count) as $index) {
			$result[] = static::build($model);
		}
		return $result;
	}

	protected static function extractArguments($type)
	{
		if (! is_array($type)) {
			return [$type, []];
		}
		reset($type);
		$key = key($type);
		return [$key, $type[$key]];
	}
}
