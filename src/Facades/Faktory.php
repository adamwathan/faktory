<?php namespace AdamWathan\Faktory\Facades;

use Illuminate\Support\Facades\Facade;

class Faktory extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(){
        return 'adamwathan.faktory';
    }
}
