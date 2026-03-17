<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Hooks;

use Backpack\CRUD\CrudManager;

final class LifecycleHooks
{
    public array $hooks = [];
    private array $executedHooks = [];

    public function hookInto(string|array $hooks, callable $callback): void
    {
        $hooks = is_array($hooks) ? $hooks : [$hooks];
        $controller = CrudManager::getActiveController() ?? CrudManager::getParentController() ?? '';

        if (empty($controller)) {
            return;
        }

        foreach ($hooks as $hook) {
            $this->hooks[$controller][$hook][] = $callback;
        }
    }

    public function trigger(string|array $hooks, array $parameters = []): void
    {
        $hooks = is_array($hooks) ? $hooks : [$hooks];
        $controller = CrudManager::getActiveController() ?? CrudManager::getParentController() ?? '';

        if (empty($controller)) {
            return;
        }

        foreach ($hooks as $hook) {
            // Create a unique identifier for this controller+hook combination
            // Include the full hook name (which includes operation) to ensure uniqueness per operation
            $hookId = is_null($controller) ? $hook : (is_string($controller) ? $controller : $controller::class).'::'.$hook;

            // Skip if this hook has already been executed
            if (isset($this->executedHooks[$hookId])) {
                continue;
            }

            if (isset($this->hooks[$controller][$hook])) {
                foreach ($this->hooks[$controller][$hook] as $callback) {
                    if ($callback instanceof \Closure) {
                        $callback(...$parameters);
                    }
                }

                $this->executedHooks[$hookId] = true;
            }
        }
    }

    public function has(string $hook): bool
    {
        $controller = CrudManager::getActiveController() ?? CrudManager::getParentController() ?? '';

        if (empty($controller)) {
            return false;
        }

        return isset($this->hooks[$controller][$hook]);
    }
}
