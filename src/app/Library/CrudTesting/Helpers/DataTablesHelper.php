<?php

namespace Backpack\CRUD\app\Library\CrudTesting\Helpers;

/**
 * Helper class for interacting with DataTables in browser tests.
 */
class DataTablesHelper
{
    protected $browser;
    protected string $tableSelector = '#crudTable';

    public function __construct($browser)
    {
        $this->browser = $browser;
    }

    /**
     * Wait for DataTables to finish loading.
     *
     * @param  int  $seconds
     * @return self
     */
    public function waitForLoad(int $seconds = 10): self
    {
        $this->browser
            ->waitFor($this->tableSelector, $seconds)
            ->waitUntilMissing('.dataTables_processing', $seconds);

        return $this;
    }

    /**
     * Search in DataTables.
     *
     * @param  string  $searchTerm
     * @return self
     */
    public function search(string $searchTerm): self
    {
        $this->browser->type('#datatable_search_stack input', $searchTerm);
        $this->waitForLoad();

        return $this;
    }

    /**
     * Clear search.
     *
     * @return self
     */
    public function clearSearch(): self
    {
        $this->browser->clear('#datatable_search_stack input');
        $this->waitForLoad();

        return $this;
    }

    /**
     * Assert that the table contains text.
     *
     * @param  string  $text
     * @return self
     */
    public function assertContains(string $text): self
    {
        $this->browser->with($this->tableSelector.' tbody', function ($table) use ($text) {
            $table->assertSee($text);
        });

        return $this;
    }

    /**
     * Assert that the table does not contain text.
     *
     * @param  string  $text
     * @return self
     */
    public function assertNotContains(string $text): self
    {
        $this->browser->with($this->tableSelector.' tbody', function ($table) use ($text) {
            $table->assertDontSee($text);
        });

        return $this;
    }

    /**
     * Get the number of rows in the table.
     *
     * @return int
     */
    public function getRowCount(): int
    {
        return count($this->browser->elements($this->tableSelector.' tbody tr'));
    }

    /**
     * Click a button in a specific row.
     *
     * @param  int  $rowIndex  Row index (0-based)
     * @param  string  $buttonSelector  CSS selector for the button
     * @return self
     */
    public function clickRowButton(int $rowIndex, string $buttonSelector): self
    {
        $selector = "{$this->tableSelector} tbody tr:nth-child(".($rowIndex + 1).") {$buttonSelector}";
        $this->browser->click($selector);

        return $this;
    }

    /**
     * Sort by a column.
     *
     * @param  string  $columnName
     * @return self
     */
    public function sortBy(string $columnName): self
    {
        $this->browser->click("{$this->tableSelector} thead th:contains('{$columnName}')");
        $this->waitForLoad();

        return $this;
    }

    /**
     * Change page length (number of entries per page).
     *
     * @param  int  $length
     * @return self
     */
    public function changePageLength(int $length): self
    {
        $this->browser->select("[name='{$this->tableSelector}_length']", (string) $length);
        $this->waitForLoad();

        return $this;
    }

    /**
     * Go to next page.
     *
     * @return self
     */
    public function nextPage(): self
    {
        $this->browser->click('.paginate_button.next');
        $this->waitForLoad();

        return $this;
    }

    /**
     * Go to previous page.
     *
     * @return self
     */
    public function previousPage(): self
    {
        $this->browser->click('.paginate_button.previous');
        $this->waitForLoad();

        return $this;
    }

    /**
     * Assert row count.
     *
     * @param  int  $expected
     * @return self
     */
    public function assertRowCount(int $expected): self
    {
        $actual = $this->getRowCount();

        if ($actual !== $expected) {
            throw new \Exception("Expected {$expected} rows, but found {$actual}");
        }

        return $this;
    }

    /**
     * Get cell value.
     *
     * @param  int  $rowIndex
     * @param  int  $columnIndex
     * @return string
     */
    public function getCellValue(int $rowIndex, int $columnIndex): string
    {
        $selector = "{$this->tableSelector} tbody tr:nth-child(".($rowIndex + 1).") td:nth-child(".($columnIndex + 1).")";

        return $this->browser->text($selector);
    }

    /**
     * Assert column header exists.
     *
     * @param  string  $columnName
     * @return self
     */
    public function assertColumnExists(string $columnName): self
    {
        $this->browser->with("{$this->tableSelector} thead", function ($thead) use ($columnName) {
            $thead->assertSee($columnName);
        });

        return $this;
    }
}
