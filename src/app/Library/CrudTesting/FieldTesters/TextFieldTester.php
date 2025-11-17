<?php

namespace Backpack\CRUD\app\Library\CrudTesting\FieldTesters;

use Illuminate\Support\Str;

/**
 * Tester for text input fields (text, password, hidden, etc.).
 */
class TextFieldTester extends FieldTester
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
        return Str::random(10);
    }
}
