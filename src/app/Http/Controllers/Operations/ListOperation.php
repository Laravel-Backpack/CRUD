<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

trait ListOperation
{
    /**
     * Display all rows in the database for this entity.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->crud->hasAccessOrFail('list');
        $this->crud->setOperation('list');

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getListView(), $this->data);
    }

    /**
     * The search function that is called by the data table.
     *
     * @return array JSON Array of cells in HTML form.
     */
    public function search()
    {
        $this->crud->hasAccessOrFail('list');
        $this->crud->setOperation('list');

        $totalRows = $this->crud->model->count();
        $filteredRows = $this->crud->count();
        $startIndex = $this->request->input('start') ?: 0;
        // if a search term was present
        if ($this->request->input('search') && $this->request->input('search')['value']) {
            // filter the results accordingly
            $this->crud->applySearchTerm($this->request->input('search')['value']);
            // recalculate the number of filtered rows
            $filteredRows = $this->crud->count();
        }
        // start the results according to the datatables pagination
        if ($this->request->input('start')) {
            $this->crud->skip((int) $this->request->input('start'));
        }
        // limit the number of results according to the datatables pagination
        if ($this->request->input('length')) {
            $this->crud->take((int) $this->request->input('length'));
        }
        // overwrite any order set in the setup() method with the datatables order
        if ($this->request->input('order')) {
            // clear any past orderBy rules
            $this->crud->query->getQuery()->orders = null;

            foreach ($this->request->input('order') as $order) {
                $column_number = $order['column'];
                $column_direction = $order['dir'];
                if (is_numeric($column_number)) {
                    $column = $this->crud->findColumnById($column_number);
                    if ($column['tableColumn']) {
                        // apply the current orderBy rules
                        $this->crud->orderBy($column['name'], $column_direction);
                    }
                } else {
                    $this->crud->orderBy($column_number, $column_direction);
                }
            }

            // check for custom order logic in the column definition
            if (isset($column['orderLogic'])) {
                $this->crud->customOrderBy($column, $column_direction);
            }
        }
        $entries = $this->crud->getEntries();

        return $this->crud->getEntriesAsJsonForDatatables($entries, $totalRows, $filteredRows, $startIndex);
    }

    /**
     * Used with AJAX in the list view (datatables) to show extra information about that row that didn't fit in the table.
     * It defaults to showing some dummy text.
     *
     * It's enabled by:
     * - setting: $crud->details_row = true;
     * - adding the details route for the entity; ex: Route::get('page/{id}/details', 'PageCrudController@showDetailsRow');
     * - adding a view with the following name to change what the row actually contains: app/resources/views/vendor/backpack/crud/details_row.blade.php
     *
     * @return \Illuminate\View\View
     */
    public function showDetailsRow($id)
    {
        $this->crud->hasAccessOrFail('details_row');
        $this->crud->setOperation('list');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;

        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getDetailsRowView(), $this->data);
    }
}
