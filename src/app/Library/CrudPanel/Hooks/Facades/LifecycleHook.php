<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Hooks\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(string $hook, string|array $operations, callable $callback)
 * @method static void run(string $hook, string|array $operations, array $parameters)
 * @method static bool has(string $hook, string $operation)
 *
 * @see \Backpack\CRUD\app\Library\CrudPanel\Hooks\OperationHooks
 */
class LifecycleHook extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'lifecycle-hook';
    }
}
