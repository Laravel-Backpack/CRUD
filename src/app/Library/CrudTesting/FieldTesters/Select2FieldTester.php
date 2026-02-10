<?php

namespace Backpack\CRUD\app\Library\CrudTesting\FieldTesters;

use Illuminate\Support\Str;

/**
 * Tester for select2 enhanced select fields.
 * Handles AJAX-powered select2 dropdowns with search.
 */
class Select2FieldTester extends FieldTester
{
    /**
     * {@inheritdoc}
     */
    public function getSelector(): string
    {
        return ".select2-container[data-field='{$this->getName()}']";
    }

    /**
     * {@inheritdoc}
     */
    public function fill($browser, $value)
    {
        // Open select2 dropdown
        $browser->click($this->getSelector());

        // Wait for dropdown to appear
        $browser->waitFor('.select2-dropdown', 2);

        // Type search value
        $browser->type('.select2-search__field', $value);

        // Select first result
        $browser->keys('.select2-search__field', '{enter}');

        return $browser;
    }

    /**
     * {@inheritdoc}
     */
    public function generateFakeValue()
    {
        return Str::random(10);
    }

    /**
     * Wait for select2 AJAX results to load.
     *
     * @param  mixed  $browser
     * @param  int  $seconds
     * @return mixed
     */
    protected function waitForResults($browser, int $seconds = 5)
    {
        return $browser->waitUntil('$(".select2-results__option").length > 0', $seconds);
    }
}
