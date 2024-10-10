<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Hooks;

final class PanelHooks
{
    const BEFORE_SETUP_ROUTES = 'beforeSetupRoutes';
    const AFTER_SETUP_ROUTES = 'afterSetupRoutes';
    const BEFORE_SETUP_DEFAULTS = 'beforeSetupDefaults';
    const AFTER_SETUP_DEFAULTS = 'afterSetupDefaults';
    const BEFORE_CONTROLLER_SETUP = 'beforeControllerSetup';
    const AFTER_CONTROLLER_SETUP = 'afterControllerSetup';

    public array $hooks = [];

    public function register(string $hook, callable $callback): void
    {
        $this->hooks[$hook][] = $callback;
    }

    public function run(string $hook, array $parameters): void
    {
        if (isset($this->hooks[$hook])) {
            foreach ($this->hooks[$hook] as $callback) {
                $callback(...$parameters);
            }
        }
    }

    public function has(string $hook): bool
    {
        return isset($this->hooks[$hook]);
    }
}
