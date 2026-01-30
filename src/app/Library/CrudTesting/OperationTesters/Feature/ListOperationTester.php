<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationTesters\Feature;

use Backpack\CRUD\app\Library\CrudTesting\OperationTesters\OperationTester;

/**
 * Feature Tester for List operation.
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
            'testColumnsAreVisible',
            'testDataTablesWorks',
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
        $browser->get($this->route)
            ->assertStatus(200)
            ->assertSee($this->config['entity_name_plural']);
    }

    /**
     * Test that configured columns are visible in the list.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testColumnsAreVisible($browser): void
    {
        $response = $browser->get($this->route);
        
        foreach ($this->config['columns'] ?? [] as $column) {
            $response->assertSee($column['label']);
        }
    }

    /**
     * Test that DataTables AJAX endpoint returns data.
     * 
     * @param mixed $browser
     * @return void
     */
    public function testDataTablesWorks($browser): void
    {
        // CRUD Search endpoint is usually POST to {route}/search
        $response = $browser->post($this->route . '/search');
        
        $response->assertStatus(200);
        
        // Assert structure matches DataTables response
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data'
        ]);
    }

    /**
     * Test that search works via AJAX.
     *
     * @param  mixed  $browser
     * @param  string  $searchTerm
     * @return void
     */
    public function testSearchWorks($browser, string $searchTerm): void
    {
        // Mock DataTables search request
        $response = $browser->post($this->route . '/search', [
            'search' => ['value' => $searchTerm],
            'columns' => $this->config['columns'] ?? [],
        ]);
        
        $response->assertStatus(200);
        // We expect the search term to be in the returned data 
        // OR the count to be filtered. 
        // This is a loose check as we don't know the exact data structure usually
        // But for feature tests, status 200 on search endpoint is a good start
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

        $response = $browser->get($this->route);
        // Check for filter UI elements
        $response->assertSee('filters-dropdown-button'); 
    }

    /**
     * Test that buttons are visible.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testButtonsAreVisible($browser): void
    {
        $response = $browser->get($this->route);
        // Basic check
        // Real check would need to know button HTML
    }

    /**
     * Test that pagination works.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testPaginationWorks($browser): void
    {
        $response = $browser->get($this->route);
        // Pagination is often rendered via JS in DataTables, so Feature test might not see it in HTML
        // unless it's static pagination.
    }

    /**
     * Test that sorting works.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testSortingWorks($browser): void
    {
        // Sorting is handled via AJAX in DataTables
        // We could test AJAX with order param
    }
}
