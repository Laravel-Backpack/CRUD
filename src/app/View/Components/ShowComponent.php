<?php

namespace Backpack\CRUD\app\View\Components;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\CrudManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

abstract class ShowComponent extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public Model $entry,
        public ?string $controller = null,
        public ?string $operation = 'show',
        public ?\Closure $setup = null,
        public ?CrudPanel $crud = null,
        public array|Collection $columns = [],
        public bool $displayButtons = true
    ) {
        $this->setPropertiesFromController();
    }

    /**
     * Set properties from the controller context.
     *
     * This method initializes the CrudPanel and sets the active controller.
     * It also applies any setup closure provided.
     */
    protected function setPropertiesFromController(): void
    {
        // If no CrudController is provided, do nothing
        if (! $this->controller) {
            return;
        }

        // If no CrudPanel is provided, try to get it from the CrudManager
        $this->crud ??= CrudManager::setupCrudPanel($this->controller, $this->operation);

        // Set active controller for proper context
        CrudManager::setActiveController($this->controller);

        // If a setup closure is provided, apply it
        if ($this->setup) {
            if (! empty($columns)) {
                throw new \Exception('You cannot define both setup closure and columns for a '.class_basename(static::class).' component.');
            }

            ($this->setup)($this->crud, $this->entry);
        }

        $this->columns = ! empty($columns) ? $columns : $this->crud?->getOperationSetting('columns', $this->operation) ?? [];

        // Reset the active controller
        CrudManager::unsetActiveController();
    }

    /**
     * Get the view name for the component.
     * This method must be implemented by child classes.
     */
    abstract protected function getViewName(): string;

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        // if no columns are set, don't load any view
        if (empty($this->columns)) {
            return '';
        }

        return view($this->getViewName());
    }
}
