<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Hooks;
use Backpack\CRUD\Backpack;

final class LifecycleHooks
{
    public array $hooks = [];

    public function hookInto(string|array $hooks, callable $callback): void
    {
        $hooks = is_array($hooks) ? $hooks : [$hooks];
        foreach ($hooks as $hook) {
            $this->hooks[Backpack::getActiveController() ?? Backpack::getRequestController()][$hook][] = $callback;
        }
    }

    public function trigger(string|array $hooks, array $parameters = []): void
    {
        $hooks = is_array($hooks) ? $hooks : [$hooks];
        $controller = Backpack::getActiveController() ?? Backpack::getRequestController();

        foreach ($hooks as $hook) {
            if (isset($this->hooks[$controller][$hook])) {
                foreach ($this->hooks[$controller][$hook] as $callback) {
                    if ($callback instanceof \Closure) {
                        $callback(...$parameters);
                    }
                }
            }
        }
    }

    public function has(string $hook): bool
    {
        $controller = Backpack::getActiveController() ?? Backpack::getRequestController();
        return isset($this->hooks[$controller][$hook]);
    }
}
