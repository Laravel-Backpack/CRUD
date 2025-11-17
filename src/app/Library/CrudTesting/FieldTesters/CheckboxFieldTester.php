<?php

namespace Backpack\CRUD\app\Library\CrudTesting\FieldTesters;

/**
 * Tester for checkbox fields.
 */
class CheckboxFieldTester extends FieldTester
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
        return $value 
            ? $browser->check($this->getSelector()) 
            : $browser->uncheck($this->getSelector());
    }

    /**
     * {@inheritdoc}
     */
    public function generateFakeValue()
    {
        return (bool) rand(0, 1);
    }

    /**
     * {@inheritdoc}
     */
    public function assertValue($browser, $value): void
    {
        if ($value) {
            $browser->assertChecked($this->getSelector());
        } else {
            $browser->assertNotChecked($this->getSelector());
        }
    }
}
