<?php

namespace Backpack\CRUD\app\Library\Database;

class TableSchema
{
    /** @var \Backpack\CRUD\app\Library\Database\Table */
    public $schema;

    public function __construct(string $connection, string $table)
    {
        $this->schema = app('DatabaseSchema')->getForTable($table, $connection);
    }

    /**
     * Return an array of column names in database.
     *
     * @return array
     */
    public function getColumnsNames()
    {
        return array_values(
            array_map(function ($item) {
                return $item->getName();
            }, $this->getColumns())
        );
    }

    /**
     * Return the column type in database.
     *
     * @param  string  $columnName
     * @return string
     */
    public function getColumnType(string $columnName)
    {
        if (! $this->schemaExists() || ! $this->schema->hasColumn($columnName)) {
            return 'varchar';
        }

        $column = $this->schema->getColumn($columnName);

        return $column->getType()->getName();
    }

    /**
     * Check if the column exists in the database.
     *
     * @param  string  $columnName
     * @return bool
     */
    public function hasColumn($columnName)
    {
        if (! $this->schemaExists()) {
            return false;
        }

        return $this->schema->hasColumn($columnName);
    }

    /**
     * Check if the column is nullable in database.
     *
     * @param  string  $columnName
     * @return bool
     */
    public function columnIsNullable($columnName)
    {
        if (! $this->hasColumn($columnName)) {
            return true;
        }

        $column = $this->schema->getColumn($columnName);

        return $column->getNotnull() ? false : true;
    }

    /**
     * Check if the column has default value set on database.
     *
     * @param  string  $columnName
     * @return bool
     */
    public function columnHasDefault($columnName)
    {
        if (! $this->hasColumn($columnName)) {
            return false;
        }

        $column = $this->schema->getColumn($columnName);

        return $column->getDefault() !== null ? true : false;
    }

    /**
     * Get the default value for a column on database.
     *
     * @param  string  $columnName
     * @return bool
     */
    public function getColumnDefault($columnName)
    {
        if (! $this->hasColumn($columnName)) {
            return false;
        }

        $column = $this->schema->getColumn($columnName);

        return $column->getDefault();
    }

    /**
     * Get the table schema columns.
     *
     * @return array
     */
    public function getColumns()
    {
        if (! $this->schemaExists()) {
            return [];
        }

        return $this->schema->getColumns();
    }

    /**
     * Make sure the schema for the connection is initialized.
     *
     * @return bool
     */
    private function schemaExists()
    {
        if (! empty($this->schema)) {
            return true;
        }

        return false;
    }
}
