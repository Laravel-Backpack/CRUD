<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationTesters\Feature;

use Backpack\CRUD\app\Library\CrudTesting\OperationTesters\OperationTester;
use Backpack\CRUD\app\Library\CrudTesting\FieldTesters\FieldTester;

class UpdateOperationTester extends OperationTester
{
    public function getTestMethods(): array
    {
        return [
            'testUpdatePageLoads',
            'testUpdateWorks',
            'testValidationWorks',
        ];
    }

    public function generateTestData(): array
    {
        $data = [];
        $fields = $this->config['fields'] ?? [];

        foreach ($fields as $field) {
            $fieldTester = FieldTester::make($field);
            $data[$field['name']] = $fieldTester->generateFakeValue();
        }

        // Apply custom overrides from test class
        if (isset($this->config['custom_data_source']) && method_exists($this->config['custom_data_source'], 'getUpdateData')) {
            $overrides = $this->config['custom_data_source']->getUpdateData();
            $data = array_merge($data, $overrides);
        } elseif (isset($this->config['custom_data_source']) && method_exists($this->config['custom_data_source'], 'getCreateData')) {
             // Fallback to create data if update data not specific
            $overrides = $this->config['custom_data_source']->getCreateData();
            $data = array_merge($data, $overrides);
        }

        return $data;
    }

    public function testUpdatePageLoads($testCase, $id): void
    {
        $response = $testCase->get($this->route.'/'.$id.'/edit');
        $response->assertStatus(200);
        if (isset($this->config['entity_name'])) {
            $response->assertSee($this->config['entity_name']);
        }
    }

    public function testUpdateWorks($testCase, $id): void
    {
        $data = $this->generateTestData();
        $response = $testCase->put($this->route.'/'.$id, $data);
        
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);
    }
    
    public function testValidationWorks($testCase): void
    {
        // ...
    }
}
