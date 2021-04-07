<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Carbon\Carbon;
use Validator;

trait Search
{
    /*
    |--------------------------------------------------------------------------
    |                                   SEARCH
    |--------------------------------------------------------------------------
    */

    /**
     * Add conditions to the CRUD query for a particular search term.
     *
     * @param string $searchTerm Whatever string the user types in the search bar.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applySearchTerm($searchTerm)
    {
        return $this->query->where(function ($query) use ($searchTerm) {
            foreach ($this->columns() as $column) {
                if (! isset($column['type'])) {
                    abort(400, 'Missing column type when trying to apply search term.');
                }

                $this->applySearchLogicForColumn($query, $column, $searchTerm);
            }
        });
    }

    /**
     * Apply the search logic for each CRUD column.
     */
    public function applySearchLogicForColumn($query, $column, $searchTerm)
    {
        $columnType = $column['type'];

        // if there's a particular search logic defined, apply that one
        if (isset($column['searchLogic'])) {
            $searchLogic = $column['searchLogic'];

            // if a closure was passed, execute it
            if (is_callable($searchLogic)) {
                return $searchLogic($query, $column, $searchTerm);
            }

            // if a string was passed, search like it was that column type
            if (is_string($searchLogic)) {
                $columnType = $searchLogic;
            }

            // if false was passed, don't search this column
            if ($searchLogic == false) {
                return;
            }
        }

        // sensible fallback search logic, if none was explicitly given
        if ($column['tableColumn']) {
            switch ($columnType) {
                case 'email':
                case 'text':
                case 'textarea':
                    $query->orWhere($this->getColumnWithTableNamePrefixed($query, $column['name']), 'like', '%'.$searchTerm.'%');
                    break;

                case 'date':
                case 'datetime':
                    $validator = Validator::make(['value' => $searchTerm], ['value' => 'date']);

                    if ($validator->fails()) {
                        break;
                    }

                    $query->orWhereDate($this->getColumnWithTableNamePrefixed($query, $column['name']), Carbon::parse($searchTerm));
                    break;

                case 'select':
                case 'select_multiple':
                    $query->orWhereHas($column['entity'], function ($q) use ($column, $searchTerm) {
                        $q->where($this->getColumnWithTableNamePrefixed($q, $column['attribute']), 'like', '%'.$searchTerm.'%');
                    });
                    break;

                default:
                    return;
                    break;
            }
        }
    }

    // -------------------------
    // Responsive Table
    // -------------------------

    /**
     * Tell the list view to NOT show a reponsive DataTable.
     *
     * @param bool $value
     */
    public function setResponsiveTable($value = true)
    {
        $this->setOperationSetting('responsiveTable', $value);
    }

    /**
     * Check if responsiveness is enabled for the table view.
     *
     * @return bool
     */
    public function getResponsiveTable()
    {
        if ($this->getOperationSetting('responsiveTable') !== null) {
            return $this->getOperationSetting('responsiveTable');
        }

        return config('backpack.crud.operations.list.responsiveTable');
    }

    /**
     * Remember to show a responsive table.
     */
    public function enableResponsiveTable()
    {
        $this->setResponsiveTable(true);
    }

    /**
     * Remember to show a table with horizontal scrolling.
     */
    public function disableResponsiveTable()
    {
        $this->setResponsiveTable(false);
    }

    // -------------------------
    // Persistent Table
    // -------------------------

    /**
     * Tell the list view to NOT store datatable information in local storage.
     *
     * @param bool $value
     */
    public function setPersistentTable($value = true)
    {
        return $this->setOperationSetting('persistentTable', $value);
    }

    /**
     * Check if saved state is enabled for the table view.
     *
     * @return bool
     */
    public function getPersistentTable()
    {
        if ($this->getOperationSetting('persistentTable') !== null) {
            return $this->getOperationSetting('persistentTable');
        }

        return config('backpack.crud.operations.list.persistentTable');
    }

    /**
     * Get duration for persistent table.
     *
     * @return bool
     */
    public function getPersistentTableDuration()
    {
        if ($this->getOperationSetting('persistentTableDuration') !== null) {
            return $this->getOperationSetting('persistentTableDuration');
        }

        return config('backpack.crud.operations.list.persistentTableDuration', false);
    }

    /**
     * Remember to show a persistent table.
     */
    public function enablePersistentTable()
    {
        return $this->setPersistentTable(true);
    }

