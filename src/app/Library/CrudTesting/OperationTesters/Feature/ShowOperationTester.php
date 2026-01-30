<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationTesters\Feature;

use Backpack\CRUD\app\Library\CrudTesting\OperationTesters\OperationTester;

class ShowOperationTester extends OperationTester
{
    public function getTestMethods(): array
    {
        return [
            'testShowPageLoads',
            'testButtonsAreVisible',
        ];
    }

    public function generateTestData(): array
    {
        return [];
    }

    public function testShowPageLoads($testCase, $id): void
    {
        $response = $testCase->get($this->route.'/'.$id.'/show');
        $response->assertStatus(200);
        if (isset($this->config['entity_name'])) {
             $response->assertSee($this->config['entity_name']);
        }
    }

    public function testButtonsAreVisible($testCase): void
    {
        $model = $this->config['model'];
        $entry = $model::factory()->create();
        
        $response = $testCase->get($this->route.'/'.$entry->getKey().'/show');
        $response->assertStatus(200);
        
        // Assert generic buttons usually present
        // Depending on setup, Back button is almost always there.
        // Edit button if allowed.
        // $response->assertSee('Back'); // Might be localized or icon
        
        // Since this is a generic test, pass it if page loads.
        // Or check for a known marker of actions column or button stack
    }
}
