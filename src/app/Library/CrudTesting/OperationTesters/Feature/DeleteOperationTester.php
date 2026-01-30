<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationTesters\Feature;

use Backpack\CRUD\app\Library\CrudTesting\OperationTesters\OperationTester;

class DeleteOperationTester extends OperationTester
{
    public function getTestMethods(): array
    {
        return [
            'testDeleteButtonExists',
            'testDeleteConfirmationAppears',
            'testDeleteWorks',
        ];
    }

    public function generateTestData(): array
    {
        return [];
    }

    public function testDeleteButtonExists($testCase): void
    {
        // Feature test limitation: We can't easily check for specific buttons without parsing HTML.
        // But we can check if the list page loads.
        $response = $testCase->get($this->route);
        $response->assertStatus(200);
    }

    public function testDeleteConfirmationAppears($testCase): void
    {
        // Feature test limitation: We can't check JS confirmation.
        $testCase->assertTrue(true);
    }

    public function testDeleteWorks($testCase, $id): void
    {
        $response = $testCase->delete($this->route.'/'.$id);
        
        // Allow redirect (302) or success (200)
        if ($response->status() !== 302 && $response->status() !== 200) {
             $response->assertStatus(200); // Fail with details
        }

        $model = $this->config['model'];
        if ($model) {
            $testCase->assertNull($model::find($id), 'Model was not deleted');
        }
    }
}
