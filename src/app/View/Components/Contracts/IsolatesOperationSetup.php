<?php

namespace Backpack\CRUD\app\View\Components\Contracts;

/**
 * Marker interface for Backpack components that require their operation setup
 * to be isolated from the parent CRUD panel context.
 *
 * Implement this interface only when the component MUST have its operation
 * setup isolated. Components that do not want isolation should NOT implement
 * this interface.
 */
interface IsolatesOperationSetup
{
}
