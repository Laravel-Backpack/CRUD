<?php

namespace Backpack\CRUD\app\View\Components;

use Backpack\CRUD\CrudManager;
use Closure;
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
        private string $id = 'backpack-form-',
        public string $name = '',
        public string $operation = 'create',
        public ?string $action = null,
        public string $method = 'post',
        public bool $hasUploadFields = false,
        public $entry = null,
        public ?Closure $setup = null,
        public bool $focusOnFirstField = false,

    ) {
        // Get CRUD panel instance from the controller
        CrudManager::setActiveController($controller);

        $this->crud = CrudManager::setupCrudPanel($controller, $operation);

        $this->crud->setAutoFocusOnFirstField($this->focusOnFirstField);

        if ($this->entry && $this->operation === 'update') {
            $this->action = $action ?? url($this->crud->route.'/'.$this->entry->getKey());
            $this->method = 'put';
            $this->crud->entry = $this->entry;
            $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());
        } else {
            $this->action = $action ?? url($this->crud->route);
        }
        $this->hasUploadFields = $this->crud->hasUploadFields($operation, $this->entry?->getKey());
        $this->id = $id.md5($this->action.$this->operation.$this->method.$this->controller);

        if ($this->setup) {
            $this->applySetupClosure();
        }

        CrudManager::unsetActiveController();
    }

    public function applySetupClosure(): bool
    {
        $originalSetup = $this->setup;
        $controllerClass = $this->controller;
        $crud = $this->crud;
        $entry = $this->entry;

        $modifiedSetup = function ($crud, $entry) use ($originalSetup, $controllerClass) {
            CrudManager::setActiveController($controllerClass);

            // Run the original closure
            return ($originalSetup)($crud, $entry);
        };

        try {
            // Execute the modified closure
            ($modifiedSetup)($crud, $entry);

            return true;
        } finally {
            // Clean up
            CrudManager::unsetActiveController();
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        // Store the current form ID in the service container for form-aware old() helper
        app()->instance('backpack.current_form_id', $this->id);

        return view('crud::components.dataform.form', [
            'crud' => $this->crud,
            'saveAction' => $this->crud->getSaveAction(),
            'id' => $this->id,
            'name' => $this->name,
            'operation' => $this->operation,
            'action' => $this->action,
            'method' => $this->method,
            'hasUploadFields' => $this->hasUploadFields,
            'entry' => $this->entry,
        ]);
    }
}
