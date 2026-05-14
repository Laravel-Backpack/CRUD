<?php

namespace Backpack\CRUD\Tests\Unit\Database;

use Backpack\CRUD\app\Library\Database\Table;
use PHPUnit\Framework\TestCase;

/**
 * @covers Backpack\CRUD\app\Library\Database\Table
 */
class DatabaseTableTest extends TestCase
{
    private function makeMariaDbSchemaManager(): ?object
    {
        if (! class_exists(\Illuminate\Database\MariaDbConnection::class)) {
            return null;
        }

        $mariaDbConn = $this->createStub(\Illuminate\Database\MariaDbConnection::class);

        return new class($mariaDbConn)
        {
            public function __construct(private object $conn)
            {
            }

            public function getConnection(): object
            {
                return $this->conn;
            }
        };
    }

    private function makeColumn(array $attributes, ?object $schemaManager = null): object
    {
        $defaults = ['type' => 'bigint', 'type_name' => 'bigint'];
        $table = new Table('test', [array_merge($defaults, $attributes)], $schemaManager);

        return $table->getColumn($attributes['name']);
    }

    /** @test */
    public function testGetDefaultNormalizesUpperCaseNullStringForNullableColumn()
    {
        $schemaManager = $this->makeMariaDbSchemaManager();
        if ($schemaManager === null) {
            $this->markTestSkipped('MariaDbConnection is not available in this Laravel version.');
        }

        // MariaDB returns 'NULL' (uppercase string) for nullable columns with DEFAULT NULL
        $column = $this->makeColumn(['name' => 'user_id', 'nullable' => true, 'default' => 'NULL'], $schemaManager);

        $this->assertNull($column->getDefault());
    }

    /** @test */
    public function testGetDefaultNormalizesLowerCaseNullStringForNullableColumn()
    {
        $schemaManager = $this->makeMariaDbSchemaManager();
        if ($schemaManager === null) {
            $this->markTestSkipped('MariaDbConnection is not available in this Laravel version.');
        }

        $column = $this->makeColumn(['name' => 'user_id', 'nullable' => true, 'default' => 'null'], $schemaManager);

        $this->assertNull($column->getDefault());
    }

    /** @test */
    public function testGetDefaultNormalizesMixedCaseNullStringForNullableColumn()
    {
        $schemaManager = $this->makeMariaDbSchemaManager();
        if ($schemaManager === null) {
            $this->markTestSkipped('MariaDbConnection is not available in this Laravel version.');
        }

        $column = $this->makeColumn(['name' => 'user_id', 'nullable' => true, 'default' => 'Null'], $schemaManager);

        $this->assertNull($column->getDefault());
    }

    /** @test */
    public function testGetDefaultReturnsPhpNullWhenDefaultIsAlreadyNull()
    {
        $column = $this->makeColumn(['name' => 'user_id', 'nullable' => true, 'default' => null]);

        $this->assertNull($column->getDefault());
    }

    /** @test */
    public function testGetDefaultReturnsActualStringDefaultForNonNullValue()
    {
        // A column with a numeric default should be returned unchanged
        $column = $this->makeColumn(['name' => 'user_id', 'nullable' => false, 'default' => '0']);

        $this->assertSame('0', $column->getDefault());
    }

    /** @test */
    public function testGetDefaultDoesNotNormalizeNullStringForNonNullableColumn()
    {
        $column = $this->makeColumn(['name' => 'user_id', 'nullable' => false, 'default' => 'NULL']);

        $this->assertSame('NULL', $column->getDefault());
    }

    /** @test */
    public function testGetDefaultDoesNotNormalizeArbitraryStringDefaultForNullableColumn()
    {
        // A nullable VARCHAR column with a real string default must not be changed
        $column = $this->makeColumn([
            'name' => 'status',
            'type' => 'varchar',
            'type_name' => 'varchar',
            'nullable' => true,
            'default' => 'active',
        ]);

        $this->assertSame('active', $column->getDefault());
    }

    /** @test */
    public function testGetDefaultWithoutSchemaManagerReturnsRawValue()
    {
        $column = $this->makeColumn(['name' => 'user_id', 'nullable' => true, 'default' => 'NULL']);

        $this->assertSame('NULL', $column->getDefault());
    }
}
