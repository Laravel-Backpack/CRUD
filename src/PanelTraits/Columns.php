<?php

namespace Backpack\CRUD\PanelTraits;

use Backpack\CRUD\Columns\Column;

trait Columns
{
    // ------------
    // COLUMNS
    // ------------

    /**
     * Get the CRUD columns.
     *
     * @return array CRUD columns.
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Add a bunch of column names and their details to the CRUD object.
     *
     * @param [array or multi-dimensional array]
     */
    public function setColumns($columns)
    {
        // clear any columns already set
        $this->columns = [];

        // if array, add a column for each of the items
        if (is_array($columns) && count($columns)) {
            foreach ($columns as $key => $column) {
                // if label and other details have been defined in the array
                if (is_array($column) || $column instanceof Column) {
                    $this->addColumn($column);
                } else {
                    $this->addColumn([
                                    'name'  => $column,
                                    'label' => ucfirst($column),
                                    'type'  => 'text',
                                ]);
                }
            }
        }

        if (is_string($columns)) {
            $this->addColumn([
                                'name'  => $columns,
                                'label' => ucfirst($columns),
                                'type'  => 'text',
                                ]);
        }

        // This was the old setColumns() function, and it did not work:
        // $this->columns = array_filter(array_map([$this, 'addDefaultTypeToColumn'], $columns));
    }

    /**
     * Add a column at the end of to the CRUD object's "columns" array.
     *
     * @param [string or array]
     */
    public function addColumn($column)
    {
        if ($column instanceof Column) {
            $column = $column->toArray();
        }
        // if a string was passed, not an array, change it to an array
        if (! is_array($column)) {
            $column = ['name' => $column];
        }

        // make sure the column has a label
        $column_with_details = $this->addDefaultLabel($column);

        // make sure the column has a name
        if (! array_key_exists('name', $column_with_details)) {
            $column_with_details['name'] = 'anonymous_column_'.str_random(5);
        }

        // check if the column exists in the database table
        $columnExistsInDb = $this->hasColumn($this->model->getTable(), $column_with_details['name']);

        // make sure the column has a type
        if (! array_key_exists('type', $column_with_details)) {
            $column_with_details['type'] = 'text';
        }

        // make sure the column has a key
        if (! array_key_exists('key', $column_with_details)) {
            $column_with_details['key'] = $column_with_details['name'];
        }

        // make sure the column has a tableColumn boolean
        if (! array_key_exists('tableColumn', $column_with_details)) {
            $column_with_details['tableColumn'] = $columnExistsInDb ? true : false;
        }

        // make sure the column has a orderable boolean
        if (! array_key_exists('orderable', $column_with_details)) {
            $column_with_details['orderable'] = $columnExistsInDb ? true : false;
        }

        // make sure the column has a searchLogic
        if (! array_key_exists('searchLogic', $column_with_details)) {
            $column_with_details['searchLogic'] = $columnExistsInDb ? true : false;
        }

        array_filter($this->columns[$column_with_details['key']] = $column_with_details);

        // if this is a relation type field and no corresponding model was specified, get it from the relation method
        // defined in the main model
        if (isset($column_with_details['entity']) && ! isset($column_with_details['model'])) {
            $column_with_details['model'] = $this->getRelationModel($column_with_details['entity']);
        }

        return $this;
    }

    /**
     * Add multiple columns at the end of the CRUD object's "columns" array.
     *
     * @param [array of columns]
     */
    public function addColumns($columns)
    {
        if (count($columns)) {
            foreach ($columns as $key => $column) {
                $this->addColumn($column);
            }
        }
        return $this;
    }

    /**
     * Move the most recently added column after the given target column.
     *
     * @param string|array $targetColumn The target column name or array.
     */
    public function afterColumn($targetColumn)
    {
        $this->moveColumn($targetColumn, false);
    }

    /**
     * Move the most recently added column before the given target column.
     *
     * @param string|array $targetColumn The target column name or array.
     */
    public function beforeColumn($targetColumn)
    {
        $this->moveColumn($targetColumn);
    }

    /**
     * Move the most recently added column before or after the given target column. Default is before.
     *
     * @param string|array $targetColumn The target column name or array.
     * @param bool $before If true, the column will be moved before the target column, otherwise it will be moved after it.
     */
    private function moveColumn($targetColumn, $before = true)
    {
        // TODO: this and the moveField method from the Fields trait should be refactored into a single method and moved
        //       into a common class
        $targetColumnName = is_array($targetColumn) ? $targetColumn['name'] : $targetColumn;

        if (array_key_exists($targetColumnName, $this->columns)) {
            $targetColumnPosition = $before ? array_search($targetColumnName, array_keys($this->columns)) :
                array_search($targetColumnName, array_keys($this->columns)) + 1;

            $element = array_pop($this->columns);
            $beginningPart = array_slice($this->columns, 0, $targetColumnPosition, true);
            $endingArrayPart = array_slice($this->columns, $targetColumnPosition, null, true);

            $this->columns = array_merge($beginningPart, [$element['name'] => $element], $endingArrayPart);
        }
    }

