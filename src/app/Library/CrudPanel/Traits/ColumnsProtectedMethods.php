<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait ColumnsProtectedMethods
{
    /**
     * Add a column to the current operation, using the Setting API.
     *
     * @param array $column Column definition array.
     */
    protected function addColumnToOperationSettings($column)
    {
        $allColumns = $this->columns();
        $allColumns = Arr::add($allColumns, $column['key'], $column);

        $this->setOperationSetting('columns', $allColumns);

        // make sure the column has a priority in terms of visibility
        // if no priority has been defined, use the order in the array plus one
        if (! array_key_exists('priority', $column)) {
            $position_in_columns_array = (int) array_search($column['key'], array_keys($this->columns()));
            $allColumns[$column['key']]['priority'] = $position_in_columns_array + 1;
        }

        $this->setOperationSetting('columns', $allColumns);
    }

    /**
     * If the field definition array is actually a string, it means the programmer was lazy
     * and has only passed the name of the column. Turn that into a proper array.
     *
     * @param array $column Column definition array.
     * @return array         Proper array defining the column.
     */
    protected function makeSureColumnHasName($column)
    {
        if (is_string($column)) {
            $column = ['name' => $column];
        }

        if (is_array($column) && ! isset($column['name'])) {
            $column['name'] = 'anonymous_column_'.Str::random(5);
        }

        return $column;
    }

    /**
     * If a column array is missing the "label" attribute, an ugly error would be show.
     * So we add the field Name as a label - it's better than nothing.
     *
     * @param array     $column  Column definition array.
     * @return array            Proper array defining the column.
     */
    protected function makeSureColumnHasLabel($column)
    {
        if (! isset($column['label'])) {
            $column['label'] = mb_ucfirst($this->makeLabel($column['name']));
        }

        return $column;
    }

    /**
     * If a column definition is missing the type, set a default.
     *
     * @param array $column Column definition array.
     * @return array        Column definition array with type.
     */
    protected function makeSureColumnHasType($column)
    {
        // if it's got a method on the model with the same name
        // then it should be a relationship
        if (! isset($column['type']) && method_exists($this->model, $column['name'])) {
            $column['type'] = 'relationship';
        }

        if (! isset($column['type'])) {
            $column['type'] = 'text';
        }

        return $column;
    }

    /**
     * If a column definition is missing the key, set the default.
     * The key is used when storing all columns using the Settings API,
     * it is used as the "key" of the associative array that holds all columns.
     *
     * @param array $column Column definition array.
     * @return array        Column definition array with key.
     */
    protected function makeSureColumnHasKey($column)
    {
        if (! isset($column['key'])) {
            $column['key'] = str_replace('.', '__', $column['name']);
        }

        return $column;
    }

    /**
     * If a column definition is missing the wrapper element, set the default (empty).
     * The wrapper is the HTML element that wrappes around the column text.
     * By defining this array a developer can wrap the text into an anchor (link),
     * span, div or whatever they want.
     *
     * @param array $column Column definition array.
     * @return array        Column definition array with wrapper.
     */
    protected function makeSureColumnHasWrapper($column)
    {
        if (! isset($column['wrapper'])) {
            $column['wrapper'] = [];
        }

        return $column;
    }

    /**
     * If an entity has been defined for the column, but no model,
     * determine the model from that relationship.
     *
     * @param array $column Column definition array.
     * @return array        Column definition array with model.
     */
    protected function makeSureColumnHasModel($column)
    {
        // if this is a relation type field and no corresponding model was specified,
        // get it from the relation method defined in the main model
        if (isset($column['entity']) && ! isset($column['model'])) {
            $column['model'] = $this->getRelationModel($column['entity']);
        }

        return $column;
    }

    /**
     * Move the most recently added column before or after the given target column. Default is before.
     *
     * @param string|array $targetColumn The target column name or array.
     * @param bool         $before       If true, the column will be moved before the target column, otherwise it will be moved after it.
     */
    protected function moveColumn($targetColumn, $before = true)
    {
        // TODO: this and the moveField method from the Fields trait should be refactored into a single method and moved
        //       into a common class
        $targetColumnName = is_array($targetColumn) ? $targetColumn['name'] : $targetColumn;
        $columnsArray = $this->columns();

        if (array_key_exists($targetColumnName, $columnsArray)) {
            $targetColumnPosition = $before ? array_search($targetColumnName, array_keys($columnsArray)) :
                array_search($targetColumnName, array_keys($columnsArray)) + 1;

            $element = array_pop($columnsArray);
            $beginningPart = array_slice($columnsArray, 0, $targetColumnPosition, true);
            $endingArrayPart = array_slice($columnsArray, $targetColumnPosition, null, true);

            $columnsArray = array_merge($beginningPart, [$element['name'] => $element], $endingArrayPart);
            $this->setOperationSetting('columns', $columnsArray);
        }
    }

    /**
     * Check if the column exists in the database, as a DB column.
     *
     * @param string $table
     * @param string $name
     *
     * @return bool
     */
    protected function hasDatabaseColumn($table, $name)
    {
        static $cache = [];

        if ($this->driverIsMongoDb()) {
            return true;
        }

        if (isset($cache[$table])) {
            $columns = $cache[$table];
        } else {
            $columns = $cache[$table] = $this->getSchema()->getColumnListing($table);
        }

        return in_array($name, $columns);
    }
}
