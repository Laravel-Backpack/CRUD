<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationTesters;

/**
 * Default tester for operations without a dedicated tester.
 * Performs basic smoke testing.
 */
class DefaultOperationTester extends OperationTester
{
    /**
     * {@inheritdoc}
     */
    public function getTestMethods(): array
    {
        return [
            'testPageLoads',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function generateTestData(): array
    {
        return [];
    }

    /**
     * Test that the operation page loads.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testPageLoads($browser): void
    {
        $operation = $this->config['operation'] ?? '';
        
        // As a fallback, we assume the operation mapping is standard (route/operation)
        // If it is 'list', the path is empty
        $path = $this->route;
        if ($operation !== 'list' && !empty($operation)) {
            $path .= '/' . $operation;
        }

        $browser->visit($path);
        
        // Basic assertions to ensure page loaded
        if (isset($this->config['entity_name'])) {
            $browser->assertSee($this->config['entity_name']);
        }
        
        // Assert no obvious errors
        $browser->assertDontSee('404')
                ->assertDontSee('Whoops, looks like something went wrong');
    }
}
