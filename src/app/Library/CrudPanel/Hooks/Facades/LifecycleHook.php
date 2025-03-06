<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Hooks\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void hootInto(string|array $hooks, callable $callback)
 * @method static void trigger(string|array $hooks, array $parameters)
 * @method static bool has(string $hook)
 *
 * @see \Backpack\CRUD\app\Library\CrudPanel\Hooks\LifecycleHooks
 */
class LifecycleHook extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'BackpackLifecycleHooks';
    }
}
