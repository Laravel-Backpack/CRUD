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
        return [
            'name' => 'test_list_page_loads_successfully',
            'description' => 'Test that the list page loads without errors',
            'route' => $this->crudPanel->route,
            'assertions' => [
                'status' => 200,
                'see' => [$this->crudPanel->entity_name_plural],
            ],
        ];
    }

    /**
     * Generate test for DataTables functionality.
     *
     * @return array|null
     */
    protected function generateDataTablesTest(): ?array
    {
        return [
            'name' => 'test_datatables_returns_data',
            'description' => 'Test that DataTables ajax endpoint returns data',
            'route' => $this->crudPanel->route.'/search',
            'method' => 'POST',
            'assertions' => [
                'status' => 200,
                'json_structure' => ['data', 'recordsTotal', 'recordsFiltered'],
            ],
        ];
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

        return [
            'name' => 'test_list_columns_are_visible',
            'description' => 'Test that configured columns are visible in the list',
            'route' => $this->crudPanel->route,
            'columns' => $columns,
        ];
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

        return [
            'name' => 'test_filters_are_available',
            'description' => 'Test that configured filters are available',
            'route' => $this->crudPanel->route,
            'filters' => $filters,
        ];
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

        return [
            'name' => 'test_buttons_are_visible',
            'description' => 'Test that configured buttons are visible',
            'route' => $this->crudPanel->route,
            'buttons' => $buttons,
        ];
    }
}
