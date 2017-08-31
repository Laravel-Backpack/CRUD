<?php

namespace Backpack\CRUD\app\Http\Controllers\CrudFeatures;

use Backpack\CRUD\CrudPanel;
use LiveControl\EloquentDataTable\DataTable;

/**
 * Trait AjaxTable.
 *
 * @property CrudPanel $crud
 */
trait AjaxTable
{
    /**
     * Respond with the JSON of one or more rows, depending on the POST parameters.
     * @return array Array of cells in HTML form.
     */
    public function search()
    {
        $this->crud->hasAccessOrFail('list');

        // create an array with the names of the searchable columns
        $columns = $this->ajaxTableGetColumns();

        // structure the response in a DataTable-friendly way
        $dataTable = $this->ajaxTableGetDataTable($columns);

        return $dataTable->make();
    }

    /**
     * @return array
     */
    protected function ajaxTableGetColumns()
    {
        return collect($this->crud->columns)
            ->reject(function ($column) {
                return $this->ajaxTableIsColumnSearchable($column);
            })
            ->pluck('name')
            // add the primary key, otherwise the buttons won't work
            ->merge($this->crud->model->getKeyName())
            ->toArray();
    }

    /**
     * @param array $column
     * @return bool
     */
    protected function ajaxTableIsColumnSearchable($column)
    {
        // the select_multiple, model_function and model_function_attribute columns are not searchable
        return isset($column['type'])
            && ($column['type'] == 'select_multiple'
                || $column['type'] == 'model_function'
                || $column['type'] == 'model_function_attribute');
    }

    /**
     * @param array $columns
     * @return DataTable
     */
    protected function ajaxTableGetDataTable($columns)
    {
        $dataTable = new DataTable($this->crud->query, $columns);

        // make the datatable use the column types instead of just echoing the text
        $dataTable->setFormatRowFunction(function ($entry) {
            // get the actual HTML for each row's cell
            $row_items = $this->crud->getRowViews($entry);

            // add the buttons as the last column
            if ($this->crud->buttons->where('stack', 'line')->count()) {
                $row_items[] = \View::make('crud::inc.button_stack', ['stack' => 'line'])
                    ->with('crud', $this->crud)
                    ->with('entry', $entry)
                    ->render();
            }

            // add the details_row buttons as the first column
            if ($this->crud->details_row) {
                array_unshift($row_items, \View::make('crud::columns.details_row_button')
                    ->with('crud', $this->crud)
                    ->with('entry', $entry)
                    ->render());
            }

            return $row_items;
        });

        return $dataTable;
    }
}
