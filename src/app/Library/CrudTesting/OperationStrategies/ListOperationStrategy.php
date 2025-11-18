<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationStrategies;

/**
 * Strategy for 'list' operation testing.
 */
class ListOperationStrategy extends AbstractOperationStrategy
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
            'filters' => $this->getFiltersConfiguration(),
            'buttons' => $this->getButtonsConfiguration(),
        ];
    }

    /**
     * Generate test methods for list operation.
     *
     * @return array
     */
    public function generateTestMethods(): array
    {
        return array_filter([
            $this->generateListPageLoadTest(),
            $this->generateDataTablesTest(),
            $this->generateColumnsVisibilityTest(),
            $this->generateFiltersTest(),
            $this->generateButtonsTest(),
        ]);
    }

    /**
     * Generate test for list page loading.
     *
     * @return array|null
     */
    protected function generateListPageLoadTest(): ?array
    {
        return $this->makeTestDescriptor(
            'test_list_page_loads_successfully',
            'Test that the list page loads without errors',
            'testListPageLoads'
        );
    }

    /**
     * Generate test for DataTables functionality.
     *
     * @return array|null
     */
    protected function generateDataTablesTest(): ?array
    {
        return $this->makeTestDescriptor(
            'test_datatables_returns_data',
            'Test that DataTables ajax endpoint returns data',
            'testDataTablesWorks',
            ['requires_entries' => true]
        );
    }

    /**
     * Generate test for columns visibility.
     *
     * @return array|null
     */
    protected function generateColumnsVisibilityTest(): ?array
    {
        $columns = $this->getColumnsConfiguration();

        if (empty($columns)) {
            return null;
        }

        return $this->makeTestDescriptor(
            'test_list_columns_are_visible',
            'Test that configured columns are visible in the list',
            'testColumnsAreVisible',
            ['columns' => $columns]
        );
    }

    /**
     * Generate test for filters functionality.
     *
     * @return array|null
     */
    protected function generateFiltersTest(): ?array
    {
        $filters = $this->getFiltersConfiguration();

        if (empty($filters)) {
            return null;
        }

        return $this->makeTestDescriptor(
            'test_filters_are_available',
            'Test that configured filters are available',
            'testFiltersWork',
            ['filters' => $filters]
        );
    }

    /**
     * Generate test for buttons functionality.
     *
     * @return array|null
     */
    protected function generateButtonsTest(): ?array
    {
        $buttons = $this->getButtonsConfiguration();

        if (empty($buttons)) {
            return null;
        }

        return $this->makeTestDescriptor(
            'test_buttons_are_visible',
            'Test that configured buttons are visible',
            'testButtonsAreVisible',
            ['buttons' => $buttons]
        );
    }

}
