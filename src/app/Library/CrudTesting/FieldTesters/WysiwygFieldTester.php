<?php

namespace Backpack\CRUD\app\Library\CrudTesting\FieldTesters;

use Illuminate\Support\Str;

/**
 * Base tester for WYSIWYG editor fields.
 * Provides common functionality for rich text editors.
 */
abstract class WysiwygFieldTester extends FieldTester
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
    public function generateFakeValue()
    {
        return $this->generateHtmlContent();
    }

    /**
     * Generate fake HTML content.
     *
     * @return string
     */
    protected function generateHtmlContent(): string
    {
        return '<p>'.Str::random(100).'</p>';
    }

    /**
     * Execute JavaScript to set editor content.
     *
     * @param  mixed  $browser
     * @param  string  $script
     * @return mixed
     */
    protected function executeScript($browser, string $script)
    {
        return $browser->script($script);
    }

    /**
     * Wait for editor to be initialized.
     *
     * @param  mixed  $browser
     * @param  int  $seconds
     * @return mixed
     */
    abstract protected function waitForEditor($browser, int $seconds = 5);
}
