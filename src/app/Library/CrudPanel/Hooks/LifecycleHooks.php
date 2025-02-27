<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Hooks;

final class LifecycleHooks
{
    public array $hooks = [];

    public function hookInto(string|array $hooks, callable $callback): void
    {
        $hooks = is_array($hooks) ? $hooks : [$hooks];
        foreach ($hooks as $hook) {
            $this->hooks[$hook][] = $callback;
        }
    }

    public function trigger(string|array $hooks, array $parameters = []): void
    {
        $hooks = is_array($hooks) ? $hooks : [$hooks];
        foreach ($hooks as $hook) {
            if (isset($this->hooks[$hook])) {
                foreach ($this->hooks[$hook] as $callback) {
                    if ($callback instanceof \Closure) {
                        $callback(...$parameters);
                    }
                }
            }
        }
    }

    public function has(string $hook): bool
    {
        return isset($this->hooks[$hook]);
    }
}
