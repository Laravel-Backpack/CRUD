<?php

namespace Backpack\CRUD\app\Library\Assets;

use Illuminate\Support\Facades\Facade;

class AssetsFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'assets';
    }
}
