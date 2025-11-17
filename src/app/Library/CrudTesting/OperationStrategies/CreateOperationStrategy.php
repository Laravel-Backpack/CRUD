<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationStrategies;

/**
 * Strategy for 'create' operation testing.
 */
class CreateOperationStrategy extends AbstractOperationStrategy
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
     * Generate test methods for create operation.
     *
     * @return array
     */
    public function generateTestMethods(): array
    {
        return array_filter([
            $this->generateCreatePageLoadTest(),
            $this->generateFieldsRenderTest(),
            $this->generateCreateFormSubmitTest(),
            $this->generateValidationTest(),
        ]);
    }

    /**
     * Generate test for create page loading.
     *
     * @return array|null
     */
    protected function generateCreatePageLoadTest(): ?array
    {
        return [
            'name' => 'test_create_page_loads_successfully',
            'description' => 'Test that the create page loads without errors',
            'route' => $this->crudPanel->route.'/create',
            'assertions' => [
                'status' => 200,
                'see' => [$this->crudPanel->entity_name],
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
            'route' => $this->crudPanel->route.'/create',
            'fields' => $fields,
        ];
    }

    /**
     * Generate test for create form submission.
     *
     * @return array|null
     */
    protected function generateCreateFormSubmitTest(): ?array
    {
        return [
            'name' => 'test_create_form_submits_successfully',
            'description' => 'Test that the create form can be submitted',
            'route' => $this->crudPanel->route,
            'method' => 'POST',
            'fields' => $this->getFieldsConfiguration(),
        ];
    }

    /**
     * Generate test for validation.
     *
     * @return array|null
     */
    protected function generateValidationTest(): ?array
    {
        $requiredFields = collect($this->getFieldsConfiguration())
            ->filter(fn ($field) => $field['required'])
            ->toArray();

        if (empty($requiredFields)) {
            return null;
        }

        return [
            'name' => 'test_validation_works_correctly',
            'description' => 'Test that validation rules are enforced',
            'route' => $this->crudPanel->route,
            'method' => 'POST',
            'required_fields' => $requiredFields,
        ];
    }
}
