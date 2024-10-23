<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Hooks\Contracts;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(string $hook, callable $callback)
 * @method static void run(string $hook, array $parameters)
 * @method static bool has(string $hook)
 *
 * @see \Backpack\CRUD\app\Library\CrudPanel\Hooks\PanelHooks
 */
class PanelHook extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'panel-hook';
    }
}
