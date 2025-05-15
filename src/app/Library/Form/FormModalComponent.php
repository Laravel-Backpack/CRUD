<?php

namespace Backpack\CRUD\app\Library\Form;

class FormModalComponent extends FormComponent
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
        string $controller,
        string $operation = 'create',
        ?string $action = null,
        string $method = 'post',
        public string $buttonText = 'Open Form',
        public string $modalTitle = 'Form',
        public string $buttonClass = 'btn btn-primary'
    ) {
        parent::__construct($controller, $operation, $action, $method);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('crud::form.modal_form_component', [
            'crud' => $this->crud,
            'operation' => $this->operation,
            'formAction' => $this->formAction,
            'formMethod' => $this->formMethod,
            'buttonText' => $this->buttonText,
            'modalTitle' => $this->modalTitle,
            'buttonClass' => $this->buttonClass,
        ]);
    }
}
