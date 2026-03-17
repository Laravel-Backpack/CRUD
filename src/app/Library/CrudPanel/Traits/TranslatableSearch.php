<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

trait TranslatableSearch
{
    protected function applyTranslatableJsonSearch($query, $column, $searchTerm, $searchOperator)
    {
        $columnName = $column['name'];
        $tableName = $this->model->getTable();
        $locale = app()->getLocale();
        $prefixedColumn = "$tableName.$columnName";

        if ($this->isDatabaseMySQL()) {
            $query->orWhereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT($prefixedColumn, ?))) $searchOperator ?",
                ["\$$locale", '%'.strtolower($searchTerm).'%']
            );
        } elseif ($this->isDatabasePostgreSQL()) {
            $query->orWhereRaw(
                "LOWER($prefixedColumn->?) ILIKE ?",
                [$locale, '%'.strtolower($searchTerm).'%']
            );
        } elseif ($this->isDatabaseSQLite()) {
            $query->orWhereRaw(
                "LOWER(JSON_EXTRACT($prefixedColumn, ?)) LIKE ?",
                ["\$$locale", '%'.strtolower($searchTerm).'%']
            );
        }
    }

    protected function isTranslatableField($columnName)
    {
        return method_exists($this->model, 'isTranslatableAttribute') &&
               $this->model->isTranslatableAttribute($columnName);
    }

    protected function isJsonColumn($columnName)
    {
        return $this->isJsonColumnType($columnName);
    }

    protected function isDatabaseMySQL()
    {
        return config('database.default') === 'mysql';
    }

    protected function isDatabasePostgreSQL()
    {
        return config('database.default') === 'pgsql';
    }

    protected function isDatabaseSQLite()
    {
        return config('database.default') === 'sqlite';
    }
}
