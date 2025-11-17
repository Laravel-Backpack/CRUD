<?php

namespace Backpack\CRUD\app\Library\CrudTesting\FieldTesters;

/**
 * Tester for TinyMCE WYSIWYG fields.
 */
class TinymceFieldTester extends WysiwygFieldTester
{
    /**
     * {@inheritdoc}
     */
    public function fill($browser, $value)
    {
        $this->waitForEditor($browser);

        $escapedValue = addslashes($value);
        $script = "tinymce.get('{$this->getName()}').setContent('{$escapedValue}');";

        return $this->executeScript($browser, $script);
    }

    /**
     * {@inheritdoc}
     */
    protected function waitForEditor($browser, int $seconds = 5)
    {
        return $browser->waitUntil(
            "typeof tinymce !== 'undefined' && tinymce.get('{$this->getName()}') !== null",
            $seconds
        );
    }
}
