<?php

namespace Backpack\CRUD\Tests\Feature;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Http\Controllers\UserSoftDeletesCrudController;
use Backpack\CRUD\Tests\config\Models\User;
use Backpack\CRUD\Tests\config\Models\UserWithSoftDeletes;

/**
 * @covers Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation
 */
class ShowOperationTest extends BaseDBCrudPanel
{
    protected string $testBaseUrl;

    protected function defineRoutes($router)
    {
        $router->crud(config('backpack.base.route_prefix').'/users-soft-deletes', UserSoftDeletesCrudController::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->testBaseUrl = config('backpack.base.route_prefix').'/users-soft-deletes';
        $this->actingAs(User::find(1));
    }

    /**
     * A soft-deleted entry that satisfies the panel query constraint should be found and shown.
     */
    public function test_show_with_soft_deletes_finds_soft_deleted_entry_that_matches_panel_query()
    {
        $user = UserWithSoftDeletes::create([
            'name' => 'Allowed User',
            'email' => 'allowed@example.com',
            'password' => bcrypt('secret'),
            'extras' => 'allowed',
        ]);
        $user->delete();

        $response = $this->get("{$this->testBaseUrl}/{$user->id}/show");

        $response->assertStatus(200);
    }

    /**
     * A soft-deleted entry excluded by the panel query constraint should not be found (404).
     */
    public function test_show_with_soft_deletes_returns_404_for_soft_deleted_entry_excluded_by_panel_query()
    {
        $user = UserWithSoftDeletes::create([
            'name' => 'Excluded User',
            'email' => 'excluded@example.com',
            'password' => bcrypt('secret'),
            'extras' => 'not-allowed',
        ]);
        $user->delete();

        $response = $this->get("{$this->testBaseUrl}/{$user->id}/show");

        $response->assertStatus(404);
    }
}
