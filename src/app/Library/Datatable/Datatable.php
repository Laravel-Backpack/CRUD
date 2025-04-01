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
        private array $tableOptions = []
    ) {
        $this->crud ??= \Backpack\CRUD\Backpack::crudFromController($controller);
        $this->tableId = 'crudTable_'.uniqid();

        // Merge default options with provided options
        $this->tableOptions = array_merge([
            'pageLength' => $this->crud->getDefaultPageLength(),
            'searchDelay' => $this->crud->getOperationSetting('searchDelay'),
            'searchableTable' => $this->crud->getOperationSetting('searchableTable') ?? true,
        ], $tableOptions);
    }

    public function render()
    {
        return view('crud::datatable.datatable', [
            'crud' => $this->crud,
            'updatesUrl' => $this->updatesUrl,
            'tableId' => $this->tableId,
            'tableOptions' => $this->tableOptions,
        ]);
    }
}
