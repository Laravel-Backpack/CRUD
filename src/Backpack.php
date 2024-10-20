<?php

namespace Backpack\CRUD;

use Illuminate\Support\Facades\Facade;

/**
 * @see BackpackManager
 */
class Backpack extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'backpack-manager';
    }
}
