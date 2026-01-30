<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationTesters\Feature;

use Backpack\CRUD\app\Library\CrudTesting\OperationTesters\OperationTester;
use Backpack\CRUD\app\Library\CrudTesting\FieldTesters\FieldTester;

/**
 * Feature Tester for Create operation.
 */
class CreateOperationTester extends OperationTester
{
    /**
     * {@inheritdoc}
     */
    public function getTestMethods(): array
    {
        return [
            'testCreatePageLoads',
            'testFieldsAreVisible',
            'testFormSubmits',
            'testValidationWorks',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function generateTestData(): array
    {
        $data = [];
        $fields = $this->config['fields'] ?? [];

        foreach ($fields as $field) {
            $fieldTester = FieldTester::make($field);
            $data[$field['name']] = $fieldTester->generateFakeValue();
        }

        // Apply custom overrides from test class
        if (isset($this->config['custom_data_source']) && method_exists($this->config['custom_data_source'], 'getCreateData')) {
            // dd("DEBUG: getCreateData found and calling");
            $overrides = $this->config['custom_data_source']->getCreateData();
            $data = array_merge($data, $overrides);
        } else {
             // dump("DEBUG: getCreateData NOT FOUND on " . (isset($this->config['custom_data_source']) ? get_class($this->config['custom_data_source']) : 'null'));
        }

        return $data;
    }

    /**
     * Test that create page loads.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testCreatePageLoads($browser): void
    {
        $response = $browser->get($this->route.'/create');
        
        $response->assertStatus(200);
        $response->assertSee($this->config['entity_name'] ?? '');
    }

    /**
     * Test that fields are visible.
     * 
     * @param mixed $browser
     * @return void
     */
    public function testFieldsAreVisible($browser): void
    {
        $response = $browser->get($this->route.'/create');
        
        foreach ($this->config['fields'] ?? [] as $field) {
            $response->assertSee($field['label'] ?? $field['name']);
        }
    }

    /**
     * Test that form submits successfully.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testFormSubmits($browser): void
    {
        $testData = $this->generateTestData();

        $response = $browser->post($this->route, $testData);

        $response->assertSessionHasNoErrors();
        // Typically redirect to list or back
        $response->assertStatus(302);
    }

    /**
     * Test that validation works.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testValidationWorks($browser): void
    {
        // Submit empty form to trigger required validations
        $response = $browser->post($this->route, []);

        $requiredFields = collect($this->config['fields'] ?? [])
            ->filter(fn ($field) => $field['required'] ?? false)
            ->pluck('name')
            ->toArray();

        if (count($requiredFields) > 0) {
            $response->assertSessionHasErrors($requiredFields);
        }
    }
}
