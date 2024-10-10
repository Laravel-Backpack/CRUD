<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Hooks\Contracts;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void register(string $hook, string|array $operations, callable $callback)
 * @method static void run(string $hook, string|array $operations, array $parameters)
 * @method static bool has(string $hook, string $operation)
 *
 * @see \Backpack\CRUD\app\Library\CrudPanel\Hooks\OperationHooks
 */
class OperationHook extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'operation-hook';
    }
}
