<?php

namespace Backpack\CRUD\app\Library\Database;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

final class DatabaseSchema
{
    private static $schema;

    /**
     * Return the schema for the table.
     */
    public static function getForTable(string $table, string $connection)
    {
        $connection = $connection ?: config('database.default');

        self::generateDatabaseSchema($connection, $table);

        return self::$schema[$connection][$table] ?? null;
    }

    public static function getTables(string $connection = null): array
    {
        $connection = $connection ?: config('database.default');

        self::$schema[$connection] = LazyCollection::make(self::getCreateSchema($connection)->getTables())->mapWithKeys(function ($table, $key) use ($connection) {
            $tableName = is_array($table) ? $table['name'] : $table->getName();

            if ($existingTable = self::$schema[$connection][$tableName] ?? false) {
                return [$tableName => $existingTable];
            }

            $table = self::mapTable($connection, $tableName);

            return [$tableName => $table];
        })->toArray();

        return self::$schema[$connection];
    }

    public function listTableColumnsNames(string $connection, string $table)
    {
        $table = self::getForTable($table, $connection);

        return $table ? array_keys($table->getColumns()) : [];
    }

    public function listTableIndexes(string $connection, string $table)
    {
        return self::getIndexColumnNames($connection, $table);
    }

    public function getManager(string $connection = null)
    {
        $connection = $connection ?: config('database.default');

        return self::getSchemaManager($connection);
    }

    /**
     * Generates and store the database schema.
     */
    private static function generateDatabaseSchema(string $connection, string $table)
    {
        if (! isset(self::$schema[$connection][$table])) {
            self::$schema[$connection][$table] = self::mapTable($connection, $table);
        }
    }

    private static function mapTable(string $connection, string $tableName)
    {
        try {
            $table = method_exists(self::getCreateSchema($connection), 'getTable') ?
                        self::getCreateSchema($connection)->getTable($tableName) :
                        self::getCreateSchema($connection)->getColumns($tableName);
        } catch (\Exception $e) {
            return new Table($tableName, []);
        }

        if (! is_array($table)) {
            return $table;
        }

        if (empty($table)) {
            return new Table($tableName, []);
        }

        $schemaManager = self::getSchemaManager($connection);
        $indexes = $schemaManager->getIndexes($tableName);

        $indexes = array_map(function ($index) {
            return $index['columns'];
        }, $indexes);

        $table = new Table($tableName, $table);

        $indexes = Arr::flatten($indexes);
        $table->setIndexes(array_unique($indexes));

        return $table;
    }

    private static function getIndexColumnNames(string $connection, string $table)
    {
        self::generateDatabaseSchema($connection, $table);

        $indexes = self::$schema[$connection][$table]->getIndexes();

        $indexes = \Illuminate\Support\Arr::flatten(array_map(function ($index) {
            return is_string($index) ? $index : $index->getColumns();
        }, $indexes));

        return array_unique($indexes);
    }

    private static function getCreateSchema(string $connection)
    {
        $schemaManager = self::getSchemaManager($connection);

        return method_exists($schemaManager, 'createSchema') ? $schemaManager->createSchema() : $schemaManager;
    }

    private static function dbalTypes()
    {
        return [
            'enum' => \Doctrine\DBAL\Types\Types::STRING,
            'jsonb' => \Doctrine\DBAL\Types\Types::JSON,
            'geometry' => \Doctrine\DBAL\Types\Types::STRING,
            'point' => \Doctrine\DBAL\Types\Types::STRING,
            'lineString' => \Doctrine\DBAL\Types\Types::STRING,
            'polygon' => \Doctrine\DBAL\Types\Types::STRING,
            'multiPoint' => \Doctrine\DBAL\Types\Types::STRING,
            'multiLineString' => \Doctrine\DBAL\Types\Types::STRING,
            'multiPolygon' => \Doctrine\DBAL\Types\Types::STRING,
            'geometryCollection' => \Doctrine\DBAL\Types\Types::STRING,
        ];
    }

    private static function getSchemaManager(string $connection)
    {
        $connection = DB::connection($connection);

        if (method_exists($connection, 'getDoctrineSchemaManager')) {
            foreach (self::dbalTypes() as $key => $value) {
                $connection->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping($key, $value);
            }

            return $connection->getDoctrineSchemaManager();
        }

        return $connection->getSchemaBuilder();
    }
}
