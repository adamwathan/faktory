<?php namespace AdamWathan\Facktory;

class Facktory
{
    protected $factories = [];

    public function add($name, $definitionCallback)
    {
        list($name, $model) = $this->extractNameAndModel($name);
        $factory = Factory::make($model, $this);
        $this->addFactory($name, $factory);
        $definitionCallback($factory);
    }

    protected function extractNameAndModel($name)
    {
        if (! is_array($name)) {
            return [$name, $name];
        }
        return [$name[0], $name[1]];
    }

    protected function addFactory($name, $factory)
    {
        $this->factories[$name] = $factory;
    }

    public function build($name, $attributes = [])
    {
        return $this->getFactory($name)->build($attributes);
    }

    public function create($name, $attributes = [])
    {
        return $this->getFactory($name)->create($attributes);
    }

    public function buildList($name, $count, $attributes = [])
    {
        return $this->getFactory($name)->buildList($count, $attributes);
    }

    public function createList($name, $count, $attributes = [])
    {
        return $this->getFactory($name)->createList($count, $attributes);
    }

    public function getFactory($name)
    {
        return $this->getProxyFactory($name);
    }

    protected function getProxyFactory($name)
    {
        return new FactoryProxy(function() use ($name) {
            return $this->factories[$name];
        });
    }
}
