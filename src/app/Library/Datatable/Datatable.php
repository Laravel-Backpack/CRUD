<?php

namespace Backpack\CRUD\app\Library\Datatable;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\View\Component;

class Datatable extends Component
{
    private CrudPanel $crud;

    public function __construct(private string $controller)
    {
        $this->crud = \Backpack\CRUD\Backpack::crudFromController($controller);
    }

    public function render()
    {
        return view('crud::datatable.datatable', [
            'crud' => $this->crud,
        ]);
    }
}
