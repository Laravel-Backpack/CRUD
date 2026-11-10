<?php

namespace Backpack\CRUD\Tests\Feature;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Http\Controllers\UserFormOperationCrudController;
use Backpack\CRUD\Tests\config\Models\User;

/**
 * @covers Backpack\CRUD\app\Http\Controllers\Operations\Concerns\HasForm
 */
class FormOperationTest extends BaseDBCrudPanel
{
    protected string $testBaseUrl;

    protected function defineRoutes($router)
    {
        $router->crud(config('backpack.base.route_prefix').'/users-form-operation', UserFormOperationCrudController::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->testBaseUrl = config('backpack.base.route_prefix').'/users-form-operation';
        $this->actingAs(User::find(1));
    }

    public function test_form_action_saves_an_accessible_entry()
    {
        $this->post("{$this->testBaseUrl}/1/rename", ['name' => 'renamed'])->assertRedirect();

        $this->assertSame('renamed', User::find(1)->name);
    }

    public function test_form_is_denied_for_an_entry_that_is_not_accessible()
    {
        $originalName = User::find(2)->name;

        $this->get("{$this->testBaseUrl}/2/rename")->assertForbidden();
        $this->post("{$this->testBaseUrl}/2/rename", ['name' => 'renamed'])->assertForbidden();

        $this->assertSame($originalName, User::find(2)->name);
    }
}
