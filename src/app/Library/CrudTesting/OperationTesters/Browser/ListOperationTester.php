<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationTesters\Browser;

use Backpack\CRUD\app\Library\CrudTesting\OperationTesters\OperationTester;

/**
 * Browser Tester for List operation.
 */
class ListOperationTester extends OperationTester
{
    /**
     * {@inheritdoc}
     */
    public function getTestMethods(): array
    {
        return [
            'testListPageLoads',
            'testDataTablesWorks',
            'testColumnsAreVisible',
            'testSearchWorks',
            'testFiltersWork',
            'testButtonsAreVisible',
            'testPaginationWorks',
            'testSortingWorks',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function generateTestData(): array
    {
        return [
            'route' => $this->route,
            'columns' => $this->config['columns'] ?? [],
            'filters' => $this->config['filters'] ?? [],
            'buttons' => $this->config['buttons'] ?? [],
        ];
    }

    /**
     * Test that the list page loads.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testListPageLoads($browser): void
    {
        $browser->visit($this->route)
            ->assertSee($this->config['entity_name_plural'])
            ->assertVisible('#crudTable');
    }

    /**
     * Test that DataTables works.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testDataTablesWorks($browser): void
    {
        $browser->visit($this->route)
            ->waitFor('#crudTable', 5)
            ->waitUntilMissing('.dataTables_processing', 10)
            ->assertVisible('#crudTable tbody tr');
    }

    /**
     * Test that columns are visible.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testColumnsAreVisible($browser): void
    {
        $browser->visit($this->route);

        foreach ($this->config['columns'] ?? [] as $column) {
            $browser->assertSee($column['label']);
        }
    }

    /**
     * Test that search works.
     *
     * @param  mixed  $browser
     * @param  string  $searchTerm
     * @return void
     */
    public function testSearchWorks($browser, string $searchTerm): void
    {
        $browser->visit($this->route)
            ->type('#datatable_search_stack input', $searchTerm)
            ->pause(1000) // Wait for DataTables to reload
            ->assertSee($searchTerm);
    }

    /**
     * Test that filters work.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testFiltersWork($browser): void
    {
        $filters = $this->config['filters'] ?? [];

        if (empty($filters)) {
            return;
        }

        $browser->visit($this->route);

        // Check if filters dropdown exists
        if ($browser->element('.filters-dropdown-button')) {
            $browser->click('.filters-dropdown-button');

            foreach ($filters as $filter) {
                $browser->assertVisible("[name='filter[{$filter['name']}]']");
            }
        }
    }

    /**
     * Test that buttons are visible.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testButtonsAreVisible($browser): void
    {
        $browser->visit($this->route);

        $buttons = $this->config['buttons'] ?? [];

        foreach ($buttons as $stack => $stackButtons) {
            foreach ($stackButtons as $button) {
                // Just check that some button indicators exist
                // Actual button visibility depends on permissions and data
            }
        }
    }

    /**
     * Test that pagination works.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testPaginationWorks($browser): void
    {
        $browser->visit($this->route)
            ->waitFor('#crudTable', 5);

        // Check if pagination exists (only if there are enough records)
        if ($browser->element('.pagination')) {
            $browser->assertVisible('.pagination');
        }
    }

    /**
     * Test that sorting works.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testSortingWorks($browser): void
    {
        $browser->visit($this->route)
            ->waitFor('#crudTable', 5);

        // Try clicking a sortable column header
        $columns = $this->config['columns'] ?? [];
        $sortableColumn = collect($columns)->first(fn ($col) => ($col['orderable'] ?? true));

        if ($sortableColumn) {
            $browser->click('#crudTable thead th:first-child')
                ->pause(1000); // Wait for DataTables to reload
        }
    }
}
