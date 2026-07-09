<?php

namespace Backpack\CRUD\Tests\Unit\Models;

use Backpack\CRUD\Tests\BaseTestClass;
use Backpack\CRUD\Tests\Config\Models\EnumTestModel;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;

class HasEnumFieldsMysqlTest extends BaseTestClass
{
    private const TABLE = 'crud_enum_test';

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.connections.testing_mysql', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'backpack_test',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureDatabaseExists();

        if (! $this->mysqlIsAvailable()) {
            $this->markTestSkipped('MySQL connection is not available.');
        }
    }

    private function ensureDatabaseExists(): void
    {
        $config = config('database.connections.testing_mysql');
        $database = $config['database'];

        try {
            $dsn = "mysql:host={$config['host']};port={$config['port']}";
            $pdo = new \PDO($dsn, $config['username'], $config['password']);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (\Throwable) {
            // If we can't create the database, mysqlIsAvailable() will skip the test.
        }
    }

    protected function tearDown(): void
    {
        DB::connection('testing_mysql')->statement(
            'DROP TABLE IF EXISTS `'.self::TABLE.'`'
        );

        parent::tearDown();
    }

    private function mysqlIsAvailable(): bool
    {
        try {
            DB::connection('testing_mysql')->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function createTableWithColumn(string $columnDefinition): void
    {
        DB::connection('testing_mysql')->statement(
            'DROP TABLE IF EXISTS `'.self::TABLE.'`'
        );

        DB::connection('testing_mysql')->statement(
            'CREATE TABLE `'.self::TABLE.'` (
                `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                '.$columnDefinition.'
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    #[Test]
    public function test_parses_multiple_enum_values(): void
    {
        $this->createTableWithColumn(
            "`status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft'"
        );

        $values = EnumTestModel::getPossibleEnumValues('status');

        $this->assertSame(['draft', 'published', 'archived'], $values);
    }

    #[Test]
    public function test_parses_single_enum_value(): void
    {
        $this->createTableWithColumn(
            "`status` ENUM('active') NOT NULL DEFAULT 'active'"
        );

        $values = EnumTestModel::getPossibleEnumValues('status');

        $this->assertSame(['active'], $values);
    }

    #[Test]
    public function test_parses_numeric_enum_values(): void
    {
        $this->createTableWithColumn(
            "`status` ENUM('0','1','2','3') NOT NULL DEFAULT '0'"
        );

        $values = EnumTestModel::getPossibleEnumValues('status');

        $this->assertSame(['0', '1', '2', '3'], $values);
    }

    #[Test]
    public function test_preserves_escaped_quotes_in_enum_values(): void
    {
        $this->createTableWithColumn(
            "`status` ENUM('it''s','with''quote') NOT NULL DEFAULT 'it''s'"
        );

        $values = EnumTestModel::getPossibleEnumValues('status');

        $this->assertSame(["it''s", "with''quote"], $values);
    }

    #[Test]
    public function test_enum_values_preserve_declaration_order(): void
    {
        $this->createTableWithColumn(
            "`status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft'"
        );

        $values = EnumTestModel::getPossibleEnumValues('status');

        $this->assertSame('draft', $values[0]);
        $this->assertSame('published', $values[1]);
        $this->assertSame('archived', $values[2]);
    }

    #[Test]
    public function test_getEnumValuesAsAssociativeArray_returns_identity_map(): void
    {
        $this->createTableWithColumn(
            "`status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft'"
        );

        $values = EnumTestModel::getEnumValuesAsAssociativeArray('status');

        $this->assertSame([
            'draft' => 'draft',
            'published' => 'published',
            'archived' => 'archived',
        ], $values);
    }

    #[Test]
    public function test_getEnumValuesAsAssociativeArray_with_single_value(): void
    {
        $this->createTableWithColumn(
            "`status` ENUM('active') NOT NULL DEFAULT 'active'"
        );

        $values = EnumTestModel::getEnumValuesAsAssociativeArray('status');

        $this->assertSame(['active' => 'active'], $values);
    }

    #[Test]
    public function test_aborts_when_column_is_not_enum(): void
    {
        $this->createTableWithColumn(
            "`title` VARCHAR(255) NOT NULL DEFAULT ''"
        );

        $this->expectException(\Throwable::class);

        EnumTestModel::getPossibleEnumValues('title');
    }

    #[Test]
    public function test_aborts_when_column_is_integer(): void
    {
        $this->createTableWithColumn(
            '`count` INT NOT NULL DEFAULT 0'
        );

        $this->expectException(\Throwable::class);

        EnumTestModel::getPossibleEnumValues('count');
    }

    #[Test]
    public function test_works_under_ansi_quotes_sql_mode(): void
    {
        $this->createTableWithColumn(
            "`status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft'"
        );

        DB::connection('testing_mysql')->statement(
            "SET SESSION sql_mode = CONCAT(@@sql_mode, ',ANSI_QUOTES')"
        );

        try {
            $values = EnumTestModel::getPossibleEnumValues('status');

            $this->assertSame(['draft', 'published', 'archived'], $values);
        } catch (\Throwable $e) {
            $this->fail(
                'ANSI_QUOTES mode is not yet supported by HasEnumFields. '.
                'This test is expected to pass after the trait is refactored '.
                'to use getSchemaBuilder()->getColumnType(). '.
                'Original error: '.$e->getMessage()
            );
        }
    }
}
