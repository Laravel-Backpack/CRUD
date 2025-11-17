<?php

namespace Backpack\CRUD\app\Library\CrudTesting\Helpers;

/**
 * Helper class for interacting with CRUD filters in browser tests.
 */
class FilterHelper
{
    protected $browser;
    protected string $filtersButtonSelector = '.filters-dropdown-button';
    protected string $filtersDropdownSelector = '.filters-dropdown';

    public function __construct($browser)
    {
        $this->browser = $browser;
    }

    /**
     * Open the filters dropdown.
     *
     * @return self
     */
    public function open(): self
    {
        if ($this->browser->element($this->filtersButtonSelector)) {
            $this->browser->click($this->filtersButtonSelector);
            $this->browser->waitFor($this->filtersDropdownSelector, 2);
        }

        return $this;
    }

    /**
     * Close the filters dropdown.
     *
     * @return self
     */
    public function close(): self
    {
        if ($this->browser->element($this->filtersButtonSelector)) {
            $this->browser->click($this->filtersButtonSelector);
        }

        return $this;
    }

    /**
     * Apply a filter.
     *
     * @param  string  $filterName
     * @param  mixed  $value
     * @return self
     */
    public function apply(string $filterName, $value): self
    {
        $this->open();

        $selector = "[name='filter[{$filterName}]']";

        // Determine filter type and interact accordingly
        $element = $this->browser->element($selector);

        if ($element) {
            $tagName = $element->getTagName();

            if ($tagName === 'select') {
                $this->browser->select($selector, $value);
            } else {
                $this->browser->type($selector, $value);
            }
        }

        // Wait for table to reload
        $this->browser->pause(1000);

        return $this;
    }

    /**
     * Clear a filter.
     *
     * @param  string  $filterName
     * @return self
     */
    public function clear(string $filterName): self
    {
        $this->open();

        $selector = "[name='filter[{$filterName}]']";
        $this->browser->clear($selector);

        // Wait for table to reload
        $this->browser->pause(1000);

        return $this;
    }

    /**
     * Clear all filters.
     *
     * @return self
     */
    public function clearAll(): self
    {
        $this->open();

        if ($this->browser->element('.clear-filters-button')) {
            $this->browser->click('.clear-filters-button');
            $this->browser->pause(1000);
        }

        return $this;
    }

    /**
     * Assert that a filter exists.
     *
     * @param  string  $filterName
     * @return self
     */
    public function assertExists(string $filterName): self
    {
        $this->open();

        $selector = "[name='filter[{$filterName}]']";
        $this->browser->assertVisible($selector);

        return $this;
    }

    /**
     * Assert that a filter has a specific value.
     *
     * @param  string  $filterName
     * @param  mixed  $value
     * @return self
     */
    public function assertValue(string $filterName, $value): self
    {
        $this->open();

        $selector = "[name='filter[{$filterName}]']";
        $this->browser->assertInputValue($selector, $value);

        return $this;
    }

    /**
     * Get filter options for a select filter.
     *
     * @param  string  $filterName
     * @return array
     */
    public function getOptions(string $filterName): array
    {
        $this->open();

        $selector = "[name='filter[{$filterName}]']";
        $options = [];

        $elements = $this->browser->elements("{$selector} option");

        foreach ($elements as $element) {
            $options[] = [
                'value' => $element->getAttribute('value'),
                'text' => $element->getText(),
            ];
        }

        return $options;
    }

    /**
     * Assert filter count.
     *
     * @param  int  $expected
     * @return self
     */
    public function assertCount(int $expected): self
    {
        $this->open();

        $actual = count($this->browser->elements('[name^="filter["]'));

        if ($actual !== $expected) {
            throw new \Exception("Expected {$expected} filters, but found {$actual}");
        }

        return $this;
    }
}
