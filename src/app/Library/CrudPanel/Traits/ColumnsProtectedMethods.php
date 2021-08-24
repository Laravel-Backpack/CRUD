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
    }

    /**
     * If a column priority has not been defined, provide a default one.
     *
     * @param array $column Column definition array.
     * @return array         Proper array defining the column.
     */
    protected function makeSureColumnHasPriority($column)
    {
        $columns_count = $this->countColumnsWithoutActions();
        $assumed_priority = $columns_count ? $columns_count : 0;

        $column['priority'] = $column['priority'] ?? $assumed_priority;

        return $column;
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
        $could_be_relation = isset($column['entity']) && $column['entity'] !== false;

        if (! isset($column['type']) && $could_be_relation) {
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

    protected function makeSureColumnHasEntity($column)
    {
        if (isset($column['entity'])) {
            return $column;
        }

        // if the name is an array it's definitely not a relationship
        if (is_array($column['name'])) {
            return $column;
        }

        // if the name is dot notation it might be a relationship
        if (strpos($column['name'], '.') !== false) {
            $possibleMethodName = Str::before($column['name'], '.');
            $model = $this->model;

            // if the first part of the string exists as method,
            // it is a relationship
            if (method_exists($model, $possibleMethodName)) {
                $parts = explode('.', $column['name']);
                $relation = $column['name'];
                $attribute_in_relation = false;

                // here we are going to iterate through all relation parts to check
                // if the attribute is present in the relation string.
                foreach ($parts as $i => $part) {
                    try {
                        $model = $model->$part()->getRelated();
                    } catch (\Exception $e) {
                        $relation = join('.', array_slice($parts, 0, $i));
                        $attribute_in_relation = true;
                    }
                }

                if (! isset($column['type'])) {
                    $column['name'] = $column['entity'] = $relation;
                }

                // if the user setup the attribute in relation string, we are not going to infer that attribute from model
                // instead we get the defined attribute by the user.
                if ($attribute_in_relation) {
                    $column['attribute'] = $column['attribute'] ?? end($parts);
                }

                return $column;
            }
        }

        // if there's a method on the model with this name
        if (method_exists($this->model, $column['name'])) {
            $column['entity'] = $column['name'];

            return $column;
        }

        // if the name ends with _id and that method exists,
        // we can probably use it as an entity
        if (Str::endsWith($column['name'], '_id')) {
            $possibleMethodName = Str::replaceLast('_id', '', $column['name']);

            if (method_exists($this->model, $possibleMethodName)) {
                $column['entity'] = $possibleMethodName;

                return $column;
            }
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
        if (isset($column['entity']) && $column['entity'] !== false && ! isset($column['model'])) {
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

        if (! $this->driverIsSql()) {
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