    /**
     * Remember to show a table that doesn't store URLs and pagination in local storage.
     */
    public function disablePersistentTable()
    {
        return $this->setPersistentTable(false);
    }

    /**
     * Get the HTML of the cells in a table row, for a certain DB entry.
     *
     * @param \Illuminate\Database\Eloquent\Model $entry     A db entry of the current entity;
     * @param bool|int                            $rowNumber The number shown to the user as row number (index);
     *
     * @return array Array of HTML cell contents.
     */
    public function getRowViews($entry, $rowNumber = false)
    {
        $row_items = [];

        // add an empty first column to support details row and/or data table modal
        $row_items[] = '';

        foreach ($this->columns() as $key => $column) {
            $row_items[] = $this->getCellView($column, $entry, $rowNumber);
        }

        // add the buttons as the last column
        if ($this->buttons()->where('stack', 'line')->count()) {
            $row_items[] = \View::make('crud::inc.button_stack', ['stack' => 'line'])
                                ->with('crud', $this)
                                ->with('entry', $entry)
                                ->with('row_number', $rowNumber)
                                ->render();
        }

        // add the details_row button to the first column
        if ($this->getOperationSetting('detailsRow')) {
            $details_row_button = \View::make('crud::columns.inc.details_row_button')
                                           ->with('crud', $this)
                                           ->with('entry', $entry)
                                           ->with('row_number', $rowNumber)
                                           ->render();
            $row_items[0] = $details_row_button.$row_items[0];
        }

        return $row_items;
    }

    /**
     * Get the HTML of a cell, using the column types.
     *
     * @param array                               $column
     * @param \Illuminate\Database\Eloquent\Model $entry     A db entry of the current entity;
     * @param bool|int                            $rowNumber The number shown to the user as row number (index);
     *
     * @return string
     */
    public function getCellView($column, $entry, $rowNumber = false)
    {
        return $this->renderCellView($this->getCellViewName($column), $column, $entry, $rowNumber);
    }

    /**
     * Get the name of the view to load for the cell.
     *
     * @param array $column
     *
     * @return string
     */
    private function getCellViewName($column)
    {
        // return custom column if view_namespace attribute is set
        if (isset($column['view_namespace']) && isset($column['type'])) {
            return $column['view_namespace'].'.'.$column['type'];
        }

        if (isset($column['type'])) {
            // if the column has been overwritten return that one
            if (view()->exists('vendor.backpack.crud.columns.'.$column['type'])) {
                return 'vendor.backpack.crud.columns.'.$column['type'];
            }

            // return the column from the package
            return 'crud::columns.'.$column['type'];
        }

        // fallback to text column
        return 'crud::columns.text';
    }

    /**
     * Render the given view.
     *
     * @param string   $view
     * @param array    $column
     * @param object   $entry
     * @param bool|int $rowNumber The number shown to the user as row number (index)
     *
     * @return string
     */
    private function renderCellView($view, $column, $entry, $rowNumber = false)
    {
        if (! view()->exists($view)) {
            $view = 'crud::columns.text'; // fallback to text column
        }

        return \View::make($view)
            ->with('crud', $this)
            ->with('column', $column)
            ->with('entry', $entry)
            ->with('rowNumber', $rowNumber)
            ->render();
    }

    /**
     * Created the array to be fed to the data table.
     *
     * @param array    $entries      Eloquent results.
     * @param int      $totalRows
     * @param int      $filteredRows
     * @param bool|int $startIndex
     *
     * @return array
     */
    public function getEntriesAsJsonForDatatables($entries, $totalRows, $filteredRows, $startIndex = false)
    {
        $rows = [];

        foreach ($entries as $row) {
            $rows[] = $this->getRowViews($row, $startIndex === false ? false : ++$startIndex);
        }

        return [
            'draw'            => (isset($this->getRequest()['draw']) ? (int) $this->getRequest()['draw'] : 0),
            'recordsTotal'    => $totalRows,
            'recordsFiltered' => $filteredRows,
            'data'            => $rows,
        ];
    }

    /**
     * Return the column attribute (column in database) prefixed with table to use in search.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @return string
     */
    public function getColumnWithTableNamePrefixed($query, $column)
    {
        return $query->getModel()->getTable().'.'.$column;
    }
}
