<?php


namespace Backpack\CRUD\Facades;


use Illuminate\Support\Facades\Facade;

class CRUDPanel extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'CRUDPanel';
    }

}