    /**
     * Add the default column type to the given Column, inferring the type from the database column type.
     *
     * @param [column array]
     */
    public function addDefaultTypeToColumn($column)
    {
        if (array_key_exists('name', (array) $column)) {
            $default_type = $this->getFieldTypeFromDbColumnType($column['name']);

            return array_merge(['type' => $default_type], $column);
        }

        return false;
    }

    /**
     * If a field or column array is missing the "label" attribute, an ugly error would be show.
     * So we add the field Name as a label - it's better than nothing.
     *
     * @param [field or column]
     */
    public function addDefaultLabel($array)
    {
        if (! array_key_exists('label', (array) $array) && array_key_exists('name', (array) $array)) {
            $array = array_merge(['label' => ucfirst($this->makeLabel($array['name']))], $array);

            return $array;
        }

        return $array;
    }

    /**
     * Remove a column from the CRUD panel by name.
     *
     * @param string $column The column name.
     */
    public function removeColumn($column)
    {
        array_forget($this->columns, $column);
    }

    /**
     * Remove multiple columns from the CRUD panel by name.
     *
     * @param array $columns Array of column names.
     */
    public function removeColumns($columns)
    {
        if (! empty($columns)) {
            foreach ($columns as $columnName) {
                $this->removeColumn($columnName);
            }
        }
    }

    /**
     * Remove an entry from an array.
     *
     * @param string $entity
     * @param array $fields
     * @return array values
     *
     * @deprecated This method is no longer used by internal code and is not recommended as it does not preserve the
     *             target array keys.
     * @see Columns::removeColumn() to remove a column from the CRUD panel by name.
     * @see Columns::removeColumns() to remove multiple columns from the CRUD panel by name.
     */
    public function remove($entity, $fields)
    {
        return array_values(array_filter($this->{$entity}, function ($field) use ($fields) {
            return ! in_array($field['name'], (array) $fields);
        }));
    }

    /**
     * Change attributes for multiple columns.
     *
     * @param [columns arrays]
     * @param [attributes and values array]
     */
    public function setColumnsDetails($columns, $attributes)
    {
        $this->sync('columns', $columns, $attributes);
    }

    /**
     * Change attributes for a certain column.
     *
     * @param [string] Column name.
     * @param [attributes and values array]
     */
    public function setColumnDetails($column, $attributes)
    {
        $this->setColumnsDetails([$column], $attributes);
    }

    /**
     * Set label for a specific column.
     *
     * @param string $column
     * @param string $label
     */
    public function setColumnLabel($column, $label)
    {
        $this->setColumnDetails($column, ['label' => $label]);
    }

    /**
     * Get the relationships used in the CRUD columns.
     * @return [array] Relationship names
     */
    public function getColumnsRelationships()
    {
        $columns = $this->getColumns();

        return collect($columns)->pluck('entity')->reject(function ($value, $key) {
            return $value == null;
        })->toArray();
    }

    /**
     * Order the CRUD columns. If certain columns are missing from the given order array, they will be pushed to the
     * new columns array in the original order.
     *
     * @param array $order An array of column names in the desired order.
     */
    public function orderColumns($order)
    {
        $orderedColumns = [];
        foreach ($order as $columnName) {
            if (array_key_exists($columnName, $this->columns)) {
                $orderedColumns[$columnName] = $this->columns[$columnName];
            }
        }

        if (empty($orderedColumns)) {
            return;
        }

        $remaining = array_diff_key($this->columns, $orderedColumns);
        $this->columns = array_merge($orderedColumns, $remaining);
    }

    /**
     * Set the order of the CRUD columns.
     *
     * @param array $columns Column order.
     *
     * @deprecated This method was not and will not be implemented since it's a duplicate of the orderColumns method.
     * @see Columns::orderColumns() to order the CRUD columns.
     */
    public function setColumnOrder($columns)
    {
        // not implemented
    }

    /**
     * Set the order of the CRUD columns.
     *
     * @param array $columns Column order.
     *
     * @deprecated This method was not and will not be implemented since it's a duplicate of the orderColumns method.
     * @see Columns::orderColumns() to order the CRUD columns.
     */
    public function setColumnsOrder($columns)
    {
        $this->setColumnOrder($columns);
    }

    /**
     * Get a column by the id, from the associative array.
     * @param  [integer] $column_number Placement inside the columns array.
     * @return [array] Column details.
     */
    public function findColumnById($column_number)
    {
        $result = array_slice($this->getColumns(), $column_number, 1);

        return reset($result);
    }

    protected function hasColumn($table, $name)
    {
        static $cache = [];

        if (isset($cache[$table])) {
            $columns = $cache[$table];
        } else {
            $columns = $cache[$table] = $this->getSchema()->getColumnListing($table);
        }

        return in_array($name, $columns);
    }
}
