<?php

namespace Backpack\CRUD\app\Library\CrudTesting\FieldTesters;

/**
 * Base tester for select fields.
 * Handles standard HTML select elements.
 */
class SelectFieldTester extends FieldTester
{
    /**
     * {@inheritdoc}
     */
    public function getSelector(): string
    {
        return "select[name='{$this->getName()}']";
    }

    /**
     * {@inheritdoc}
     */
    public function fill($browser, $value)
    {
        return $browser->select($this->getSelector(), $value);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFakeValue()
    {
        $options = $this->getFieldOptions();

        if (empty($options)) {
            return null;
        }

        $keys = array_keys($options);

        return $keys[array_rand($keys)];
    }

    /**
     * Get options from field configuration.
     *
     * @return array
     */
    protected function getFieldOptions(): array
    {
        return $this->field['options'] ?? [];
    }
}
