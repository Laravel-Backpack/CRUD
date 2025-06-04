<?php

namespace Backpack\CRUD\app\Library\Datatable;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\Widget;
use Backpack\CRUD\CrudManager;
use Illuminate\Support\Facades\Cache;
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
           // Apply the configuration using the shared method
            $this->applySetupClosure($this->crud, $this->controller, $this->setup, $this->getParentCrudEntry());
            $this->cacheSetupClosure();
        }

        if (! $this->crud->has('list.datatablesUrl')) {
            $this->crud->set('list.datatablesUrl', $this->crud->getRoute());
        }

        // Reset the active controller
        CrudManager::unsetActiveController();
    }

    private function applySetupClosure(CrudPanel $crud, string $controllerClass, \Closure $setupClosure, $entry = null)
    {
        $originalSetup = $setupClosure;
        $modifiedSetup = function($crud, $entry) use ($originalSetup, $controllerClass) {
          
            CrudManager::setActiveController($controllerClass);            
            // Run the original closure
            return ($originalSetup)($crud, $entry);
        };
        
        try {
            // Execute the modified closure
            ($modifiedSetup)($crud, $entry);
            return true;
        } finally {
            // Clean up
            CrudManager::unsetActiveController();
        }
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

    /**
     * Store the datatable configuration in the cache for later use in Ajax requests.
     */
    private function cacheSetupClosure()
    {
        if (! $this->setup) {
            return;
        }

        $controllerClass = $this->controller;
        $cruds = CrudManager::getCrudPanels();
        $parentCrud = reset($cruds);

        if ($parentCrud && $parentCrud->getCurrentEntry()) {
            $parentEntry = $parentCrud->getCurrentEntry();
            $parentController = $parentCrud->controller;
            $cacheKey = 'datatable_config_'.$this->tableId;

            Cache::forget($cacheKey);

            // Store the controller class, parent entry, element type and name
            Cache::put($cacheKey, [
                'controller' => $controllerClass,
                'parentController' => $parentController,
                'parent_entry' => $parentEntry,
                'element_name' => $this->name,
                'operations' => CrudManager::getInitializedOperations($parentController),
            ], now()->addHours(1));

            $this->crud->set('list.datatable_id', $this->tableId);
        }
    }

    public static function applyCachedSetupClosure($crud)
    {
        $tableId = request('datatable_id');

        if (! $tableId) {
            \Log::debug('Missing datatable_id in request parameters');

            return false;
        }

        $cacheKey = 'datatable_config_'.$tableId;
        $cachedData = Cache::get($cacheKey);

        if (! $cachedData) {
            return false;
        }

        try {
            // Get the parent crud instance
            self::initializeOperations($cachedData['parentController'], $cachedData['operations']);
            $entry = $cachedData['parent_entry'];
            $elementName = $cachedData['element_name'];
        
        $widgets = Widget::collection();

        foreach ($widgets as $widget) {
            if ($widget['type'] === 'datatable' &&
                (isset($widget['name']) && $widget['name'] === $elementName) &&
                (isset($widget['setup']) && $widget['setup'] instanceof \Closure)) {
                $instance = new self($cachedData['controller']);

                $instance->applySetupClosure($crud, $cachedData['controller'], $widget['setup'], $entry);
            }
        }

            return false;
        } catch (\Exception $e) {
            \Log::error('Error applying cached datatable config: '.$e->getMessage(), [
                'exception' => $e,
            ]);
        }

        return false;
    }

    private static function initializeOperations(string $parentController, $operations)
    {
        $parentCrud = CrudManager::setupCrudPanel($parentController);

        foreach ($operations as $operation) {
            $parentCrud->initialized = false;
            CrudManager::setupCrudPanel($parentController, $operation);
        }
    }

    public function render()
    {
        return view('crud::datatable.datatable', [
            'crud' => $this->crud,
            'modifiesUrl' => $this->modifiesUrl,
            'tableId' => $this->tableId,
        ]);
    }
}
