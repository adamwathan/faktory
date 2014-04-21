<?php namespace AdamWathan\Facktory\Facades;

use Illuminate\Support\Facades\Facade;

class Facktory extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor(){
		return 'adamwathan.facktory';
	}
}
