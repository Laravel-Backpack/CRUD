<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationStrategies;

/**
 * Strategy for 'show' operation testing.
 */
class ShowOperationStrategy extends AbstractOperationStrategy
{
    /**
     * Get operation-specific configuration.
     *
     * @return array
     */
    public function getOperationConfiguration(): array
    {
        return [
            'columns' => $this->getColumnsConfiguration(),
        ];
    }

    /**
     * Generate test methods for show operation.
     *
     * @return array
     */
    public function generateTestMethods(): array
    {
        return array_filter([
            $this->generateShowPageLoadTest(),
            $this->generateColumnsDisplayTest(),
        ]);
    }

    /**
     * Generate test for show page loading.
     *
     * @return array|null
     */
    protected function generateShowPageLoadTest(): ?array
    {
        return $this->makeTestDescriptor(
            'test_show_page_loads_successfully',
            'Test that the show page loads without errors',
            'testShowPageLoads',
            ['requires_entry' => true]
        );
    }

    /**
     * Generate test for columns display on show page.
     *
     * @return array|null
     */
    protected function generateColumnsDisplayTest(): ?array
    {
        $columns = $this->getColumnsConfiguration();

        if (empty($columns)) {
            return null;
        }

        return $this->makeTestDescriptor(
            'test_show_columns_display_correctly',
            'Test that configured columns display on show page',
            'testColumnsAreVisible',
            [
                'columns' => $columns,
                'requires_entry' => true,
            ]
        );
    }
}
