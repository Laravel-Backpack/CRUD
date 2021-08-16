<?php

namespace Backpack\CRUD\app\Models\Traits;
/**
 * Allows the ability to change the exposed route key simply by setting the public $routeKey parameter.
 * public $routeKey = 'uuid';
 */

trait CanChangeRouteKey{
	

		    /**
	 * Get the route key for the model.
	 *
	 * @return string
	 */
	public function getRouteKeyName()
	{
	  return $this->routeKey ?? parent::getRouteKeyName();
	}


	public function getKey()
	{	
		return $this->routeKey ? $this->{$this->getRouteKeyName()} : parent::getKey();	
	}
}