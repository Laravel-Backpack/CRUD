<?php

namespace Backpack\CRUD\app\View\Components\Contracts;

/**
 * Interface for Backpack components that need to control operation isolation behavior.
 *
 * Components implementing this interface can declare whether the CRUD panel should
 * isolate their operation setup from the parent context.
 *
 * Use cases:
 * - Modal forms: Isolate setup to prevent affecting the parent view's operation state
 * - Standalone forms: Don't isolate, can switch operations permanently
 * - Datatables: Don't isolate, manage their own operation state
 */
interface IsolatesOperationSetup
{
    /**
     * Determine if this component's operation setup should be isolated.
     *
     * When true, the CrudPanelManager will create a temporary isolated context
     * for this component's operation setup, preserving the parent operation state.
     *
     * When false, the component is allowed to permanently switch the operation.
     *
     * @return bool True if operation should be isolated, false otherwise
     */
    public function shouldIsolateOperationSetup(): bool;
}
