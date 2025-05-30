<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\config\Models\ColumnType;
use Exception;

class MyColumnTypeWithOtherConnection extends ColumnType
{
    protected $connection = 'testing_2';
}

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Autoset
 * @covers Backpack\CRUD\app\Library\CrudPanel\CrudPanel
 */
class CrudPanelAutoSetTest extends \Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanelWithSingleton
{
    private $expectedUnknownFieldType = 'text';

    private $expectedFieldTypeFromColumnType = [
        'bigIntegerCol' => 'number',
        'binaryCol' => 'text',
        'booleanCol' => 'boolean',
        'charCol' => 'text',
        'dateCol' => 'date',
        'dateTimeCol' => 'datetime',
        'dateTimeTzCol' => 'datetime',
        'decimalCol' => 'text',
        'doubleCol' => 'text',
        'enumCol' => 'text',
        'floatCol' => 'text',
        'integerCol' => 'number',
        'ipAddressCol' => 'text',
        'jsonCol' => 'textarea',
        'jsonbCol' => 'textarea',
        'longTextCol' => 'textarea',
        'macAddressCol' => 'text',
        'mediumIntegerCol' => 'number',
        'mediumTextCol' => 'textarea',
        'smallIntegerCol' => 'number',
        'stringCol' => 'text',
        'textCol' => 'textarea',
        'timeCol' => 'time',
        'timeTzCol' => 'time',
        'tinyIntegerCol' => 'number',
        'timestampCol' => 'datetime',
        'timestampTzCol' => 'datetime',
        'uuidCol' => 'text',
    ];

    private $expectedColumnTypesFromDb = [
        'bigIntegerCol' => [
            'type' => 'integer',
            'default' => '',
        ],
        'binaryCol' => [
            'type' => 'blob',
            'default' => '',
        ],
        'booleanCol' => [
            'type' => 'boolean',
            'default' => '',
        ],
        'charCol' => [
            'type' => 'string',
            'default' => '',
        ],
        'dateCol' => [
            'type' => 'date',
            'default' => '',
        ],
        'dateTimeCol' => [
            'type' => 'datetime',
            'default' => '',
        ],
        'dateTimeTzCol' => [
            'type' => 'datetime',
            'default' => '',
        ],
        'decimalCol' => [
            'type' => 'decimal',
            'default' => '',
        ],
        'doubleCol' => [
            'type' => 'float',
            'default' => '',
        ],
        'enumCol' => [
            'type' => 'string',
            'default' => '',
        ],
        'floatCol' => [
            'type' => 'float',
            'default' => '',
        ],
        'integerCol' => [
            'type' => 'integer',
            'default' => '',
        ],
        'ipAddressCol' => [
            'type' => 'string',
            'default' => '',
        ],
        'jsonCol' => [
            'type' => 'text',
            'default' => '',
        ],
        'jsonbCol' => [
            'type' => 'text',
            'default' => '',
        ],
        'longTextCol' => [
            'type' => 'text',
            'default' => '',
        ],
        'macAddressCol' => [
            'type' => 'string',
            'default' => '',
        ],
        'mediumIntegerCol' => [
            'type' => 'integer',
            'default' => '',
        ],
        'mediumTextCol' => [
            'type' => 'text',
            'default' => '',
        ],
        'smallIntegerCol' => [
            'type' => 'integer',
            'default' => '',
        ],
        'stringCol' => [
            'type' => 'string',
            'default' => '',
        ],
        'textCol' => [
            'type' => 'text',
            'default' => '',
        ],
        'timeCol' => [
            'type' => 'time',
            'default' => '',
        ],
        'timeTzCol' => [
            'type' => 'time',
            'default' => '',
        ],
        'tinyIntegerCol' => [
            'type' => 'integer',
            'default' => '',
        ],
        'timestampCol' => [
            'type' => 'datetime',
            'default' => '',
        ],
        'timestampTzCol' => [
            'type' => 'datetime',
            'default' => '',
        ],
        'uuidCol' => [
            'type' => 'string',
            'default' => '',
        ],
    ];

