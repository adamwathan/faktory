<?php namespace AdamWathan\Facktory;

class Facktory
{
	protected static $factories = [];

	public static function add($model, $definition)
	{
		$factory = new Factory($model);
		$definition($factory);
		static::$factories[$model] = $factory;
	}

	public static function build($model, $attributes = [])
	{
		return static::getFactory($model)->build($attributes);
	}

	protected static function getFactory($model)
	{
		return static::$factories[$model];
	}
}
