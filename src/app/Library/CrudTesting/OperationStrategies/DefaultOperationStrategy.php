<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationStrategies;

/**
 * Default strategy for operations that don't have a specific strategy.
 * Performs basic smoke testing.
 */
class DefaultOperationStrategy extends AbstractOperationStrategy
{
    /**
     * Get operation-specific configuration.
     *
     * @return array
     */
    public function getOperationConfiguration(): array
    {
        return [];
    }

    /**
     * Generate test methods for default operation.
     *
     * @return array
     */
    public function generateTestMethods(): array
    {
        return array_filter([
            $this->generatePageLoadTest(),
        ]);
    }

    /**
     * Generate test for page loading.
     *
     * @return array|null
     */
    protected function generatePageLoadTest(): ?array
    {
        return $this->makeTestDescriptor(
            'test_operation_page_loads',
            'Test that the operation page loads without errors (default strategy)',
            'testPageLoads'
        );
    }
}
