<?php namespace AdamWathan\Facktory;

class FactoryProxy
{
    protected $factory_loader;
    protected $instance;

    public function __construct($factory_loader)
    {
        $this->factory_loader = $factory_loader;
    }

    protected function getInstance()
    {
        if (! isset($this->instance)) {
            $this->instance = $this->factory_loader->__invoke();
        }
        return $this->instance;
    }

    public function __call($method, $parameters)
    {
        $instance = $this->getInstance();
        return call_user_func_array(array($instance, $method), $parameters);
    }
}
