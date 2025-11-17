<?php

namespace Backpack\CRUD\app\Library\CrudTesting\FieldTesters;

/**
 * Tester for CKEditor WYSIWYG fields.
 */
class CkeditorFieldTester extends WysiwygFieldTester
{
    /**
     * {@inheritdoc}
     */
    public function fill($browser, $value)
    {
        $this->waitForEditor($browser);

        $escapedValue = addslashes($value);
        $script = "CKEDITOR.instances['{$this->getName()}'].setData('{$escapedValue}');";

        return $this->executeScript($browser, $script);
    }

    /**
     * {@inheritdoc}
     */
    protected function waitForEditor($browser, int $seconds = 5)
    {
        return $browser->waitUntil(
            "typeof CKEDITOR !== 'undefined' && CKEDITOR.instances['{$this->getName()}'] !== undefined",
            $seconds
        );
    }
}
