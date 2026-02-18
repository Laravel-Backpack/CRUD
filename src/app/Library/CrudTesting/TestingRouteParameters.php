<?php

namespace Backpack\CRUD\app\Library\CrudTesting;

use Attribute;

/**
 * Attribute to provide route parameters to CRUD tests.
 *
 * Usage:
 * #[TestingRouteParameters(['pet' => 1, 'owner' => 2])]
 * class MyCrudController { ... }
 *
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class TestingRouteParameters
{
    private array $parameters;

    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

    /**
     * Return the configured route parameters.
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
