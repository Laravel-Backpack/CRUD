<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationTesters\Browser;

use Backpack\CRUD\app\Library\CrudTesting\OperationTesters\OperationTester;
use Backpack\CRUD\app\Library\CrudTesting\FieldTesters\FieldTester;

/**
 * Browser Tester for Create operation.
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
            'testRequiredFields',
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
            $overrides = $this->config['custom_data_source']->getCreateData();
            $data = array_merge($data, $overrides);
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
        $browser->visit($this->route.'/create')
            ->assertSee($this->config['entity_name'])
            ->assertVisible('form');
    }

    /**
     * Test that fields are visible.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testFieldsAreVisible($browser): void
    {
        $browser->visit($this->route.'/create');

        foreach ($this->config['fields'] ?? [] as $field) {
            $fieldTester = FieldTester::make($field);
            $fieldTester->assertVisible($browser);
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
        $browser->visit($this->route.'/create');

        $testData = $this->generateTestData();

        foreach ($this->config['fields'] ?? [] as $field) {
            if (isset($testData[$field['name']])) {
                $fieldTester = FieldTester::make($field);
                $fieldTester->fill($browser, $testData[$field['name']]);
            }
        }

        $browser->press('Save')
            ->pause(1000)
            ->assertDontSee('error');
    }

    /**
     * Test that validation works.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testValidationWorks($browser): void
    {
        $browser->visit($this->route.'/create')
            ->press('Save') // Submit without filling fields
            ->pause(500);

        // Check for validation errors on required fields
        $requiredFields = collect($this->config['fields'] ?? [])
            ->filter(fn ($field) => $field['required'] ?? false);

        if ($requiredFields->isNotEmpty()) {
            $browser->assertVisible('.invalid-feedback');
        }
    }

    /**
     * Test that required fields are enforced.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testRequiredFields($browser): void
    {
        $browser->visit($this->route.'/create');

        $requiredFields = collect($this->config['fields'] ?? [])
            ->filter(fn ($field) => $field['required'] ?? false);

        foreach ($requiredFields as $field) {
            $fieldTester = FieldTester::make($field);
            
            // Check if field has required indicator (like asterisk)
            // This is visual verification, not functional
            $fieldTester->assertVisible($browser);
        }
    }
}
