<?php

namespace Backpack\CRUD\app\View\Components;

use Illuminate\View\Component;

class Datagrid extends Component
{
    public $columns;
    public $entry;
    public $crud;
    public $displayActionsColumn;

    /**
     * Create a new component instance.
     */
    public function __construct($columns, $entry, $crud, $displayActionsColumn = true)
    {
        $this->columns = $columns;
        $this->entry = $entry;
        $this->crud = $crud;
        $this->displayActionsColumn = $displayActionsColumn;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        // if no columns are set, don't load any view
        if (empty($this->columns)) {
            return '';
        }

        return view('crud::components.datagrid');
    }
}
