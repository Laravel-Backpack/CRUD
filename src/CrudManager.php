<?php

namespace Backpack\CRUD;

use Illuminate\Support\Facades\Facade;

/**
 * @see CrudPanelManager
 */
class CrudManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'CrudManager';
    }
}
