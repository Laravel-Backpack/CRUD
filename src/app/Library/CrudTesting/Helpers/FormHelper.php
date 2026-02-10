<?php

namespace Backpack\CRUD\app\Library\CrudTesting\Helpers;

use Backpack\CRUD\app\Library\CrudTesting\FieldTesters\FieldTester;

/**
 * Helper class for interacting with CRUD forms in browser tests.
 */
class FormHelper
{
    protected $browser;
    protected string $formSelector = 'form';
    protected array $fields = [];

    public function __construct($browser, array $fields = [])
    {
        $this->browser = $browser;
        $this->fields = $fields;
    }

    /**
     * Fill the form with data.
     *
     * @param  array  $data  Associative array of field names and values
     * @return self
     */
    public function fill(array $data): self
    {
        foreach ($data as $fieldName => $value) {
            $field = $this->getFieldConfig($fieldName);

            if ($field) {
                $fieldTester = FieldTester::make($field);
                $fieldTester->fill($this->browser, $value);
            } else {
                // Fallback to simple type
                $this->browser->type("[name='{$fieldName}']", $value);
            }
        }

        return $this;
    }

    /**
     * Fill the form with generated fake data.
     *
     * @return array The generated data
     */
    public function fillWithFakeData(): array
    {
        $data = [];

        foreach ($this->fields as $field) {
            $fieldTester = FieldTester::make($field);
            $value = $fieldTester->generateFakeValue();
            $data[$field['name']] = $value;

            $fieldTester->fill($this->browser, $value);
        }

        return $data;
    }

    /**
     * Submit the form.
     *
     * @param  string  $buttonText
     * @return self
     */
    public function submit(string $buttonText = 'Save'): self
    {
        $this->browser->press($buttonText);
        $this->browser->pause(1000); // Wait for submission

        return $this;
    }

    /**
     * Assert that a field is visible.
     *
     * @param  string  $fieldName
     * @return self
     */
    public function assertFieldVisible(string $fieldName): self
    {
        $field = $this->getFieldConfig($fieldName);

        if ($field) {
            $fieldTester = FieldTester::make($field);
            $fieldTester->assertVisible($this->browser);
        } else {
            $this->browser->assertVisible("[name='{$fieldName}']");
        }

        return $this;
    }

    /**
     * Assert that a field has a specific value.
     *
     * @param  string  $fieldName
     * @param  mixed  $value
     * @return self
     */
    public function assertFieldValue(string $fieldName, $value): self
    {
        $field = $this->getFieldConfig($fieldName);

        if ($field) {
            $fieldTester = FieldTester::make($field);
            $fieldTester->assertValue($this->browser, $value);
        } else {
            $this->browser->assertInputValue("[name='{$fieldName}']", $value);
        }

        return $this;
    }

    /**
     * Assert that a validation error is shown for a field.
     *
     * @param  string  $fieldName
     * @param  string|null  $message
     * @return self
     */
    public function assertValidationError(string $fieldName, ?string $message = null): self
    {
        // Find the field wrapper
        $fieldWrapper = $this->browser->element("[name='{$fieldName}']")->getParent();

        $this->browser->with($fieldWrapper, function ($wrapper) use ($message) {
            $wrapper->assertVisible('.invalid-feedback');

            if ($message) {
                $wrapper->assertSee($message, '.invalid-feedback');
            }
        });

        return $this;
    }

    /**
     * Assert that no validation errors are shown.
     *
     * @return self
     */
    public function assertNoValidationErrors(): self
    {
        $this->browser->assertMissing('.invalid-feedback');

        return $this;
    }

    /**
     * Assert that the form has a specific number of fields.
     *
     * @param  int  $expected
     * @return self
     */
    public function assertFieldCount(int $expected): self
    {
        $actual = count($this->fields);

        if ($actual !== $expected) {
            throw new \Exception("Expected {$expected} fields, but found {$actual}");
        }

        return $this;
    }

    /**
     * Assert that a save action button is visible.
     *
     * @param  string  $actionName
     * @return self
     */
    public function assertSaveActionVisible(string $actionName): self
    {
        $this->browser->assertVisible("[name='_save_action'][value='{$actionName}']");

        return $this;
    }

    /**
     * Select a save action.
     *
     * @param  string  $actionName
     * @return self
     */
    public function selectSaveAction(string $actionName): self
    {
        // Open save actions dropdown
        if ($this->browser->element('.dropdown-toggle')) {
            $this->browser->click('.dropdown-toggle');
            $this->browser->pause(300);
        }

        $this->browser->click("[data-value='{$actionName}']");

        return $this;
    }

    /**
     * Get field configuration.
     *
     * @param  string  $fieldName
     * @return array|null
     */
    protected function getFieldConfig(string $fieldName): ?array
    {
        foreach ($this->fields as $field) {
            if ($field['name'] === $fieldName) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Assert that all configured fields are visible.
     *
     * @return self
     */
    public function assertAllFieldsVisible(): self
    {
        foreach ($this->fields as $field) {
            $this->assertFieldVisible($field['name']);
        }

        return $this;
    }

    /**
     * Fill only required fields.
     *
     * @return array The generated data
     */
    public function fillRequiredFields(): array
    {
        $data = [];

        foreach ($this->fields as $field) {
            if ($field['required'] ?? false) {
                $fieldTester = FieldTester::make($field);
                $value = $fieldTester->generateFakeValue();
                $data[$field['name']] = $value;

                $fieldTester->fill($this->browser, $value);
            }
        }

        return $data;
    }

    /**
     * Clear a specific field.
     *
     * @param  string  $fieldName
     * @return self
     */
    public function clearField(string $fieldName): self
    {
        $this->browser->clear("[name='{$fieldName}']");

        return $this;
    }

    /**
     * Clear all fields.
     *
     * @return self
     */
    public function clearAll(): self
    {
        foreach ($this->fields as $field) {
            $this->clearField($field['name']);
        }

        return $this;
    }
}
