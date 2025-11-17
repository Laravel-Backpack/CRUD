<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationTesters;

/**
 * Tester for Show operation.
 * 
 * Handles testing for the show/details page of a CRUD, including:
 * - Page loading
 * - Column visibility
 * - Data display
 */
class ShowOperationTester extends OperationTester
{
    /**
     * {@inheritdoc}
     */
    public function getTestMethods(): array
    {
        return [
            'testShowPageLoads',
            'testColumnsAreVisible',
            'testDataIsDisplayed',
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
        ];
    }

    /**
     * Test that show page loads.
     *
     * @param  mixed  $browser
     * @param  int  $id
     * @return void
     */
    public function testShowPageLoads($browser, int $id): void
    {
        $browser->visit($this->route.'/'.$id.'/show')
            ->assertSee($this->config['entity_name'])
            ->assertVisible('[bp-section="crud-operation-show"]');
    }

    /**
     * Test that columns are visible.
     *
     * @param  mixed  $browser
     * @param  int  $id
     * @return void
     */
    public function testColumnsAreVisible($browser, int $id): void
    {
        $browser->visit($this->route.'/'.$id.'/show');

        foreach ($this->config['columns'] ?? [] as $column) {
            $browser->assertSee($column['label']);
        }
    }

    /**
     * Test that data is displayed correctly.
     *
     * @param  mixed  $browser
     * @param  int  $id
     * @param  array  $expectedData
     * @return void
     */
    public function testDataIsDisplayed($browser, int $id, array $expectedData = []): void
    {
        $browser->visit($this->route.'/'.$id.'/show');

        foreach ($expectedData as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $browser->assertSee((string) $value);
            }
        }
    }
}
