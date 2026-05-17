<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\app\Library\CrudPanel\CrudColumn;
use Backpack\CRUD\Tests\Unit\Models\User;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Columns
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\ColumnsProtectedMethods
 * @covers Backpack\CRUD\app\Library\CrudPanel\CrudColumn
 * @covers Backpack\CRUD\app\Library\CrudPanel\CrudPanel
 */
class CrudPanelColumnsTest extends BaseDBCrudPanelTest
{
    private $oneColumnArray = [
        'name' => 'column1',
        'label' => 'Column1',
    ];

    private $expectedOneColumnArray = [
        'column1' => [
            'label' => 'Column1',
            'name' => 'column1',
            'key' => 'column1',
            'type' => 'text',
            'tableColumn' => false,
            'orderable' => false,
            'searchLogic' => false,
            'priority' => 0,
        ],
    ];

    private $otherOneColumnArray = [
        'name' => 'column4',
        'label' => 'Column4',
    ];

    private $twoColumnsArray = [
        [
            'name' => 'column1',
            'label' => 'Column1',
        ],
        [
            'name' => 'column2',
            'label' => 'Column2',
        ],
    ];

    private $expectedTwoColumnsArray = [
        'column1' => [
            'name' => 'column1',
            'key' => 'column1',
            'label' => 'Column1',
            'type' => 'text',
            'tableColumn' => false,
            'orderable' => false,
            'searchLogic' => false,
            'priority' => 0,

        ],
        'column2' => [
            'name' => 'column2',
            'key' => 'column2',
            'label' => 'Column2',
            'type' => 'text',
            'tableColumn' => false,
            'orderable' => false,
            'searchLogic' => false,
            'priority' => 1,
        ],
    ];

    private $threeColumnsArray = [
        [
            'name' => 'column1',
            'label' => 'Column1',
        ],
        [
            'name' => 'column2',
            'label' => 'Column2',
        ],
        [
            'name' => 'column3',
            'label' => 'Column3',
        ],
    ];

    private $expectedThreeColumnsArray = [
        'column1' => [
            'name' => 'column1',
            'key' => 'column1',
            'label' => 'Column1',
            'type' => 'text',
            'tableColumn' => false,
            'orderable' => false,
            'searchLogic' => false,
            'priority' => 0,
        ],
        'column2' => [
            'name' => 'column2',
            'key' => 'column2',
            'label' => 'Column2',
            'type' => 'text',
            'tableColumn' => false,
            'orderable' => false,
            'searchLogic' => false,
            'priority' => 1,
        ],
        'column3' => [
            'name' => 'column3',
            'key' => 'column3',
            'label' => 'Column3',
            'type' => 'text',
            'tableColumn' => false,
            'orderable' => false,
            'searchLogic' => false,
            'priority' => 2,
        ],
    ];

    private $expectedRelationColumnsArrayWithoutPro = [
        'accountDetails' => [
            'name' => 'accountDetails',
            'label' => 'AccountDetails',
            'type' => 'text',
            'key' => 'accountDetails',
            'priority' => 0,
            'tableColumn' => false,
            'orderable' => false,
            'searchLogic' => false,
            'entity' => 'accountDetails',
            'model' => 'Backpack\CRUD\Tests\Unit\Models\AccountDetails',
            'relation_type' => 'HasOne',
            'attribute' => 'nickname',
        ],
        'accountDetails__nickname' => [
            'name' => 'accountDetails.nickname',
            'label' => 'AccountDetails.nickname',
            'type' => 'text',
            'key' => 'accountDetails__nickname',
            'priority' => 1,
            'attribute' => 'nickname',
            'tableColumn' => false,
            'orderable' => false,
            'searchLogic' => false,
            'relation_type' => 'HasOne',
            'entity' => 'accountDetails.nickname',
            'model' => 'Backpack\CRUD\Tests\Unit\Models\AccountDetails',
        ],
        'accountDetails__user' => [
            'name' => 'accountDetails.user',
            'label' => 'AccountDetails.user',
            'type' => 'select',
            'key' => 'accountDetails__user',
            'priority' => 2,
            'tableColumn' => false,
            'orderable' => false,
            'searchLogic' => false,
            'relation_type' => 'BelongsTo',
            'entity' => 'accountDetails.user',
            'model' => 'Backpack\CRUD\Tests\Unit\Models\User',
            'attribute' => 'name',
        ],
    ];

