<?php

namespace Backpack\CRUD\app\Library\CrudTesting\FieldTesters;

/**
 * Base tester for file upload fields.
 */
class UploadFieldTester extends FieldTester
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
        // $value should be a file path
        return $browser->attach($this->getSelector(), $value);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFakeValue()
    {
        return $this->getTestFilePath();
    }

    /**
     * Get the path to the test file for this upload field.
     *
     * @return string
     */
    protected function getTestFilePath(): string
    {
        return storage_path('app/test-files/test.txt');
    }

    /**
     * Create a test file if it doesn't exist.
     *
     * @param  string  $path
     * @param  string  $content
     * @return string
     */
    protected function ensureTestFileExists(string $path, string $content = 'Test file content'): string
    {
        $directory = dirname($path);

        if (! file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        if (! file_exists($path)) {
            file_put_contents($path, $content);
        }

        return $path;
    }
}
