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
     * @param  bool  $focusOnFirstField  Whether to focus on the first field when form loads
     * @param  bool|null  $showCancelButton  Override for the CRUD cancel button visibility
     */
    public function __construct(
        public string $controller,
        private string $formId = 'backpack-form-',
        public string $formOperation = 'create',
        public ?string $formUrl = null,
        public ?string $formAction = null,
        public ?string $formMethod = 'post',
        public bool $hasUploadFields = false,
        public $entry = null,
        public ?Closure $setup = null,
        public bool $focusOnFirstField = false,
        public ?bool $showCancelButton = null,
        public bool $formInsideCard = false,
        public array $saveActions = [],
    ) {
        // Get CRUD panel instance from the controller
        CrudManager::setActiveController($controller);

        $this->crud = CrudManager::setupCrudPanel($controller, $this->formOperation);

        if ($this->crud->getOperation() !== $this->formOperation) {
            $this->crud->setOperation($this->formOperation);
        }

        $this->crud->setAutoFocusOnFirstField($this->focusOnFirstField);

        if ($this->crud->getOperationSetting('save_actions') === null) {
            $this->crud->setupDefaultSaveActions();
        }

        if (! empty($this->saveActions)) {
            $this->crud->replaceSaveActions($this->saveActions);
        }

        if ($this->entry && $this->formOperation === 'update') {
            $this->formAction = $formAction ?? url($this->crud->route.'/'.$this->entry->getKey());
            $this->formMethod = 'put';
            $this->crud->entry = $this->entry;
            $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());
            $this->formUrl = url($this->crud->route.'/'.$this->entry->getKey().'/edit');
        } else {
            $this->formAction = $formAction ?? url($this->crud->route);
            $this->formUrl = url($this->crud->route.'/create');
        }

        $this->hasUploadFields = $this->crud->hasUploadFields($this->formOperation, $this->entry?->getKey());
        $this->formId = $formId.md5($this->formAction.$this->formOperation.$this->formMethod.$this->controller);

        if ($this->setup) {
            $parentEntry = $this->getParentCrudEntry();
            call_user_func($this->setup, $this->crud, $parentEntry);
        }

        if (! is_null($showCancelButton)) {
            $this->crud->setOperationSetting('showCancelButton', $showCancelButton);
        }

        // Reset the active controller
        CrudManager::unsetActiveController();
    }

    private function getParentCrudEntry()
    {
        $cruds = CrudManager::getCrudPanels();
        $parentCrud = reset($cruds);

        if ($parentCrud && $parentCrud->getCurrentEntry()) {
            CrudManager::storeInitializedOperation(
                $parentCrud->controller,
                $parentCrud->getCurrentOperation()
            );

            return $parentCrud->getCurrentEntry();
        }

        return null;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        // Store the current form ID in the service container for form-aware old() helper
        app()->instance('backpack.current_form_id', $this->formId);

        return view('crud::components.dataform.form', [
            'crud' => $this->crud,
            'saveAction' => $this->crud->getSaveAction(),
            'formId' => $this->formId,
            'formOperation' => $this->formOperation,
            'formAction' => $this->formAction,
            'formMethod' => $this->formMethod,
            'hasUploadFields' => $this->hasUploadFields,
            'entry' => $this->entry,
            'formInsideCard' => $this->formInsideCard,
        ]);
    }
}
