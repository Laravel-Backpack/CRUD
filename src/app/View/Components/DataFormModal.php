<?php

namespace Backpack\CRUD\app\View\Components;

use Closure;

class DataformModal extends DataForm
{
    /**
     * Create a new component instance.
     *
     * @param  string  $controller  The CRUD controller class name
     * @param  string  $operation  The operation to use (create, update, etc.)
     * @param  string|null  $action  Custom form action URL
     * @param  string  $method  Form method (post, put, etc.)
     * @param  bool  $hasUploadFields  Whether the form has upload fields
     * @param  mixed|null  $entry  The model instance for update operations
     * @param  Closure|null  $setup  A closure to customize the CRUD panel
     * @param  string  $formRouteOperation  The operation to use for the form route (defaults to 'create')
     * @param  string  $id  The ID for the form element (defaults to 'backpack-form')
     * @param  bool  $focusOnFirstField  Whether to focus on the first field when form loads
     * @param  string  $title  The title of the modal
     * @param  string  $classes  CSS classes for the modal dialog
     * @param  bool  $refreshDatatable  Whether to refresh the datatable after form submission
     */
    public string $hashedFormId;

    public function __construct(
        public string $controller,
        public string $id = 'backpack-form',
        public string $operation = 'create',
        public string $name = '',
        public string $formRouteOperation = 'create',
        public ?string $action = null,
        public string $method = 'post',
        public bool $hasUploadFields = false,
        public $entry = null,
        public ?Closure $setup = null,
        public bool $focusOnFirstField = false,
        public string $title = 'Form',
        public string $classes = 'modal-dialog modal-lg',
        public bool $refreshDatatable = false,
    ) {
        // Temporarily set up CRUD panel to get the route for hashed form ID generation
        \Backpack\CRUD\CrudManager::setActiveController($controller);
        $tempCrud = \Backpack\CRUD\CrudManager::setupCrudPanel($controller, $operation);
        $action = $this->action ?? url($tempCrud->route);
        \Backpack\CRUD\CrudManager::unsetActiveController();
        
        // Generate the SAME hashed form ID that the DataForm component uses
        $this->hashedFormId = $this->id . md5($action . $this->operation . 'post' . $this->controller);
        
        // Cache the setup closure if provided (for retrieval during AJAX request)
        if ($this->setup instanceof \Closure) {
            $this->cacheSetupClosure();
        }
        
        parent::__construct($controller, $id, $name, $operation, $action, $method, $hasUploadFields, $entry, $setup, $focusOnFirstField);
    
        if($this->entry && $this->operation === 'update') {
            $this->formRouteOperation = url($this->crud->route.'/'.$this->entry->getKey().'/edit');
        }    
    }

    /**
     * Cache the setup closure for later retrieval during AJAX form load.
     */
    protected function cacheSetupClosure(): void
    {
        // Create a temporary CRUD instance to apply and cache the setup
        \Backpack\CRUD\CrudManager::setActiveController($this->controller);
        $tempCrud = \Backpack\CRUD\CrudManager::setupCrudPanel($this->controller, $this->operation);
        
        // Apply and cache the setup closure using the HASHED ID
        \Backpack\CRUD\app\Library\Support\DataformCache::applyAndStoreSetupClosure(
            $this->hashedFormId,  // Use the hashed ID that matches what DataForm component generates
            $this->controller,
            $this->setup,
            null,
            $tempCrud,
            null
        );
        
        \Backpack\CRUD\CrudManager::unsetActiveController();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('crud::components.dataform.modal-form', [
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
            'hashedFormId' => $this->hashedFormId,
            'controller' => $this->controller,
        ]);
    }
}
