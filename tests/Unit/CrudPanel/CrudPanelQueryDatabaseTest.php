<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\CrudPanel\NoSqlDriverCrudPanel;
use Backpack\CRUD\Tests\config\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Query
 */
class CrudPanelQueryDatabaseTest extends BaseDBCrudPanel
{
    public function setUp(): void
    {
        parent::setUp();

        $this->crudPanel->setModel(User::class);
    }

    public function testItCanGetTheQueryCount()
    {
        $this->assertEquals(User::query()->count(), $this->crudPanel->count());
    }

    public function testItDoesNotCountTheQueryAgainIfThereAreNoFilters()
    {
        $this->assertNull($this->crudPanel->getFilteredQueryCount());
    }

    public function testItCountTheQueryAgainIfThereAreFilters()
    {
        $this->crudPanel->addClause('where', 'id', 1);

        $this->assertEquals(User::query()->where('id', 1)->count(), $this->crudPanel->getFilteredQueryCount());
    }

    public function testRawExpressionsDontGetRemovedFromCount()
    {
        $this->crudPanel->addClause(fn ($query) => $query->selectRaw('id')->whereRaw('id = 1'));

        $this->assertEquals(1, $this->crudPanel->getFilteredQueryCount());
    }

    public function testItDoesNotPerformCountWhenCrudPanelDoesNoUseASqlDriver()
    {
        $this->crudPanel = new NoSqlDriverCrudPanel();
        $this->crudPanel->setModel(User::class);

        $this->assertNull($this->crudPanel->getFilteredQueryCount());
        $this->assertEquals(2, $this->crudPanel->getQueryCount());
    }

    public function testCountWorksWithGroupBy()
    {
        $this->crudPanel->addClause(fn ($query) => $query->groupBy('name'));

        $expected = User::query()->groupBy('name')->get()->count();

        $this->assertEquals($expected, $this->crudPanel->getFilteredQueryCount());
    }

    public function testCountWorksWithGroupByOnMultipleColumns()
    {
        $this->crudPanel->addClause(fn ($query) => $query->groupBy('name', 'email'));

        $expected = User::query()->groupBy('name', 'email')->get()->count();

        $this->assertEquals($expected, $this->crudPanel->getFilteredQueryCount());
    }

    public function testCountWithGroupByReturnsDistinctGroupCount()
    {
        DB::table('users')->insert([
            ['name' => 'Duplicate Name', 'email' => 'dup1@example.com', 'password' => 'x', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Duplicate Name', 'email' => 'dup2@example.com', 'password' => 'x', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Unique Name',    'email' => 'uniq@example.com', 'password' => 'x', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Scope to only our inserted rows so the seeded users don't interfere.
        $this->crudPanel->addClause(fn ($query) => $query
            ->whereIn('email', ['dup1@example.com', 'dup2@example.com', 'uniq@example.com'])
            ->groupBy('name')
        );

        // 2 distinct names ('Duplicate Name' and 'Unique Name').
        $this->assertEquals(2, $this->crudPanel->getFilteredQueryCount());
    }
}
