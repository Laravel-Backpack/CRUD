<?php

namespace Backpack\CRUD\app\Library\CrudTesting\OperationTesters;

/**
 * Tester for Delete operation.
 * 
 * Handles testing for the delete functionality of a CRUD, including:
 * - Delete button visibility
 * - Confirmation dialog
 * - Actual deletion
 */
class DeleteOperationTester extends OperationTester
{
    /**
     * {@inheritdoc}
     */
    public function getTestMethods(): array
    {
        return [
            'testDeleteButtonExists',
            'testDeleteConfirmationAppears',
            'testDeleteWorks',
            'testBulkDeleteWorks',
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
     * Test that delete button exists.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testDeleteButtonExists($browser): void
    {
        $browser->visit($this->route)
            ->waitFor('#crudTable', 5)
            ->assertVisible('[data-button-type="delete"]');
    }

    /**
     * Test that delete confirmation appears.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testDeleteConfirmationAppears($browser): void
    {
        $browser->visit($this->route)
            ->waitFor('#crudTable', 5)
            ->click('[data-button-type="delete"]')
            ->pause(500)
            ->assertVisible('.swal2-popup'); // SweetAlert confirmation
    }

    /**
     * Test that delete works (deletes a single entry).
     *
     * @param  mixed  $browser
     * @param  int  $id
     * @param  string  $identifierText  Some text to identify the deleted entry
     * @return void
     */
    public function testDeleteWorks($browser, int $id, string $identifierText = ''): void
    {
        $browser->visit($this->route)
            ->waitFor('#crudTable', 5);

        if ($identifierText) {
            $browser->assertSee($identifierText);
        }

        $browser->click('[data-button-type="delete"]')
            ->pause(500)
            ->press('Delete') // Confirm deletion
            ->pause(1000);

        if ($identifierText) {
            $browser->assertDontSee($identifierText);
        }
    }

    /**
     * Test that bulk delete works.
     *
     * @param  mixed  $browser
     * @return void
     */
    public function testBulkDeleteWorks($browser): void
    {
        $browser->visit($this->route)
            ->waitFor('#crudTable', 5);

        // Check if bulk delete is enabled
        if ($browser->element('[data-button-type="bulk_delete"]')) {
            // Select some checkboxes
            $browser->check('input[type="checkbox"][data-crud-row-checkbox]')
                ->pause(500)
                ->click('[data-button-type="bulk_delete"]')
                ->pause(500)
                ->press('Delete') // Confirm bulk deletion
                ->pause(1000);
        }
    }
}