    private $expectedRelationColumnsArrayWithPro = [
        'accountDetails' => [
            'name' => 'accountDetails',
            'label' => 'AccountDetails',
            'type' => 'relationship',
            'key' => 'accountDetails',
            'priority' => 0,
            'tableColumn' => false,
            'orderable' => false,
            'searchLogic' => false,
            'entity' => 'accountDetails',
            'model' => 'Backpack\CRUD\Tests\Unit\Models\AccountDetails',
            'relation_type' => 'HasOne',
            'attribute' => 'nickname',
        ],
        'accountDetails__nickname' => [
            'name' => 'accountDetails.nickname',
            'label' => 'AccountDetails.nickname',
            'type' => 'relationship',
            'key' => 'accountDetails__nickname',
            'priority' => 1,
            'attribute' => 'nickname',
            'tableColumn' => false,
            'orderable' => false,
            'searchLogic' => false,
            'relation_type' => 'HasOne',
            'entity' => 'accountDetails.nickname',
            'model' => 'Backpack\CRUD\Tests\Unit\Models\AccountDetails',
        ],
        'accountDetails__user' => [
            'name' => 'accountDetails.user',
            'label' => 'AccountDetails.user',
            'type' => 'relationship',
            'key' => 'accountDetails__user',
            'priority' => 2,
            'tableColumn' => false,
            'orderable' => false,
            'searchLogic' => false,
            'relation_type' => 'BelongsTo',
            'entity' => 'accountDetails.user',
            'model' => 'Backpack\CRUD\Tests\Unit\Models\User',
            'attribute' => 'name',
        ],
    ];

    private $relationColumnArray = [
        'name' => 'nickname',
        'type' => 'select',
        'entity' => 'accountDetails',
        'attribute' => 'nickname',
    ];

    private $expectedRelationColumnArray = [
        'nickname' => [
            'name' => 'nickname',
            'type' => 'select',
            'entity' => 'accountDetails',
            'attribute' => 'nickname',
            'label' => 'Nickname',
            'model' => 'Backpack\CRUD\Tests\Unit\Models\AccountDetails',
            'key' => 'nickname',
            'tableColumn' => false,
            'orderable' => false,
            'searchLogic' => false,
            'priority' => 0,
            'relation_type' => 'HasOne',
        ],
    ];

    private $nestedRelationColumnArray = [
        'name' => 'accountDetails.article',
    ];

    private $secondNestedRelationColumnArray = [
        'name' => 'accountDetails.article',
        'attribute' => 'content',
        'key' => 'ac_article_content',
    ];

    private $expectedNestedRelationColumnArrayWithPro = [
        'accountDetails__article' => [
            'name' => 'accountDetails.article',
            'type' => 'relationship',
            'entity' => 'accountDetails.article',
            'label' => 'AccountDetails.article',
            'model' => 'Backpack\CRUD\Tests\Unit\Models\Article',
            'key' => 'accountDetails__article',
            'tableColumn' => false,
            'orderable' => false,
            'searchLogic' => false,
            'priority' => 0,
            'relation_type' => 'BelongsTo',
            'attribute' => 'content',
        ],
        'ac_article_content' => [
            'name' => 'accountDetails.article',
            'type' => 'relationship',
            'entity' => 'accountDetails.article',
            'label' => 'AccountDetails.article',
            'model' => 'Backpack\CRUD\Tests\Unit\Models\Article',
            'key' => 'ac_article_content',
            'tableColumn' => false,
            'orderable' => false,
            'searchLogic' => false,
            'priority' => 1,
            'relation_type' => 'BelongsTo',
            'attribute' => 'content',
        ],
    ];

