<?php namespace AdamWathan\Facktory;

use Faker\Factory as Faker;

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
			$args = [];
			if (is_array($type)) {
				reset($type);
				$key = key($type);
				$args = $type[$key];
				$type = $key;
			}
			$result[$attribute] = static::generateAttribute($type, $args);
		}
		return $result;
	}

	protected static function generateAttribute($type, $args = [])
	{
		$faker = Faker::create();
		return call_user_func_array([$faker, $type], $args);
	}
}
