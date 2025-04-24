<?php

namespace Backpack\CRUD\app\Library\Datatable;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\Backpack;
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
        // Set active controller for proper context
        Backpack::setActiveController($controller);

        $this->crud ??= Backpack::crudFromController($controller);

        $this->tableId = $tableId ?? 'crudTable_'.uniqid();

        if ($this->configure) {
            ($this->configure)($this->crud);
        }

        if (! $this->crud->getOperationSetting('datatablesUrl')) {
            $this->crud->setOperationSetting('datatablesUrl', $this->crud->getRoute());
        }

        // Reset the active controller
        Backpack::unsetActiveController($controller);
    }

    public function render()
    {
        return view('crud::datatable.datatable', [
            'crud' => $this->crud,
            'updatesUrl' => $this->updatesUrl,
            'tableId' => $this->tableId,
        ]);
    }
}
