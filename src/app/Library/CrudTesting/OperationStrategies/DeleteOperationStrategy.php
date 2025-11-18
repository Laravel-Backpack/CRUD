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
            $this->generateDeleteExecutionTest(),
        ]);
    }

    /**
     * Generate test for delete button.
     *
     * @return array|null
     */
    protected function generateDeleteButtonTest(): ?array
    {
        return $this->makeTestDescriptor(
            'test_delete_button_is_visible',
            'Test that the delete button is visible on list page',
            'testDeleteButtonExists',
            ['requires_entries' => true]
        );
    }

    /**
     * Generate test for delete confirmation.
     *
     * @return array|null
     */
    protected function generateDeleteConfirmationTest(): ?array
    {
        return $this->makeTestDescriptor(
            'test_delete_confirmation_is_displayed',
            'Test that the delete confirmation dialog appears',
            'testDeleteConfirmationAppears',
            ['requires_entries' => true]
        );
    }

    /**
     * Generate test for delete execution.
     */
    protected function generateDeleteExecutionTest(): ?array
    {
        return $this->makeTestDescriptor(
            'test_delete_works_correctly',
            'Test that delete operation works',
            'testDeleteWorks',
            [
                'method' => 'DELETE',
                'requires_entry' => true,
            ]
        );
    }
}
