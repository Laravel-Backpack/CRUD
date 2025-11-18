<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationStrategies;

/**
 * Strategy for 'update' operation testing.
 */
class UpdateOperationStrategy extends AbstractOperationStrategy
{
    /**
     * Get operation-specific configuration.
     *
     * @return array
     */
    public function getOperationConfiguration(): array
    {
        return [
            'fields' => $this->getFieldsConfiguration(),
            'save_actions' => $this->getSaveActionsConfiguration(),
        ];
    }

    /**
     * Generate test methods for update operation.
     *
     * @return array
     */
    public function generateTestMethods(): array
    {
        return array_filter([
            $this->generateUpdatePageLoadTest(),
            $this->generateFieldsRenderTest(),
            $this->generateUpdateFormSubmitTest(),
        ]);
    }

    /**
     * Generate test for update page loading.
     *
     * @return array|null
     */
    protected function generateUpdatePageLoadTest(): ?array
    {
        return $this->makeTestDescriptor(
            'test_update_page_loads_successfully',
            'Test that the update page loads without errors',
            'testUpdatePageLoads',
            ['requires_entry' => true]
        );
    }

    /**
     * Generate test for fields rendering.
     *
     * @return array|null
     */
    protected function generateFieldsRenderTest(): ?array
    {
        $fields = $this->getFieldsConfiguration();

        if (empty($fields)) {
            return null;
        }

        return $this->makeTestDescriptor(
            'test_form_fields_render_correctly',
            'Test that all configured fields render on the form',
            'testFieldsAreVisible',
            [
                'fields' => $fields,
                'requires_entry' => true,
            ]
        );
    }

    /**
     * Generate test for update form submission.
     *
     * @return array|null
     */
    protected function generateUpdateFormSubmitTest(): ?array
    {
        return $this->makeTestDescriptor(
            'test_update_form_submits_successfully',
            'Test that the update form can be submitted',
            'testFormSubmits',
            [
                'method' => 'PUT',
                'fields' => $this->getFieldsConfiguration(),
                'requires_entry' => true,
            ]
        );
    }
}
