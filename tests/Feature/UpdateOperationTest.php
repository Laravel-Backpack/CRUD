<?php

namespace Backpack\CRUD\Tests\Feature;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Http\Controllers\UserAccessConditionCrudController;
use Backpack\CRUD\Tests\config\Models\User;

/**
 * @covers Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation
 */
class UpdateOperationTest extends BaseDBCrudPanel
{
    protected string $testBaseUrl;

    protected function defineRoutes($router)
    {
        $router->crud(config('backpack.base.route_prefix').'/users-access-condition', UserAccessConditionCrudController::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->testBaseUrl = config('backpack.base.route_prefix').'/users-access-condition';
        $this->actingAs(User::find(1));
    }

    public function test_update_saves_the_entry_in_the_route()
    {
        $this->put("{$this->testBaseUrl}/1", ['name' => 'updated'])->assertRedirect();

        $this->assertSame('updated', User::find(1)->name);
    }

    public function test_update_is_denied_when_the_entry_in_the_route_is_not_accessible()
    {
        $originalName = User::find(2)->name;

        $this->put("{$this->testBaseUrl}/2", ['name' => 'updated'])->assertForbidden();
        $this->put("{$this->testBaseUrl}/2", ['user' => 1, 'name' => 'updated'])->assertForbidden();

        $this->assertSame($originalName, User::find(2)->name);
    }

    public function test_update_ignores_a_different_key_in_the_request_body()
    {
        $originalName = User::find(2)->name;

        $this->put("{$this->testBaseUrl}/1", ['id' => 2, 'name' => 'updated'])->assertRedirect();

        $this->assertSame($originalName, User::find(2)->name);
        $this->assertSame('updated', User::find(1)->name);
    }

    public function test_update_ignores_a_key_named_after_the_entity()
    {
        $originalName = User::find(2)->name;

        $this->put("{$this->testBaseUrl}/2", ['user' => 1, 'id' => 2, 'name' => 'updated'])->assertForbidden();
        $this->put("{$this->testBaseUrl}/2?user=1", ['id' => 2, 'name' => 'updated'])->assertForbidden();

        $this->assertSame($originalName, User::find(2)->name);
    }

    public function test_delete_ignores_a_key_named_after_the_entity()
    {
        $this->delete("{$this->testBaseUrl}/2", ['user' => 1])->assertForbidden();
        $this->delete("{$this->testBaseUrl}/2?user=1")->assertForbidden();

        $this->assertNotNull(User::find(1));
        $this->assertNotNull(User::find(2));
    }
}
