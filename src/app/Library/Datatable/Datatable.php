<?php

namespace Backpack\CRUD\app\Library\Datatable;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\View\Component;

class Datatable extends Component
{
    public function __construct(private CrudPanel $crud)
    {
    }

    public function render()
    {
        return view('crud::datatable.datatable', [
            'crud' => $this->crud,
        ]);
    }
}
