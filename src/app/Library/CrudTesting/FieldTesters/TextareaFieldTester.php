<?php

namespace Backpack\CRUD\app\Library\CrudTesting\FieldTesters;

use Illuminate\Support\Str;

/**
 * Tester for textarea fields.
 */
class TextareaFieldTester extends FieldTester
{
    /**
     * {@inheritdoc}
     */
    public function getSelector(): string
    {
        return "textarea[name='{$this->getName()}']";
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
        return Str::random(100);
    }
}
