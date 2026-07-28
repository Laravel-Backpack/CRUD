<?php

namespace Backpack\CRUD\Tests\Unit\Database;

use Backpack\CRUD\app\Library\Database\TableSchema;
use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;

class DatabaseSchemaTest extends BaseDBCrudPanel
{
    public function testGetForTableReturnsTableSchema()
    {
        $schema = app('DatabaseSchema')->getForTable('schema_test_models', 'testing');

        $this->assertNotNull($schema);
        $this->assertInstanceOf(\Backpack\CRUD\app\Library\Database\Table::class, $schema);
    }

    public function testGetForTableReturnsEmptyTableForNonexistentTable()
    {
        $schema = app('DatabaseSchema')->getForTable('nonexistent_table', 'testing');

        // Returns a Table object with empty columns, not null
        $this->assertInstanceOf(\Backpack\CRUD\app\Library\Database\Table::class, $schema);
        $this->assertEmpty($schema->getColumns());
    }

    public function testListTableColumnsNamesReturnsColumnNames()
    {
        $columns = app('DatabaseSchema')->listTableColumnsNames('testing', 'schema_test_models');

        $this->assertContains('name', $columns);
        $this->assertContains('title', $columns);
        $this->assertContains('description', $columns);
        $this->assertContains('age', $columns);
        $this->assertContains('active', $columns);
        $this->assertContains('price', $columns);
        $this->assertContains('metadata', $columns);
        $this->assertContains('published_at', $columns);
        $this->assertContains('status', $columns);
        $this->assertContains('notes', $columns);
        $this->assertContains('sku', $columns);
        $this->assertContains('email', $columns);
        $this->assertContains('user_id', $columns);
        $this->assertContains('code', $columns);
        $this->assertNotEmpty($columns);
        $this->assertIsArray($columns);
    }

    public function testListTableIndexesReturnsIndexedColumnNames()
    {
        $indexes = app('DatabaseSchema')->listTableIndexes('testing', 'schema_test_models');

        // Primary key column should be in indexes
        $this->assertContains('id', $indexes);
        // Unique index column
        $this->assertContains('email', $indexes);
        // Indexed column
        $this->assertContains('user_id', $indexes);
    }

    public function testGetTablesReturnsArrayOfTables()
    {
        $tables = app('DatabaseSchema')->getTables('testing');

        $this->assertIsArray($tables);
        $this->assertArrayHasKey('schema_test_models', $tables);
    }

    public function testGetManagerReturnsSchemaBuilder()
    {
        $manager = app('DatabaseSchema')->getManager('testing');

        $this->assertInstanceOf(\Illuminate\Database\Schema\Builder::class, $manager);
    }

    // TableSchema wrapper tests

    public function testTableSchemaGetColumnType()
    {
        $schema = new TableSchema('testing', 'schema_test_models');

        $this->assertEquals('varchar', $schema->getColumnType('name'));
        $this->assertEquals('integer', $schema->getColumnType('age'));
        // SQLite reports boolean as tinyint
        $this->assertContains($schema->getColumnType('active'), ['boolean', 'tinyint']);
        // SQLite reports decimal as numeric, json as text
        $this->assertContains($schema->getColumnType('price'), ['decimal', 'numeric']);
        $this->assertContains($schema->getColumnType('metadata'), ['json', 'text']);
        $this->assertEquals('date', $schema->getColumnType('published_at'));
        $this->assertEquals('text', $schema->getColumnType('description'));
    }

    public function testTableSchemaGetColumnTypeReturnsVarcharForUnknownColumn()
    {
        $schema = new TableSchema('testing', 'schema_test_models');

        $this->assertEquals('varchar', $schema->getColumnType('nonexistent_column'));
    }

    public function testTableSchemaHasColumn()
    {
        $schema = new TableSchema('testing', 'schema_test_models');

        $this->assertTrue($schema->hasColumn('name'));
        $this->assertTrue($schema->hasColumn('age'));
        $this->assertFalse($schema->hasColumn('nonexistent_column'));
    }

