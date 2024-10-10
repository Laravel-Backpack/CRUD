<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Hooks;

final class OperationHooks
{
    const BEFORE_OPERATION_SETUP = 'beforeOperationSetup';
    const AFTER_OPERATION_SETUP = 'afterOperationSetup';
    const SETUP_OPERATION_FROM_CONFIG = 'setupOperationFromConfig';

    public array $hooks = [];

    public function register(string $hook, string|array $operations, callable $callback): void
    {
        $operations = is_array($operations) ? $operations : [$operations];

        foreach ($operations as $operation) {
            $this->hooks[$operation][$hook][] = $callback;
        }
    }

    public function run(string $hook, string|array $operations, array $parameters): void
    {
        $operations = is_array($operations) ? $operations : [$operations];
        foreach ($operations as $operation) {
            if (isset($this->hooks[$operation][$hook])) {
                foreach ($this->hooks[$operation][$hook] as $callback) {
                    $callback(...$parameters);
                }
            }
        }
    }

    public function has(string $hook, string $operation): bool
    {
        return isset($this->hooks[$operation][$hook]);
    }
}
