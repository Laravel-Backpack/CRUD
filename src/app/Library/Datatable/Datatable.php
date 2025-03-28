<?php

namespace Backpack\CRUD\app\Library\Datatable;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\View\Component;

class Datatable extends Component
{
    public function __construct(private string $controller, private ?CrudPanel $crud = null, private bool $updatesUrl = true)
    {
        $this->crud ??= \Backpack\CRUD\Backpack::crudFromController($controller);
    }

    public function render()
    {
        return view('crud::datatable.datatable', [
            'crud'       => $this->crud,
            'updatesUrl' => $this->updatesUrl,
        ]);
    }
}
