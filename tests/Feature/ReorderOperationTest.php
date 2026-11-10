<?php

namespace Backpack\CRUD\Tests\Feature;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Http\Controllers\ReorderCrudController;
use Backpack\CRUD\Tests\config\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * @covers Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Reorder
 */
class ReorderOperationTest extends BaseDBCrudPanel
{
    protected string $testBaseUrl;

    protected function defineRoutes($router)
    {
        $router->crud(config('backpack.base.route_prefix').'/reorder-items', ReorderCrudController::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->testBaseUrl = config('backpack.base.route_prefix').'/reorder-items/reorder';
        $this->actingAs(User::find(1));

        foreach ([1, 2, 3] as $id) {
            DB::table('reorders')->insert(['id' => $id, 'name' => 'Item '.$id]);
        }
    }

    public function test_reorder_saves_the_tree()
    {
        $this->post($this->testBaseUrl, ['tree' => json_encode([
            ['item_id' => 1, 'parent_id' => null, 'depth' => 1, 'left' => 2, 'right' => 5],
            ['item_id' => 2, 'parent_id' => 1, 'depth' => 2, 'left' => 3, 'right' => 4],
        ])])->assertOk();

        $this->assertDatabaseHas('reorders', ['id' => 2, 'parent_id' => 1, 'depth' => 2]);
    }

    public function test_reorder_cannot_be_saved_when_disabled()
    {
        $this->post($this->testBaseUrl.'?disabled=1', ['tree' => json_encode([
            ['item_id' => 2, 'parent_id' => 1, 'depth' => 2, 'left' => 3, 'right' => 4],
        ])])->assertForbidden();

        $this->assertDatabaseHas('reorders', ['id' => 2, 'parent_id' => null]);
    }

    public function test_reorder_cannot_go_deeper_than_the_max_level()
    {
        $this->post($this->testBaseUrl, ['tree' => json_encode([
            ['item_id' => 1, 'parent_id' => null, 'depth' => 1, 'left' => 2, 'right' => 7],
            ['item_id' => 2, 'parent_id' => 1, 'depth' => 2, 'left' => 3, 'right' => 6],
            ['item_id' => 3, 'parent_id' => 2, 'depth' => 3, 'left' => 4, 'right' => 5],
        ])])->assertStatus(422);

        $this->assertDatabaseHas('reorders', ['id' => 3, 'parent_id' => null]);
    }

    public function test_reorder_cannot_save_a_tree_with_loops()
    {
        $this->post($this->testBaseUrl, ['tree' => json_encode([
            ['item_id' => 1, 'parent_id' => 2, 'depth' => 1, 'left' => 2, 'right' => 3],
            ['item_id' => 2, 'parent_id' => 1, 'depth' => 1, 'left' => 4, 'right' => 5],
        ])])->assertStatus(422);

        $this->assertDatabaseHas('reorders', ['id' => 1, 'parent_id' => null]);
    }
}