    private $expectedNestedRelationColumnArrayWithoutPro = [
        'accountDetails__article' => [
            'name' => 'accountDetails.article',
            'type' => 'select',
            'entity' => 'accountDetails.article',
            'label' => 'AccountDetails.article',
            'model' => 'Backpack\CRUD\Tests\Unit\Models\Article',
            'key' => 'accountDetails__article',
            'tableColumn' => false,
            'orderable' => false,
            'searchLogic' => false,
            'priority' => 0,
            'relation_type' => 'BelongsTo',
            'attribute' => 'content',
        ],
        'ac_article_content' => [
            'name' => 'accountDetails.article',
            'type' => 'select',
            'entity' => 'accountDetails.article',
            'label' => 'AccountDetails.article',
            'model' => 'Backpack\CRUD\Tests\Unit\Models\Article',
            'key' => 'ac_article_content',
            'tableColumn' => false,
            'orderable' => false,
            'searchLogic' => false,
            'priority' => 1,
            'relation_type' => 'BelongsTo',
            'attribute' => 'content',
        ],
    ];

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->crudPanel->setOperation('list');
    }

    public function testAddColumnByName()
    {
        $this->crudPanel->addColumn('column1');

        $this->assertEquals($this->expectedOneColumnArray, $this->crudPanel->columns());
    }

    public function testAddColumnsByName()
    {
        $this->crudPanel->addColumns(['column1', 'column2']);

        $this->assertEquals(2, count($this->crudPanel->columns()));
        $this->assertEquals($this->expectedTwoColumnsArray, $this->crudPanel->columns());
    }

    public function testAddColumnAsArray()
    {
        $this->crudPanel->addColumn($this->oneColumnArray);

        $this->assertEquals($this->expectedOneColumnArray, $this->crudPanel->columns());
    }

    public function testAddColumnsAsArray()
    {
        $this->crudPanel->addColumns($this->twoColumnsArray);

        $this->assertEquals(2, count($this->crudPanel->columns()));
        $this->assertEquals($this->expectedTwoColumnsArray, $this->crudPanel->columns());
    }

    public function testAddColumnNotArray()
    {
        $this->expectException(PHP_MAJOR_VERSION == 7 ? \ErrorException::class : \TypeError::class);
        // Why? When calling count() on a non-countable entity,
        // PHP 7.x will through ErrorException, but PHP 8.x will throw TypeError.

        $this->crudPanel->addColumns('column1');
    }

    public function testAddRelationsByName()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addColumn('accountDetails');
        $this->crudPanel->addColumn('accountDetails.nickname');
        $this->crudPanel->addColumn('accountDetails.user');

        if (backpack_pro()) {
            $this->assertEquals($this->expectedRelationColumnsArrayWithPro, $this->crudPanel->columns());
        } else {
            $this->assertEquals($this->expectedRelationColumnsArrayWithoutPro, $this->crudPanel->columns());
        }
    }

    public function testAddRelationColumn()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addColumn($this->relationColumnArray);

        $this->assertEquals($this->expectedRelationColumnArray, $this->crudPanel->columns());
    }

    public function testAddNestedRelationColumn()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addColumn($this->nestedRelationColumnArray);
        $this->crudPanel->addColumn($this->secondNestedRelationColumnArray);

        if (backpack_pro()) {
            $this->assertEquals($this->expectedNestedRelationColumnArrayWithPro, $this->crudPanel->columns());
        } else {
            $this->assertEquals($this->expectedNestedRelationColumnArrayWithoutPro, $this->crudPanel->columns());
        }
    }

    public function testMoveColumnBefore()
    {
        $this->crudPanel->addColumns($this->twoColumnsArray);

        $this->crudPanel->beforeColumn('column1');

        $keys = array_keys($this->crudPanel->columns());
        $this->assertEquals($this->expectedTwoColumnsArray['column2'], $this->crudPanel->columns()[$keys[0]]);
        $this->assertEquals(['column2', 'column1'], $keys);
    }

    public function testMoveColumnBeforeUnknownColumnName()
    {
        $this->crudPanel->addColumns($this->twoColumnsArray);

        $this->crudPanel->beforeColumn('column3');

        $this->assertEquals(array_keys($this->expectedTwoColumnsArray), array_keys($this->crudPanel->columns()));
    }

    public function testMoveColumnAfter()
    {
        $this->crudPanel->addColumns($this->threeColumnsArray);

        $this->crudPanel->afterColumn('column1');

        $keys = array_keys($this->crudPanel->columns());
        $this->assertEquals($this->expectedThreeColumnsArray['column3'], $this->crudPanel->columns()[$keys[1]]);
        $this->assertEquals(['column1', 'column3', 'column2'], $keys);
    }

    public function testMoveColumnAfterUnknownColumnName()
    {
        $this->crudPanel->addColumns($this->twoColumnsArray);

        $this->crudPanel->afterColumn('column3');

        $this->assertEquals(array_keys($this->expectedTwoColumnsArray), array_keys($this->crudPanel->columns()));
    }

    public function testRemoveColumnByName()
    {
        $this->crudPanel->addColumns(['column1', 'column2', 'column3']);

        $this->crudPanel->removeColumn('column1');

        $this->assertEquals(2, count($this->crudPanel->columns()));
        $this->assertEquals(['column2', 'column3'], array_keys($this->crudPanel->columns()));
        $this->assertNotContains($this->oneColumnArray, $this->crudPanel->columns());
    }

    public function testRemoveUnknownColumnName()
    {
        $unknownColumnName = 'column4';
        $this->crudPanel->addColumns(['column1', 'column2', 'column3']);

        $this->crudPanel->removeColumn($unknownColumnName);

        $this->assertEquals(3, count($this->crudPanel->columns()));
        $this->assertEquals(['column1', 'column2', 'column3'], array_keys($this->crudPanel->columns()));
        $this->assertNotContains($this->otherOneColumnArray, $this->crudPanel->columns());
    }

    public function testRemoveColumnsByName()
    {
        $this->crudPanel->addColumns(['column1', 'column2', 'column3']);

        $this->crudPanel->removeColumns($this->twoColumnsArray);

        $this->assertEquals(1, count($this->crudPanel->columns()));
        $this->assertEquals(['column3'], array_keys($this->crudPanel->columns()));
        $this->assertNotEquals($this->expectedThreeColumnsArray, $this->crudPanel->columns());
    }

    public function testRemoveUnknownColumnsByName()
    {
        $unknownColumnNames = ['column4', 'column5'];
        $this->crudPanel->addColumns(['column1', 'column2', 'column3']);

        $this->crudPanel->removeColumns($unknownColumnNames);

        $this->assertEquals(3, count($this->crudPanel->columns()));
        $this->assertEquals(['column1', 'column2', 'column3'], array_keys($this->crudPanel->columns()));
        $this->assertNotContains($this->otherOneColumnArray, $this->crudPanel->columns());
    }

    public function testSetColumnDetails()
    {
        $this->markTestIncomplete('Not correctly implemented');

        // TODO: refactor crud panel sync method
    }

    public function testSetColumnsDetails()
    {
        $this->markTestIncomplete('Not correctly implemented');

        // TODO: refactor crud panel sync method
    }

    public function testOrderColumns()
    {
        $this->crudPanel->addColumns($this->threeColumnsArray);

        $this->crudPanel->orderColumns(['column2', 'column1', 'column3']);

        $this->assertEquals(['column2', 'column1', 'column3'], array_keys($this->crudPanel->columns()));
    }

    public function testOrderColumnsIncompleteList()
    {
        $this->crudPanel->addColumns($this->threeColumnsArray);

        $this->crudPanel->orderColumns(['column2', 'column3']);

        $this->assertEquals(['column2', 'column3', 'column1'], array_keys($this->crudPanel->columns()));
    }

    public function testOrderColumnsEmptyList()
    {
        $this->crudPanel->addColumns($this->threeColumnsArray);

        $this->crudPanel->orderColumns([]);

        $this->assertEquals($this->expectedThreeColumnsArray, $this->crudPanel->columns());
    }

    public function testOrderColumnsUnknownList()
    {
        $this->crudPanel->addColumns($this->threeColumnsArray);

        $this->crudPanel->orderColumns(['column4', 'column5', 'column6']);

        $this->assertEquals($this->expectedThreeColumnsArray, $this->crudPanel->columns());
    }

    public function testOrderColumnsMixedList()
    {
        $this->crudPanel->addColumns($this->threeColumnsArray);

        $this->crudPanel->orderColumns(['column2', 'column5', 'column6']);

        $this->assertEquals(['column2', 'column1', 'column3'], array_keys($this->crudPanel->columns()));
    }

    public function testItCanChangeTheColumnKey()
    {
        $this->crudPanel->column('test');

        $this->assertEquals('test', $this->crudPanel->columns()['test']['key']);

        $this->crudPanel->column('test')->key('new_key');

        $this->assertEquals('new_key', $this->crudPanel->columns()['new_key']['key']);
    }

    public function testItCanAddAFluentColumn()
    {
        $this->crudPanel->setModel(User::class);

        $this->crudPanel->column('my_column')->label('my_column');

        $this->assertCount(1, $this->crudPanel->columns());

        $this->assertEquals([
            'name' => 'my_column',
            'type' => 'text',
            'label' => 'my_column',
            'key' => 'my_column',
            'priority' => 0,
            'tableColumn' => false,
            'orderable' => false,
            'searchLogic' => false,
        ], $this->crudPanel->columns()['my_column']);
    }

    public function testItCanMakeAColumnFirstFluently()
    {
        $this->crudPanel->column('test1');
        $this->crudPanel->column('test2')->makeFirst();
        $crudColumns = $this->crudPanel->columns();
        $firstColumn = reset($crudColumns);
        $this->assertEquals($firstColumn['name'], 'test2');
    }

    public function testItCanMakeAColumnLastFluently()
    {
        $this->crudPanel->column('test1');
        $this->crudPanel->column('test2');
        $this->crudPanel->column('test1')->makeLast();
        $crudColumns = $this->crudPanel->columns();
        $firstColumn = reset($crudColumns);
        $this->assertEquals($firstColumn['name'], 'test2');
    }

    public function testItCanPlaceColumnsFluently()
    {
        $this->crudPanel->column('test1');
        $this->crudPanel->column('test2');
        $this->crudPanel->column('test3')->after('test1');

        $crudColumnsNames = array_column($this->crudPanel->columns(), 'name');
        $this->assertEquals($crudColumnsNames, ['test1', 'test3', 'test2']);

        $this->crudPanel->column('test4')->before('test1');
        $crudColumnsNames = array_column($this->crudPanel->columns(), 'name');
        $this->assertEquals($crudColumnsNames, ['test4', 'test1', 'test3', 'test2']);
    }

    public function testItCanRemoveColumnAttributesFluently()
    {
        $this->crudPanel->column('test1')->type('test');
        $this->assertEquals($this->crudPanel->columns()['test1']['type'], 'test');
        $this->crudPanel->column('test1')->forget('type');
        $this->assertNull($this->crudPanel->columns()['test1']['type'] ?? null);
    }

    public function testItCanRemoveColumnFluently()
    {
        $this->crudPanel->column('test1')->type('test');
        $this->assertCount(1, $this->crudPanel->columns());
        $this->crudPanel->column('test1')->remove();
        $this->assertCount(0, $this->crudPanel->columns());
    }

    public function testItCanAddAColumnToCrudFromClass()
    {
        CrudColumn::name('test');
        $this->assertCount(1, $this->crudPanel->columns());
    }
}
