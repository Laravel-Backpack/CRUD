<?php

namespace Backpack\CRUD\app\View\Components;

class FormModal extends Form
{
    /**
     * Create a new component instance.
     *
     * @param  string  $controller  The CRUD controller class name
     * @param  string  $operation  The operation to use (create, update, etc.)
     * @param  string|null  $action  Custom form action URL
     * @param  string  $method  Form method (post, put, etc.)
     * @param  string  $modalTitle  Title for the modal
     * @param  string  $modalClasses  CSS classes for the modal dialog
     * @param  string  $formRouteOperation  The operation to use for the form route (defaults to 'create')
     * @param  string  $id  The ID for the form element (defaults to 'backpack-form')
     */
    public function __construct(
        public string $controller,
        public string $id = 'backpack-form',
        public string $operation = 'create',
        public string $formRouteOperation = 'create',
        public ?string $action = null,
        public string $method = 'post',
        public string $title = 'Form',
        public string $classes = 'modal-dialog modal-lg',
        public bool $refreshDatatable = false,
    ) {
        parent::__construct($controller, $id, $operation, $action, $method);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('crud::components.form.modal', [
            'crud' => $this->crud,
            'id' => $this->id,
            'operation' => $this->operation,
            'formRouteOperation' => $this->formRouteOperation,
            'hasUploadFields' => $this->hasUploadFields,
            'refreshDatatable' => $this->refreshDatatable,
            'action' => $this->action,
            'method' => $this->method,
            'title' => $this->title,
            'classes' => $this->classes,
        ]);
    }
}
