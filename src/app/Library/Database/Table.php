<?php

namespace Backpack\CRUD\app\Library\Database;

final class Table
{
    private string $name;
    private array $columns = [];
    private array $indexes = [];

    public function __construct(string $name, array $columns = [], $schemaManager = null)
    {
        $this->name = $name;
        foreach ($columns as $column) {
            $this->columns[$column['name']] = new class($column, $schemaManager)
            {
                public function __construct(private array $column, private $schemaManager)
                {
                }

                public function getName()
                {
                    return $this->column['name'];
                }

                public function getNotnull()
                {
                    return ! $this->column['nullable'];
                }

                public function getDefault()
                {
                    return isset($this->schemaManager) && class_exists(\Illuminate\Database\MariaDbConnection::class) ?
                        (is_a($this->schemaManager->getConnection(), \Illuminate\Database\MariaDbConnection::class) &&
                            is_string($this->column['default']) &&
                            $this->column['nullable'] === true &&
                            (strtolower($this->column['default']) === 'null') ? null : $this->column['default']) : $this->column['default'];
                }

                public function getUnsigned()
                {
                    return in_array('unsigned', explode(' ', $this->column['type']));
                }

                public function getType()
                {
                    return new class($this->column)
                    {
                        public function __construct(private array $column)
                        {
                        }

                        public function getName()
                        {
                            return $this->column['type_name'];
                        }
                    };
                }
            };
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function hasColumn(string $columnName)
    {
        return isset($this->columns[$columnName]);
    }

    public function getColumn(string $columnName)
    {
        return $this->columns[$columnName];
    }

    public function getIndexes()
    {
        return $this->indexes;
    }

    public function setIndexes(array $indexes)
    {
        $this->indexes = $indexes;
    }
}
