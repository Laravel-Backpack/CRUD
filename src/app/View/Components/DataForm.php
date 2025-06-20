<?php

namespace Backpack\CRUD\app\View\Components;

use Backpack\CRUD\CrudManager;
use Illuminate\View\Component;

class Dataform extends Component
{
    public $crud;

    /**
     * Create a new component instance.
     *
     * @param  string  $controller  The CRUD controller class name
     * @param  string  $operation  The operation to use (create, update, etc.)
     * @param  string|null  $action  Custom form action URL
     * @param  string  $method  Form method (post, put, etc.)
     */
    public function __construct(
        public string $controller,
        public string $id = 'backpack-form',
        public string $operation = 'create',
        public ?string $action = null,
        public string $method = 'post',
        public bool $hasUploadFields = false,

    ) {
        // Get CRUD panel instance from the controller
        if (CrudManager::hasCrudPanel($controller)) {
            $previousOperation = CrudManager::getCrudPanel($controller)->getOperation();
        }

        $this->crud = CrudManager::setupCrudPanel($controller, $operation);

        if (isset($previousOperation)) {
            $this->crud->setOperation($previousOperation);
        }

        $this->operation = $operation;
        $this->action = $action ?? url($this->crud->route);
        $this->hasUploadFields = $this->crud->hasUploadFields($operation);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('crud::components.dataform.form', [
            'crud' => $this->crud,
            'saveAction' => $this->crud->getSaveAction(),
            'id' => $this->id,
            'operation' => $this->operation,
            'action' => $this->action,
            'method' => $this->method,
        ]);
    }
}
