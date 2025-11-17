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
        return [
            'name' => 'test_show_page_loads_successfully',
            'description' => 'Test that the show page loads without errors',
            'route' => $this->crudPanel->route.'/{id}/show',
            'assertions' => [
                'status' => 200,
            ],
        ];
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

        return [
            'name' => 'test_show_columns_display_correctly',
            'description' => 'Test that configured columns display on show page',
            'route' => $this->crudPanel->route.'/{id}/show',
            'columns' => $columns,
        ];
    }
}
