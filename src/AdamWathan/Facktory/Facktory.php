<?php namespace AdamWathan\Facktory;

class Facktory
{
	protected static $factories = [];

	public static function add($name, $definitionCallback)
	{
		list($name, $model) = static::extractNameAndModel($name);
		$factory = new Factory($model);
		$definitionCallback($factory);
		static::$factories[$name] = $factory;
	}

	protected static function extractNameAndModel($name)
	{
		if (! is_array($name)) {
			return [$name, $name];
		}
		return [$name[0], $name[1]];
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
