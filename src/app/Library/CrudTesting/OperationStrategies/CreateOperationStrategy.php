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
        return $this->makeTestDescriptor(
            'test_create_page_loads_successfully',
            'Test that the create page loads without errors',
            'testCreatePageLoads'
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
            ['fields' => $fields]
        );
    }

    /**
     * Generate test for create form submission.
     *
     * @return array|null
     */
    protected function generateCreateFormSubmitTest(): ?array
    {
        return $this->makeTestDescriptor(
            'test_create_form_submits_successfully',
            'Test that the create form can be submitted',
            'testFormSubmits',
            [
                'method' => 'POST',
                'fields' => $this->getFieldsConfiguration(),
            ]
        );
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

        return $this->makeTestDescriptor(
            'test_validation_works_correctly',
            'Test that validation rules are enforced',
            'testValidationWorks',
            [
                'method' => 'POST',
                'required_fields' => $requiredFields,
            ]
        );
    }
}
