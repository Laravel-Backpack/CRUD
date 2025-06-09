<?php

namespace Backpack\CRUD\app\View\Components;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\Support\DatatableCache;
use Backpack\CRUD\CrudManager;
use Illuminate\View\Component;

class Datatable extends Component
{
    protected string $tableId;

    public function __construct(
        private string $controller,
        private ?CrudPanel $crud = null,
        private bool $modifiesUrl = false,
        private ?\Closure $setup = null,
        private ?string $name = null,
    ) {
        // Set active controller for proper context
        CrudManager::setActiveController($controller);

        $this->crud ??= CrudManager::setupCrudPanel($controller, 'list');

        $this->tableId = $this->generateTableId();

        if ($this->setup) {
            // Apply the configuration using DatatableCache
            DatatableCache::applyAndStoreSetupClosure(
                $this->tableId,
                $this->controller,
                $this->setup,
                $this->name,
                $this->crud,
                $this->getParentCrudEntry()
            );
        }

        if (! $this->crud->has('list.datatablesUrl')) {
            $this->crud->set('list.datatablesUrl', $this->crud->getRoute());
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

    private function generateTableId(): string
    {
        $controllerPart = str_replace('\\', '_', $this->controller);
        $namePart = $this->name ?? 'default';
        $uniqueId = md5($controllerPart.'_'.$namePart);

        return 'crudTable_'.$uniqueId;
    }

    public function render()
    {
        return view('crud::components.datatable.datatable', [
            'crud' => $this->crud,
            'modifiesUrl' => $this->modifiesUrl,
            'tableId' => $this->tableId,
        ]);
    }
}
