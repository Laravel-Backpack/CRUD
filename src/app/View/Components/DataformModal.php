<?php

namespace Backpack\CRUD\app\View\Components;

use Backpack\CRUD\app\View\Components\Contracts\IsolatesOperationSetup;
use Closure;

class DataformModal extends Dataform implements IsolatesOperationSetup
{
    /**
     * Modal forms ALWAYS isolate their operation setup.
     * This prevents them from affecting the parent view's operation state.
     */
    public function shouldIsolateOperationSetup(): bool
    {
        return true;
    }

    public string $hashedFormId;

    public function __construct(
        public string $controller,
        public string $id = 'backpack-form',
        public string $formOperation = 'create',
        public ?string $formUrl = null,
        public ?string $formAction = null,
        public ?string $formMethod = null,
        public bool $hasUploadFields = false,
        public $entry = null,
        public ?Closure $setup = null,
        public bool $focusOnFirstField = false,
        public string $title = 'Form',
        public string $classes = 'modal-dialog modal-lg',
        public bool $refreshDatatable = false,
    ) {
        \Backpack\CRUD\CrudManager::setActiveController($controller);
        $this->crud = \Backpack\CRUD\CrudManager::setupCrudPanel($controller, $this->formOperation);
        \Backpack\CRUD\CrudManager::unsetActiveController();
    }

    /**
     * Cache the setup closure for later retrieval during AJAX form load.
     */
    protected function cacheSetupClosure(): void
    {
        // Create a temporary CRUD instance to apply and cache the setup
        \Backpack\CRUD\CrudManager::setActiveController($this->controller);
        $tempCrud = \Backpack\CRUD\CrudManager::setupCrudPanel($this->controller, $this->formOperation);

        // Apply and cache the setup closure using the HASHED ID
        \Backpack\CRUD\app\Library\Support\DataformCache::applyAndStoreSetupClosure(
            $this->hashedFormId,  // Use the hashed ID that matches what Dataform component generates
            $this->controller,
            $this->setup,
            $tempCrud,
            $this->entry
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
        $this->hashedFormId = $this->id.md5($this->formAction.$this->formOperation.'post'.$this->controller);

        if (empty($this->formUrl)) {
            $this->formUrl = isset($this->entry) ? url($this->crud->route.'/'.$this->entry->getKey().'/edit') : url($this->crud->route.'/create');
        }

        if (empty($this->formAction)) {
            $this->formAction = isset($this->entry) ? url($this->crud->route.'/'.$this->entry->getKey()) : url($this->crud->route);
            $this->formMethod = $this->formMethod ?? (isset($this->entry) ? 'put' : 'post');
        }

        // Cache the setup closure if provided (for retrieval during AJAX request)
        if ($this->setup instanceof \Closure) {
            $this->cacheSetupClosure();
        }

        return view('crud::components.dataform.modal-form', [
            'id' => $this->id,
            'formOperation' => $this->formOperation,
            'formUrl' => $this->formUrl,
            'entry' => $this->entry,
            'hasUploadFields' => $this->hasUploadFields,
            'refreshDatatable' => $this->refreshDatatable,
            'formAction' => $this->formAction,
            'formMethod' => $this->formMethod,
            'title' => $this->title,
            'classes' => $this->classes,
            'hashedFormId' => $this->hashedFormId,
            'controller' => $this->controller,
        ]);
    }
}
