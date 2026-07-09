<?php

namespace Backpack\CRUD\Tests\Unit\Models;

use Backpack\CRUD\Tests\BaseTestClass;
use Backpack\CRUD\Tests\Config\Models\EnumTestModel;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HasEnumFieldsMysqlTest extends BaseTestClass
{
    private const TABLE = 'crud_enum_test';

    protected function getEnvironmentSetUp($app): void
    {
        $hostEnv = $this->loadHostEnv();

        $app['config']->set('database.connections.testing_mysql', [
            'driver' => 'mysql',
            'host' => $hostEnv['DB_HOST'] ?? '127.0.0.1',
            'port' => $hostEnv['DB_PORT'] ?? '3306',
            'database' => $hostEnv['DB_DATABASE'] ?? 'backpack_test',
            'username' => $hostEnv['DB_USERNAME'] ?? 'root',
            'password' => $hostEnv['DB_PASSWORD'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ]);
    }

    private function loadHostEnv(): array
    {
        $envFile = dirname(__DIR__, 6).DIRECTORY_SEPARATOR.'.env';

        if (! file_exists($envFile)) {
            return [];
        }

        $env = [];
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }
            if (! str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $env[trim($key)] = trim($value);
        }

        return $env;
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->mysqlIsAvailable()) {
            $this->markTestSkipped('MySQL connection is not available.');
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

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Could not deduce enum values');

        EnumTestModel::getPossibleEnumValues('title');
    }

    #[Test]
    public function test_aborts_when_column_is_integer(): void
    {
        $this->createTableWithColumn(
            '`count` INT NOT NULL DEFAULT 0'
        );

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Could not deduce enum values');

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

        $values = EnumTestModel::getPossibleEnumValues('status');

        $this->assertSame(['draft', 'published', 'archived'], $values);
    }
}
