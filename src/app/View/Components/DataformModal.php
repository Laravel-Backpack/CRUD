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
        public ?string $route = null,
        public string $id = 'backpack-form',
        public string $formOperation = 'create',
        public string $name = '',
        public string $formUrl = 'create',
        public ?string $formAction = null,
        public string $formMethod = 'post',
        public bool $hasUploadFields = false,
        public $entry = null,
        public ?Closure $setup = null,
        public bool $focusOnFirstField = false,
        public string $title = 'Form',
        public string $classes = 'modal-dialog modal-lg',
        public bool $refreshDatatable = false,
    ) {
        // If route is not provided (e.g., when rendering via AJAX), get it from CRUD panel
        if ($this->route === null) {
            \Backpack\CRUD\CrudManager::setActiveController($controller);
            $tempCrud = \Backpack\CRUD\CrudManager::setupCrudPanel($controller, $this->formOperation);
            $this->route = $tempCrud->route;
            \Backpack\CRUD\CrudManager::unsetActiveController();
        }

        // keep backwards compatible behavior for resolving route when not provided
        $this->formAction = $this->formAction ?? url($this->route);

        // Generate the SAME hashed form ID that the Dataform component uses
        $this->hashedFormId = $this->id.md5($this->formAction.$this->formOperation.'post'.$this->controller);

        // Cache the setup closure if provided (for retrieval during AJAX request)
        if ($this->setup instanceof \Closure) {
            $this->cacheSetupClosure();
        }

        // DO NOT call parent::__construct() because we don't want to initialize
        // the CRUD panel on page load - the form will be loaded via AJAX

        if ($this->entry && $this->formOperation === 'update') {
            // Use the resolved action (base route) to build the edit URL for the entry
            $this->formUrl = url($this->formAction.'/'.$this->entry->getKey().'/edit');
        }
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
        // We don't need $crud here because the modal loads the form via AJAX
        // The CRUD panel will be initialized when the AJAX request is made
        return view('crud::components.dataform.modal-form', [
            'id' => $this->id,
            'formOperation' => $this->formOperation,
            'formUrl' => $this->formUrl,
            'hasUploadFields' => $this->hasUploadFields,
            'refreshDatatable' => $this->refreshDatatable,
            'formAction' => $this->formAction,
            'formMethod' => $this->formMethod,
            'title' => $this->title,
            'classes' => $this->classes,
            'hashedFormId' => $this->hashedFormId,
            'controller' => $this->controller,
            'route' => $this->route, // Pass the route for building URLs in the template
        ]);
    }
}
