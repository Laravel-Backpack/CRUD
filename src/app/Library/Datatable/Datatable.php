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
        private bool $updatesUrl = true,
        private ?\Closure $configure = null,
        private ?string $type = null,
        private ?string $name = null
    ) {
        // Set active controller for proper context
        CrudManager::setActiveController($controller);

        $this->crud ??= CrudManager::crudFromController($controller, 'list');

        $this->tableId = $this->generateTableId();

        if ($this->configure) {
            // Apply the configuration
            ($this->configure)($this->crud, null);

            // Store the configuration in cache for Ajax requests
            $this->storeDatatableConfig();
        }

        if (! $this->crud->has('list.datatablesUrl')) {
            $this->crud->set('list.datatablesUrl', $this->crud->getRoute());
        }

        // Reset the active controller
        CrudManager::unsetActiveController($controller);
    }

    private function generateTableId(): string
    {
        $controllerPart = str_replace('\\', '_', $this->controller);
        $typePart = $this->type ?? 'default';
        $namePart = $this->name ?? 'default';
        $uniqueId = md5($controllerPart.'_'.$typePart.'_'.$namePart);

        return 'crudTable_'.$uniqueId;
    }

    /**
     * Store the datatable configuration in the cache for later use in Ajax requests.
     */
    private function storeDatatableConfig()
    {
        if (! $this->configure) {
            return;
        }

        $controllerClass = $this->controller;
        $cruds = CrudManager::getCruds();
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
                'element_type' => $this->type,
                'element_name' => $this->name,
            ], now()->addHours(1));

            $this->crud->set('list.datatable_id', $this->tableId);
        }
    }

    public static function applyCachedConfigurationClosure($crud)
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
            $parentCrud = CrudManager::crudFromController($cachedData['parentController']);
            $parentCrud->initialized = false;
            $parentCrud = CrudManager::crudFromController($cachedData['parentController'], 'show');
            $entry = $cachedData['parent_entry'];
            // Get element type and name from cached data
            $elementType = $cachedData['element_type'];
            $elementName = $cachedData['element_name'];

            if ($elementType === 'widget') {
                $widgets = Widget::collection();

                foreach ($widgets as $widget) {
                    if ($widget['type'] === 'datatable' &&
                        (isset($widget['name']) && $widget['name'] === $elementName) &&
                        (isset($widget['configure']) && $widget['configure'] instanceof \Closure)) {
                        $widget['configure']($crud, $entry);
                        return true;
                    }
                }

                return false;
            }
        } catch (\Exception $e) {
            \Log::error('Error applying cached datatable config: '.$e->getMessage(), [
                'exception' => $e,
            ]);
        }
        return false;
    }

    public function render()
    {
        return view('crud::datatable.datatable', [
            'crud' => $this->crud,
            'updatesUrl' => $this->updatesUrl,
            'tableId' => $this->tableId,
        ]);
    }
}
