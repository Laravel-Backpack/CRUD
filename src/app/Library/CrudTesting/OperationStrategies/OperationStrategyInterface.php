<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationStrategies;

/**
 * Interface for operation-specific test generation strategies.
 */
interface OperationStrategyInterface
{
    /**
     * Get operation-specific configuration.
     *
     * @return array
     */
    public function getOperationConfiguration(): array;

    /**
     * Generate test methods for this operation.
     *
     * @return array
     */
    public function generateTestMethods(): array;
}
