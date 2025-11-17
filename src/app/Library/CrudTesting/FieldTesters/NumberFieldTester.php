<?php

namespace Backpack\CRUD\app\Library\CrudTesting\FieldTesters;

/**
 * Tester for number input fields.
 */
class NumberFieldTester extends FieldTester
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
        return rand(1, 100);
    }
}
