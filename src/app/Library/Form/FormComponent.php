<?php

namespace Backpack\CRUD\app\Library\Form;

use Backpack\CRUD\CrudManager;
use Illuminate\View\Component;

class FormComponent extends Component
{
    public $crud;
    public $operation;
    public $formAction;
    public $formMethod;

    /**
     * Create a new component instance.
     *
     * @param string $controller The CRUD controller class name
     * @param string $operation The operation to use (create, update, etc.)
     * @param string|null $action Custom form action URL
     * @param string $method Form method (post, put, etc.)
     */
    public function __construct(
        public string $controller,
        string $operation = 'create',
        ?string $action = null,
        string $method = 'post'
    ) {
        // Get CRUD panel instance from the controller
        $this->crud = CrudManager::crudFromController($controller, $operation);
        $this->operation = $operation;
        $this->formAction = $action ?? url($this->crud->route);
        $this->formMethod = $method;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('crud::form.form_component', [
            'crud' => $this->crud,
            'operation' => $this->operation,
            'formAction' => $this->formAction,
            'formMethod' => $this->formMethod,
        ]);
    }
}