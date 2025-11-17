<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationStrategies;

/**
 * Strategy for 'delete' operation testing.
 */
class DeleteOperationStrategy extends AbstractOperationStrategy
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
     * Generate test methods for delete operation.
     *
     * @return array
     */
    public function generateTestMethods(): array
    {
        return array_filter([
            $this->generateDeleteButtonTest(),
            $this->generateDeleteConfirmationTest(),
        ]);
    }

    /**
     * Generate test for delete button.
     *
     * @return array|null
     */
    protected function generateDeleteButtonTest(): ?array
    {
        return [
            'name' => 'test_delete_button_is_visible',
            'description' => 'Test that the delete button is visible on list page',
            'route' => $this->crudPanel->route,
        ];
    }

    /**
     * Generate test for delete confirmation.
     *
     * @return array|null
     */
    protected function generateDeleteConfirmationTest(): ?array
    {
        return [
            'name' => 'test_delete_works_correctly',
            'description' => 'Test that delete operation works',
            'route' => $this->crudPanel->route.'/{id}',
            'method' => 'DELETE',
        ];
    }
}
