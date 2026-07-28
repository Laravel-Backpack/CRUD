<?php

namespace Backpack\CRUD\Tests\Unit\Models;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Models\SchemaTestModel;

class HasIdentifiableAttributeTest extends BaseDBCrudPanel
{
    public function testIdentifiableAttributeReturnsPropertyWhenSet()
    {
        $model = new class extends SchemaTestModel
        {
            public $identifiableAttribute = 'custom_column';
        };

        $this->assertEquals('custom_column', $model->identifiableAttribute());
    }

    public function testIdentifiableAttributeGuessesNameFirst()
    {
        $model = new SchemaTestModel();

        // The table has a 'name' column, which is the first sensible default
        $this->assertEquals('name', $model->identifiableAttribute());
    }

    public function testGuessIdentifiableColumnNamePrefersSensibleDefaults()
    {
        $model = new SchemaTestModel();

        // 'name' is the highest priority sensible default and it exists
        $this->assertEquals('name', $model->identifiableAttribute());
    }
}
