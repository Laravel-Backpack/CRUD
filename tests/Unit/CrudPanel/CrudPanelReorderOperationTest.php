<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Models\Reorder;
use Illuminate\Support\Facades\DB;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Reorder
 */
class CrudPanelReorderOperationTest extends BaseDBCrudPanel
{
    public function testSaveReorderTree()
    {
        $this->createReorderItems();
        $this->crudPanel->setModel(Reorder::class);

        $this->crudPanel->setOperationSetting('reorderColumnNames', [
            'parent_id' => 'parent_id',
            'depth' => 'depth',
            'lft' => 'lft',
            'rgt' => 'rgt',
        ]);

        $this->crudPanel->updateTreeOrder(
            [
                [
                    'item_id' => 1,
                    'parent_id' => null,
                    'depth' => 1,
                    'left' => 2,
                    'right' => 7,
                ],
                [
                    'item_id' => 2,
                    'parent_id' => 1,
                    'depth' => 2,
                    'left' => 3,
                    'right' => 4,
                ],
                [
                    'item_id' => 3,
                    'parent_id' => 1,
                    'depth' => 2,
                    'left' => 5,
                    'right' => 6,
                ],
            ]
        );

        $this->assertDatabaseHas('reorders', [
            'id' => 1,
            'parent_id' => null,
            'lft' => 2,
            'rgt' => 7,
            'depth' => 1,
        ]);
    }

    public function testSaveReorderTreeOnlyAcceptsExactKeysInScope()
    {
        $this->createReorderItems();
        $this->crudPanel->setModel(Reorder::class);
        $this->crudPanel->addClause('whereIn', 'id', [2, 3]);

        $this->crudPanel->setOperationSetting('reorderColumnNames', [
            'parent_id' => 'parent_id',
            'depth' => 'depth',
            'lft' => 'lft',
            'rgt' => 'rgt',
        ]);

        $this->crudPanel->updateTreeOrder([
            ['item_id' => 2, 'parent_id' => true, 'depth' => 2, 'left' => 2, 'right' => 3],
            ['item_id' => 3, 'parent_id' => '1', 'depth' => 2, 'left' => 4, 'right' => 5],
            ['item_id' => true, 'parent_id' => null, 'depth' => 1, 'left' => 6, 'right' => 7],
        ]);

        $this->assertDatabaseHas('reorders', ['id' => 1, 'parent_id' => null, 'lft' => null]);
        $this->assertDatabaseHas('reorders', ['id' => 2, 'parent_id' => null, 'lft' => 2]);
        $this->assertDatabaseHas('reorders', ['id' => 3, 'parent_id' => null, 'lft' => 4]);
    }

    private function createReorderItems()
    {
        DB::table('reorders')->insert([
            [
                'id' => 1,
                'name' => 'Item 1',
                'parent_id' => null,
                'lft' => null,
                'rgt' => null,
                'depth' => null,
            ],
            [
                'id' => 2,
                'name' => 'Item 2',
                'parent_id' => null,
                'lft' => null,
                'rgt' => null,
                'depth' => null,
            ],
            [
                'id' => 3,
                'name' => 'Item 3',
                'parent_id' => null,
                'lft' => null,
                'rgt' => null,
                'depth' => null,
            ],
        ]);
    }
}
