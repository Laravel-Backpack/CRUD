<?php


namespace Backpack\CRUD\ColumnsTraits;

use Backpack\CRUD\Columns\Column;
use Backpack\CRUD\Facades\CRUDPanel;

trait ManageColumns
{

    /**
     * Remove all previously set columns and add the provided ones
     * @param $columns array
     */
    public function set($columns)
    {
        CRUDPanel::setColumns($columns);
    }

    /**
     * @param $columns [string, array or multidimensional array]
     * @return \Backpack\CRUD\Facades\CRUDPanel
     */
    public function add($columns)
    {
        // if a string was passed
        if (! is_array($columns)) {
            return CRUDPanel::addColumn($columns);
        }

        if (is_array($columns) && count($columns)) {
            // it's a column as an associative array
            if (key_exists('name', $columns) || key_exists('label', $columns) ) {
                return CRUDPanel::addColumn($columns);
            }
            // we have an array of columns
            return CRUDPanel::addColumns($columns);
        }
    }

    /**
     * @param $columns [column array, Column, array of Columns or array of column arrays]
     */
    public function remove($columns)
    {
        //TODO: remove seems to be buggy...
        if (! is_array($columns)) {
            if ($columns instanceof Column) {
                CRUDPanel::removeColumn($columns->toArray()); //does not work :/
            } else {
                CRUDPanel::removeColumn($columns);
            }
        } else {
            $columns = collect($columns)->transform(function ($column) {
                if ($column instanceof Column) {
                    return $column->toArray();
                } else {
                    return $column;
                }
            });
            CRUDPanel::removeColumns($columns);
        }
    }
}