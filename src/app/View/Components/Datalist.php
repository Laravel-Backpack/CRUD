<?php

namespace Backpack\CRUD\app\View\Components;

use Illuminate\View\Component;

class Datalist extends Component
{
    public $entry;
    public $crud;
    public $columns;
    public $displayButtons;

    /**
     * Create a new component instance.
     */
    public function __construct($entry, $crud = null, $columns = [], $displayButtons = true)
    {
        $this->columns = $columns ?? $crud?->columns() ?? [];
        $this->entry = $entry;
        $this->crud = $crud;
        $this->displayButtons = $displayButtons;
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

        return view('crud::components.datalist');
    }
}