    private $expectedColumnTypes = [
        'bigIntegerCol' => [
            'name' => 'bigIntegerCol',
            'label' => 'BigIntegerCol',
            'value' => null,
            'default' => null,
            'type' => 'number',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'binaryCol' => [
            'name' => 'binaryCol',
            'label' => 'BinaryCol',
            'value' => null,
            'default' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'booleanCol' => [
            'name' => 'booleanCol',
            'label' => 'BooleanCol',
            'value' => null,
            'default' => null,
            'type' => 'boolean',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'charCol' => [
            'name' => 'charCol',
            'label' => 'CharCol',
            'value' => null,
            'default' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'dateCol' => [
            'name' => 'dateCol',
            'label' => 'DateCol',
            'value' => null,
            'default' => null,
            'type' => 'date',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'dateTimeCol' => [
            'name' => 'dateTimeCol',
            'label' => 'DateTimeCol',
            'value' => null,
            'default' => null,
            'type' => 'datetime',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'dateTimeTzCol' => [
            'name' => 'dateTimeTzCol',
            'label' => 'DateTimeTzCol',
            'value' => null,
            'default' => null,
            'type' => 'datetime',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'decimalCol' => [
            'name' => 'decimalCol',
            'label' => 'DecimalCol',
            'value' => null,
            'default' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'doubleCol' => [
            'name' => 'doubleCol',
            'label' => 'DoubleCol',
            'value' => null,
            'default' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'enumCol' => [
            'name' => 'enumCol',
            'label' => 'EnumCol',
            'value' => null,
            'default' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'floatCol' => [
            'name' => 'floatCol',
            'label' => 'FloatCol',
            'value' => null,
            'default' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'integerCol' => [
            'name' => 'integerCol',
            'label' => 'IntegerCol',
            'value' => null,
            'default' => null,
            'type' => 'number',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'ipAddressCol' => [
            'name' => 'ipAddressCol',
            'label' => 'IpAddressCol',
            'value' => null,
            'default' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'jsonCol' => [
            'name' => 'jsonCol',
            'label' => 'JsonCol',
            'value' => null,
            'default' => null,
            'type' => 'textarea',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'jsonbCol' => [
            'name' => 'jsonbCol',
            'label' => 'JsonbCol',
            'value' => null,
            'default' => null,
            'type' => 'textarea',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'longTextCol' => [
            'name' => 'longTextCol',
            'label' => 'LongTextCol',
            'value' => null,
            'default' => null,
            'type' => 'textarea',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'macAddressCol' => [
            'name' => 'macAddressCol',
            'label' => 'MacAddressCol',
            'value' => null,
            'default' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'mediumIntegerCol' => [
            'name' => 'mediumIntegerCol',
            'label' => 'MediumIntegerCol',
            'value' => null,
            'default' => null,
            'type' => 'number',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'mediumTextCol' => [
            'name' => 'mediumTextCol',
            'label' => 'MediumTextCol',
            'value' => null,
            'default' => null,
            'type' => 'textarea',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'smallIntegerCol' => [
            'name' => 'smallIntegerCol',
            'label' => 'SmallIntegerCol',
            'value' => null,
            'default' => null,
            'type' => 'number',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'stringCol' => [
            'name' => 'stringCol',
            'label' => 'StringCol',
            'value' => null,
            'default' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'textCol' => [
            'name' => 'textCol',
            'label' => 'TextCol',
            'value' => null,
            'default' => null,
            'type' => 'textarea',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'timeCol' => [
            'name' => 'timeCol',
            'label' => 'TimeCol',
            'value' => null,
            'default' => null,
            'type' => 'time',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'timeTzCol' => [
            'name' => 'timeTzCol',
            'label' => 'TimeTzCol',
            'value' => null,
            'default' => null,
            'type' => 'time',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'tinyIntegerCol' => [
            'name' => 'tinyIntegerCol',
            'label' => 'TinyIntegerCol',
            'value' => null,
            'default' => null,
            'type' => 'number',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'timestampCol' => [
            'name' => 'timestampCol',
            'label' => 'TimestampCol',
            'value' => null,
            'default' => null,
            'type' => 'datetime',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'timestampTzCol' => [
            'name' => 'timestampTzCol',
            'label' => 'TimestampTzCol',
            'value' => null,
            'default' => null,
            'type' => 'datetime',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
        'uuidCol' => [
            'name' => 'uuidCol',
            'label' => 'UuidCol',
            'value' => null,
            'default' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
        ],
    ];

    private $expectedFieldsFromDb = [
        'bigIntegerCol' => [
            'name' => 'bigIntegerCol',
            'label' => 'BigIntegerCol',
            'value' => null,
            'default' => null,
            'type' => 'number',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'binaryCol' => [
            'name' => 'binaryCol',
            'label' => 'BinaryCol',
            'value' => null,
            'default' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'booleanCol' => [
            'name' => 'booleanCol',
            'label' => 'BooleanCol',
            'value' => null,
            'default' => null,
            'type' => 'boolean',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'charCol' => [
            'name' => 'charCol',
            'label' => 'CharCol',
            'value' => null,
            'default' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'dateCol' => [
            'name' => 'dateCol',
            'label' => 'DateCol',
            'value' => null,
            'default' => null,
            'type' => 'date',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'dateTimeCol' => [
            'name' => 'dateTimeCol',
            'label' => 'DateTimeCol',
            'value' => null,
            'default' => null,
            'type' => 'datetime',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'dateTimeTzCol' => [
            'name' => 'dateTimeTzCol',
            'label' => 'DateTimeTzCol',
            'value' => null,
            'default' => null,
            'type' => 'datetime',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'decimalCol' => [
            'name' => 'decimalCol',
            'label' => 'DecimalCol',
            'value' => null,
            'default' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'doubleCol' => [
            'name' => 'doubleCol',
            'label' => 'DoubleCol',
            'value' => null,
            'default' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'enumCol' => [
            'name' => 'enumCol',
            'label' => 'EnumCol',
            'value' => null,
            'default' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'floatCol' => [
            'name' => 'floatCol',
            'label' => 'FloatCol',
            'value' => null,
            'default' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'integerCol' => [
            'name' => 'integerCol',
            'label' => 'IntegerCol',
            'value' => null,
            'default' => null,
            'type' => 'number',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'ipAddressCol' => [
            'name' => 'ipAddressCol',
            'label' => 'IpAddressCol',
            'value' => null,
            'default' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'jsonCol' => [
            'name' => 'jsonCol',
            'label' => 'JsonCol',
            'value' => null,
            'default' => null,
            'type' => 'textarea',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'jsonbCol' => [
            'name' => 'jsonbCol',
            'label' => 'JsonbCol',
            'value' => null,
            'default' => null,
            'type' => 'textarea',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'longTextCol' => [
            'name' => 'longTextCol',
            'label' => 'LongTextCol',
            'value' => null,
            'default' => null,
            'type' => 'textarea',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'macAddressCol' => [
            'name' => 'macAddressCol',
            'label' => 'MacAddressCol',
            'value' => null,
            'default' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'mediumIntegerCol' => [
            'name' => 'mediumIntegerCol',
            'label' => 'MediumIntegerCol',
            'value' => null,
            'default' => null,
            'type' => 'number',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'mediumTextCol' => [
            'name' => 'mediumTextCol',
            'label' => 'MediumTextCol',
            'value' => null,
            'default' => null,
            'type' => 'textarea',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'smallIntegerCol' => [
            'name' => 'smallIntegerCol',
            'label' => 'SmallIntegerCol',
            'value' => null,
            'default' => null,
            'type' => 'number',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'stringCol' => [
            'name' => 'stringCol',
            'label' => 'StringCol',
            'value' => null,
            'default' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'textCol' => [
            'name' => 'textCol',
            'label' => 'TextCol',
            'value' => null,
            'default' => null,
            'type' => 'textarea',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'timeCol' => [
            'name' => 'timeCol',
            'label' => 'TimeCol',
            'value' => null,
            'default' => null,
            'type' => 'time',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'timeTzCol' => [
            'name' => 'timeTzCol',
            'label' => 'TimeTzCol',
            'value' => null,
            'default' => null,
            'type' => 'time',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'tinyIntegerCol' => [
            'name' => 'tinyIntegerCol',
            'label' => 'TinyIntegerCol',
            'value' => null,
            'default' => null,
            'type' => 'number',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'timestampCol' => [
            'name' => 'timestampCol',
            'label' => 'TimestampCol',
            'value' => null,
            'default' => null,
            'type' => 'datetime',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'timestampTzCol' => [
            'name' => 'timestampTzCol',
            'label' => 'TimestampTzCol',
            'value' => null,
            'default' => null,
            'type' => 'datetime',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
        'uuidCol' => [
            'name' => 'uuidCol',
            'label' => 'UuidCol',
            'value' => null,
            'default' => null,
            'type' => 'text',
            'values' => [],
            'attributes' => [],
            'autoset' => true,
            'entity' => false,
        ],
    ];

    public function testGetFieldTypeFromDbColumnType()
    {
        $this->crudPanel->setModel(ColumnType::class);
        $this->crudPanel->setOperation('create');
        $this->crudPanel->setFromDb();

        $fieldTypesFromColumnType = [];

        foreach ($this->crudPanel->fields() as $field) {
            $fieldTypesFromColumnType[] = $this->invokeMethod($this->crudPanel, 'inferFieldTypeFromDbColumnType', [$field['name']]);
        }

        $this->assertEquals(array_values($this->expectedFieldTypeFromColumnType), $fieldTypesFromColumnType);
    }

    public function testSetFromDb()
    {
        $this->crudPanel->setModel(ColumnType::class);
        $this->crudPanel->setOperation('create');
        $this->crudPanel->setFromDb();

        $this->assertEquals($this->expectedFieldsFromDb, $this->crudPanel->fields());
    }

    public function testGetDbColumnTypes()
    {
        $this->markTestIncomplete('Its not that it does not work, the return types are different. eg. string vs varchar.');
        $this->crudPanel->setModel(ColumnType::class);

        $columnTypes = $this->crudPanel->getDbColumnTypes();

        $this->assertEquals($this->expectedColumnTypesFromDb, $columnTypes);
    }

    public function testGetFieldTypeFromDbColumnTypeUnknownField()
    {
        $this->crudPanel->setModel(ColumnType::class);

        $fieldType = $this->invokeMethod($this->crudPanel, 'inferFieldTypeFromDbColumnType', ['someUnknowField1']);

        $this->assertEquals($this->expectedUnknownFieldType, $fieldType);
    }

    public function testMakeLabel()
    {
        $this->markTestIncomplete('Not correctly implemented');

        $idLabel = $this->crudPanel->makeLabel('id');
        $snakeCaseFKLabel = $this->crudPanel->makeLabel('id_user');
        $camelCaseFKLabel = $this->crudPanel->makeLabel('idUser');
        $camelCaseFKLabelReversed = $this->crudPanel->makeLabel('userId');
        $dateLabel = $this->crudPanel->makeLabel('created_at');
        $camelCaseLabel = $this->crudPanel->makeLabel('camelCaseLabel');
        $camelCaseRandomLabel = $this->crudPanel->makeLabel('camelCaseLabelRANDOMCase');
        $simpleLabel = $this->crudPanel->makeLabel('label');
        $snakeCaseLabel = $this->crudPanel->makeLabel('snake_case_label');
        $snakeCaseRandomLabel = $this->crudPanel->makeLabel('snake_Case_random_CASE');
        $allCapsLabel = $this->crudPanel->makeLabel('ALLCAPSLABEL');

        // TODO: the id label gets removed. it should not be removed if it is not followed by anything.
        // TODO: improve method documentation to know what to expect.
        $this->assertEquals('Id', $idLabel);
        $this->assertEquals('Id user', $snakeCaseFKLabel);
        $this->assertEquals('IdUser', $camelCaseFKLabel);
        $this->assertEquals('User', $camelCaseFKLabelReversed);
        $this->assertEquals('Created', $dateLabel);
        $this->assertEquals('CamelCaseLabel', $camelCaseLabel);
        $this->assertEquals('CamelCaseLabelRANDOMCase', $camelCaseRandomLabel);
        $this->assertEquals('Label', $simpleLabel);
        $this->assertEquals('Snake case label', $snakeCaseLabel);
        $this->assertEquals('Snake Case random CASE', $snakeCaseRandomLabel);
        $this->assertEquals('ALLCAPSLABEL', $allCapsLabel);
    }

    public function testMakeLabelEmpty()
    {
        $label = $this->crudPanel->makeLabel('');

        $this->assertEmpty($label);
    }

    public function testGetDbColumnsNames()
    {
        $this->crudPanel->setModel(ColumnType::class);

        $columnNames = $this->crudPanel->getDbColumnsNames();

        $this->assertEquals(array_keys($this->expectedColumnTypes), $columnNames);
    }

    public function testSetDoctrineTypesMapping()
    {
        $this->crudPanel->setModel(ColumnType::class);
        if (! method_exists($this->crudPanel->getModel()->getConnection(), 'getDoctrineConnection')) {
            $this->markTestSkipped('This test is only for Laravel 10, Laravel 11 does not have dbal as a dependency anymore');
        }

        $original_db_config = $this->app['config']->get('database.connections.testing');
        $new_model_db_config = array_merge($original_db_config, ['prefix' => 'testing2']);

        $this->app['config']->set('database.connections.testing_2', $new_model_db_config);

        $original_db_platform = $this->crudPanel->getModel()->getConnection()->getDoctrineConnection()->getDatabasePlatform();
        $this->crudPanel->setDoctrineTypesMapping();
        $type = $original_db_platform->getDoctrineTypeMapping('enum');

        $this->crudPanel->setModel(MyColumnTypeWithOtherConnection::class);
        $new_model_db_platform = $this->crudPanel->getModel()->getConnection()->getDoctrineConnection()->getDatabasePlatform();

        try {
            $new_model_db_platform->getDoctrineTypeMapping('enum');
        } catch (Exception $e) {
            $this->assertInstanceOf(Exception::class, $e);
        }
        $this->crudPanel->setDoctrineTypesMapping();

        $type = $new_model_db_platform->getDoctrineTypeMapping('enum');
        $this->assertEquals('string', $type);
    }
}