    public function testTableSchemaColumnIsNullable()
    {
        $schema = new TableSchema('testing', 'schema_test_models');

        // Nullable columns
        $this->assertTrue($schema->columnIsNullable('title'));
        $this->assertTrue($schema->columnIsNullable('notes'));
        $this->assertTrue($schema->columnIsNullable('price'));
        $this->assertTrue($schema->columnIsNullable('metadata'));

        // Non-nullable columns
        $this->assertFalse($schema->columnIsNullable('name'));
        $this->assertFalse($schema->columnIsNullable('age'));
        $this->assertFalse($schema->columnIsNullable('sku'));
    }

    public function testTableSchemaColumnIsNullableReturnsTrueForUnknownColumn()
    {
        $schema = new TableSchema('testing', 'schema_test_models');

        $this->assertTrue($schema->columnIsNullable('nonexistent_column'));
    }

    public function testTableSchemaColumnHasDefault()
    {
        $schema = new TableSchema('testing', 'schema_test_models');

        $this->assertTrue($schema->columnHasDefault('age'));
        $this->assertTrue($schema->columnHasDefault('active'));
        $this->assertTrue($schema->columnHasDefault('status'));
        $this->assertTrue($schema->columnHasDefault('code'));
        $this->assertFalse($schema->columnHasDefault('name'));
        $this->assertFalse($schema->columnHasDefault('sku'));
    }

    public function testTableSchemaColumnHasDefaultReturnsFalseForUnknownColumn()
    {
        $schema = new TableSchema('testing', 'schema_test_models');

        $this->assertFalse($schema->columnHasDefault('nonexistent_column'));
    }

    public function testTableSchemaGetColumnDefault()
    {
        $schema = new TableSchema('testing', 'schema_test_models');

        $ageDefault = $schema->getColumnDefault('age');
        $this->assertNotNull($ageDefault);
        $this->assertContains($ageDefault, ['0', "'0'"]);

        $activeDefault = $schema->getColumnDefault('active');
        $this->assertNotNull($activeDefault);
        $this->assertContains($activeDefault, ['1', "'1'"]);

        $this->assertEquals('draft', str_replace("'", '', (string) $schema->getColumnDefault('status')));
        $this->assertEquals('ABC', str_replace("'", '', (string) $schema->getColumnDefault('code')));
    }

    public function testTableSchemaGetColumnDefaultReturnsNullForNullableWithNullDefault()
    {
        $schema = new TableSchema('testing', 'schema_test_models');

        $this->assertNull($schema->getColumnDefault('price'));
    }

    public function testTableSchemaGetColumnDefaultReturnsFalseForColumnWithoutDefault()
    {
        $schema = new TableSchema('testing', 'schema_test_models');

        // Column exists but has no default → returns null
        $this->assertNull($schema->getColumnDefault('name'));
    }

    public function testTableSchemaGetColumnDefaultReturnsFalseForUnknownColumn()
    {
        $schema = new TableSchema('testing', 'schema_test_models');

        $this->assertFalse($schema->getColumnDefault('nonexistent_column'));
    }

    public function testTableSchemaGetColumnsNames()
    {
        $schema = new TableSchema('testing', 'schema_test_models');
        $names = $schema->getColumnsNames();

        $this->assertContains('name', $names);
        $this->assertContains('title', $names);
        $this->assertContains('age', $names);
        $this->assertContains('active', $names);
        $this->assertContains('status', $names);
        $this->assertContains('sku', $names);
        $this->assertContains('email', $names);
        $this->assertNotEmpty($names);
    }

    public function testTableSchemaGetColumns()
    {
        $schema = new TableSchema('testing', 'schema_test_models');
        $columns = $schema->getColumns();

        $this->assertIsArray($columns);
        $this->assertNotEmpty($columns);
        $this->assertArrayHasKey('name', $columns);
    }
}
