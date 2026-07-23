<?php

namespace Backpack\CRUD\Tests\Unit\Models;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Models\SchemaTestModel;

class HasRelationshipFieldsTest extends BaseDBCrudPanel
{
    public function testGetColumnTypeReturnsCorrectType()
    {
        $model = new SchemaTestModel();

        $this->assertEquals('varchar', $model->getColumnType('name'));
        $this->assertEquals('varchar', $model->getColumnType('status'));
        $this->assertEquals('integer', $model->getColumnType('age'));
        // SQLite reports boolean as tinyint
        $this->assertContains($model->getColumnType('active'), ['boolean', 'tinyint']);
        // SQLite reports decimal as numeric, json as text
        $this->assertContains($model->getColumnType('price'), ['decimal', 'numeric']);
        $this->assertContains($model->getColumnType('metadata'), ['json', 'text']);
        $this->assertEquals('date', $model->getColumnType('published_at'));
        $this->assertEquals('text', $model->getColumnType('description'));
    }

    public function testGetColumnTypeReturnsFallbackForUnknownColumn()
    {
        $model = new SchemaTestModel();

        // TableSchema returns 'varchar' as fallback for unknown columns on SQL connections
        $this->assertContains($model->getColumnType('nonexistent_column'), ['varchar', 'text']);
    }

    public function testIsColumnNullableDetectsNullableColumns()
    {
        $this->assertTrue(SchemaTestModel::isColumnNullable('title'));
        $this->assertTrue(SchemaTestModel::isColumnNullable('description'));
        $this->assertTrue(SchemaTestModel::isColumnNullable('price'));
        $this->assertTrue(SchemaTestModel::isColumnNullable('metadata'));
        $this->assertTrue(SchemaTestModel::isColumnNullable('published_at'));
        $this->assertTrue(SchemaTestModel::isColumnNullable('notes'));
    }

    public function testIsColumnNullableDetectsNonNullableColumns()
    {
        $this->assertFalse(SchemaTestModel::isColumnNullable('name'));
        $this->assertFalse(SchemaTestModel::isColumnNullable('age'));
        $this->assertFalse(SchemaTestModel::isColumnNullable('active'));
        $this->assertFalse(SchemaTestModel::isColumnNullable('status'));
        $this->assertFalse(SchemaTestModel::isColumnNullable('sku'));
        $this->assertFalse(SchemaTestModel::isColumnNullable('email'));
    }

    public function testIsColumnNullableReturnsTrueForUnknownColumn()
    {
        $this->assertTrue(SchemaTestModel::isColumnNullable('nonexistent_column'));
    }

    public function testDbColumnHasDefaultDetectsColumnsWithDefaults()
    {
        $this->assertTrue(SchemaTestModel::dbColumnHasDefault('age'));
        $this->assertTrue(SchemaTestModel::dbColumnHasDefault('active'));
        $this->assertTrue(SchemaTestModel::dbColumnHasDefault('status'));
        $this->assertTrue(SchemaTestModel::dbColumnHasDefault('code'));
    }

    public function testDbColumnHasDefaultDetectsColumnsWithoutDefaults()
    {
        $this->assertFalse(SchemaTestModel::dbColumnHasDefault('name'));
        $this->assertFalse(SchemaTestModel::dbColumnHasDefault('sku'));
        $this->assertFalse(SchemaTestModel::dbColumnHasDefault('email'));
        $this->assertFalse(SchemaTestModel::dbColumnHasDefault('notes'));
    }

    public function testDbColumnHasDefaultReturnsFalseForUnknownColumn()
    {
        $this->assertFalse(SchemaTestModel::dbColumnHasDefault('nonexistent_column'));
    }

    public function testGetDbColumnDefaultReturnsCorrectValues()
    {
        // SQLite may return defaults with surrounding quotes — check the actual value
        $ageDefault = SchemaTestModel::getDbColumnDefault('age');
        $this->assertNotNull($ageDefault);
        // The default is 0, but SQLite may return '0'
        $this->assertContains($ageDefault, ['0', "'0'"]);

        $activeDefault = SchemaTestModel::getDbColumnDefault('active');
        $this->assertNotNull($activeDefault);
        // The default is 1 (true), but SQLite may return '1'
        $this->assertContains($activeDefault, ['1', "'1'"]);

        $this->assertEquals('draft', str_replace("'", '', (string) SchemaTestModel::getDbColumnDefault('status')));
        $this->assertEquals('ABC', str_replace("'", '', (string) SchemaTestModel::getDbColumnDefault('code')));
    }

    public function testGetDbColumnDefaultReturnsNullForNullableColumnWithNullDefault()
    {
        $this->assertNull(SchemaTestModel::getDbColumnDefault('price'));
    }

    public function testGetDbColumnDefaultReturnsNullForColumnWithoutDefault()
    {
        // Column exists but has no default set → returns null (the raw DB value)
        $this->assertNull(SchemaTestModel::getDbColumnDefault('name'));
        $this->assertNull(SchemaTestModel::getDbColumnDefault('sku'));
    }

    public function testGetDbColumnDefaultReturnsFalseForUnknownColumn()
    {
        $this->assertFalse(SchemaTestModel::getDbColumnDefault('nonexistent_column'));
    }

    public function testGetTableWithPrefixReturnsPrefixedTableName()
    {
        $model = new SchemaTestModel();

        $expected = $model->getConnection()->getTablePrefix().'schema_test_models';

        $this->assertEquals($expected, $model->getTableWithPrefix());
    }

    public function testGetDbTableSchemaReturnsTableSchemaInstance()
    {
        $schema = SchemaTestModel::getDbTableSchema();

        $this->assertInstanceOf(\Backpack\CRUD\app\Library\Database\TableSchema::class, $schema);
    }

    public function testGetConnectionWithExtraTypeMappingsReturnsConnection()
    {
        $model = new SchemaTestModel();
        $connection = $model->getConnectionWithExtraTypeMappings();

        $this->assertInstanceOf(\Illuminate\Database\Connection::class, $connection);
    }
}
