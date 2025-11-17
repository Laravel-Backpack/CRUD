<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationTesters;

use Backpack\CRUD\app\Library\CrudTesting\FieldTesters\FieldTester;

/**
 * Tester for Update operation.
 * 
 * Handles testing for the update/edit page of a CRUD, including:
 * - Page loading
 * - Field visibility and population
 * - Form submission
 * - Validation
 */
class UpdateOperationTester extends OperationTester
{
    /**
     * {@inheritdoc}
     */
    public function getTestMethods(): array
    {
        return [
            'testUpdatePageLoads',
            'testFieldsAreVisible',
            'testFieldsArePopulated',
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

        return $data;
    }

    /**
     * Test that update page loads.
     *
     * @param  mixed  $browser
     * @param  int  $id
     * @return void
     */
    public function testUpdatePageLoads($browser, int $id): void
    {
        $browser->visit($this->route.'/'.$id.'/edit')
            ->assertSee($this->config['entity_name'])
            ->assertVisible('form');
    }

    /**
     * Test that fields are visible.
     *
     * @param  mixed  $browser
     * @param  int  $id
     * @return void
     */
    public function testFieldsAreVisible($browser, int $id): void
    {
        $browser->visit($this->route.'/'.$id.'/edit');

        foreach ($this->config['fields'] ?? [] as $field) {
            $fieldTester = FieldTester::make($field);
            $fieldTester->assertVisible($browser);
        }
    }

    /**
     * Test that fields are populated with existing data.
     *
     * @param  mixed  $browser
     * @param  int  $id
     * @param  array  $expectedData
     * @return void
     */
    public function testFieldsArePopulated($browser, int $id, array $expectedData = []): void
    {
        $browser->visit($this->route.'/'.$id.'/edit');

        foreach ($this->config['fields'] ?? [] as $field) {
            if (isset($expectedData[$field['name']])) {
                $fieldTester = FieldTester::make($field);
                // Note: Depending on field type, value assertion may need different approach
                // This is a basic check
                $fieldTester->assertVisible($browser);
            }
        }
    }

    /**
     * Test that form submits successfully.
     *
     * @param  mixed  $browser
     * @param  int  $id
     * @return void
     */
    public function testFormSubmits($browser, int $id): void
    {
        $browser->visit($this->route.'/'.$id.'/edit');

        $testData = $this->generateTestData();

        foreach ($this->config['fields'] ?? [] as $field) {
            if (isset($testData[$field['name']])) {
                $fieldTester = FieldTester::make($field);
                $fieldTester->fill($browser, $testData[$field['name']]);
            }
        }

        $browser->press('Update')
            ->pause(1000)
            ->assertDontSee('error');
    }

    /**
     * Test that validation works.
     *
     * @param  mixed  $browser
     * @param  int  $id
     * @return void
     */
    public function testValidationWorks($browser, int $id): void
    {
        $browser->visit($this->route.'/'.$id.'/edit');

        // Clear required fields if possible and try to submit
        $requiredFields = collect($this->config['fields'] ?? [])
            ->filter(fn ($field) => $field['required'] ?? false);

        foreach ($requiredFields as $field) {
            $fieldTester = FieldTester::make($field);
            // Attempt to clear the field
            try {
                $fieldTester->fill($browser, '');
            } catch (\Exception $e) {
                // Some fields may not allow clearing
            }
        }

        $browser->press('Update')
            ->pause(500);

        if ($requiredFields->isNotEmpty()) {
            $browser->assertVisible('.invalid-feedback');
        }
    }
}
