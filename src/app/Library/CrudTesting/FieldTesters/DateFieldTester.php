<?php

namespace Backpack\CRUD\app\Library\CrudTesting\FieldTesters;

/**
 * Tester for date picker fields.
 */
class DateFieldTester extends FieldTester
{
    /**
     * {@inheritdoc}
     */
    public function getSelector(): string
    {
        return "input[name='{$this->getName()}']";
    }

    /**
     * {@inheritdoc}
     */
    public function fill($browser, $value)
    {
        return $browser->type($this->getSelector(), $value);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFakeValue()
    {
        return now()->format($this->getDateFormat());
    }

    /**
     * Get the date format for this field.
     *
     * @return string
     */
    protected function getDateFormat(): string
    {
        return $this->field['date_format'] ?? 'Y-m-d';
    }
}
