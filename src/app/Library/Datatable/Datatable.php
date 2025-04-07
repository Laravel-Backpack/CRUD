<?php

namespace Backpack\CRUD\app\Library\Datatable;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\View\Component;

class Datatable extends Component
{
    public function __construct(
        private string $controller,
        private ?CrudPanel $crud = null,
        private bool $updatesUrl = true,
        private ?string $tableId = null,
        private ?\Closure $configure = null
    ) {
        $this->crud ??= \Backpack\CRUD\Backpack::crudFromController($controller);
        $this->tableId = 'crudTable_'.uniqid();

        // Apply the configuration if provided
        if ($this->configure) {
            ($this->configure)($this->crud);
        }
    }

    public function render()
    {
        return view('crud::datatable.datatable', [
            'crud'       => $this->crud,
            'updatesUrl' => $this->updatesUrl,
            'tableId'    => $this->tableId,
        ]);
    }
}
