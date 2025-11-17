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
        return [
            'name' => 'test_update_page_loads_successfully',
            'description' => 'Test that the update page loads without errors',
            'route' => $this->crudPanel->route.'/{id}/edit',
            'assertions' => [
                'status' => 200,
            ],
        ];
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

        return [
            'name' => 'test_form_fields_render_correctly',
            'description' => 'Test that all configured fields render on the form',
            'route' => $this->crudPanel->route.'/{id}/edit',
            'fields' => $fields,
        ];
    }

    /**
     * Generate test for update form submission.
     *
     * @return array|null
     */
    protected function generateUpdateFormSubmitTest(): ?array
    {
        return [
            'name' => 'test_update_form_submits_successfully',
            'description' => 'Test that the update form can be submitted',
            'route' => $this->crudPanel->route.'/{id}',
            'method' => 'PUT',
            'fields' => $this->getFieldsConfiguration(),
        ];
    }
}
