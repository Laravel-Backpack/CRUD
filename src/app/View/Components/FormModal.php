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
     * @param  string  $buttonText  Text to display on the button that opens the modal
     * @param  string  $modalTitle  Title for the modal
     * @param  string  $buttonClass  CSS classes for the button
     */
    public function __construct(
        public string $controller,
        public string $id = 'backpack-form',
        public string $operation = 'create',
        public string $formRouteOperation = 'create',
        public ?string $action = null,
        public string $method = 'post',
        public string $modalTitle = 'Form',
        public string $modalClasses = "modal-dialog modal-lg"
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
        return view('crud::components.form.modal_form', [
            'crud' => $this->crud,
            'id' => $this->id,
            'operation' => $this->operation,
            'formRouteOperation' => $this->formRouteOperation,
            'action' => $this->action,
            'method' => $this->method,
            'modalTitle' => $this->modalTitle,
            'modalClasses' => $this->modalClasses,
        ]);
    }
}
