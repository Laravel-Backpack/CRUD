<?php

namespace Backpack\CRUD\app\Library\CrudTesting\FieldTesters;

/**
 * Tester for email input fields.
 */
class EmailFieldTester extends FieldTester
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
        return 'test'.rand(1000, 9999).'@example.com';
    }
}
