<?php

namespace Backpack\CRUD\app\Library\Datatable;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
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

        $this->crud ??= CrudManager::crudFromController($controller);

        $this->tableId = $this->generateTableId();

        if ($this->configure) {
            // Apply the configuration
            ($this->configure)($this->crud);

            // Store the configuration in cache for Ajax requests
            $this->storeDatatableConfig();
        }

        if (! $this->crud->getOperationSetting('datatablesUrl')) {
            $this->crud->setOperationSetting('datatablesUrl', $this->crud->getRoute());
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

            // Store the controller class, parent entry, element type and name
            Cache::put($cacheKey, [
                'controller' => $controllerClass,
                'parentController' => $parentController,
                'parent_entry' => $parentEntry,
                'element_type' => $this->type,
                'element_name' => $this->name,
            ], now()->addHours(1));

            $this->crud->setOperationSetting('datatable_id', $this->tableId);
        }
    }

    public static function applyCachedConfig($crud)
    {
        $tableId = request('datatable_id');

        if (! $tableId) {
            \Log::debug('Missing datatable_id in request parameters');

            return false;
        }

        $cacheKey = 'datatable_config_'.$tableId;
        $cachedData = Cache::get($cacheKey);

        if (! $cachedData) {
            \Log::debug('No cached configuration found for the given datatable_id');

            return false;
        }

        try {
            \Log::debug('Found matching configuration by table ID', [
                'controller' => $cachedData['controller'],
                'element_type' => $cachedData['element_type'],
                'element_name' => $cachedData['element_name'],
                'table_id' => $tableId,
            ]);

            // Get the parent crud instance
            $parentCrud = CrudManager::crudFromController($cachedData['parentController'], 'show');
            $entry = $cachedData['parent_entry'];

            // Get element type and name from cached data
            $elementType = $cachedData['element_type'];
            $elementName = $cachedData['element_name'];

            \Log::debug('Element type and name', [
                'element_type' => $elementType,
                'element_name' => $elementName,
            ]);

            if ($elementType === 'column') {
                $column = $parentCrud->columns()[$elementName] ?? null;
                if ($column && isset($column['configure'])) {
                    self::applyColumnDatatableConfig($parentCrud, $crud, $elementName, $entry);
                    // clear the cache after applying the configuration
                    Cache::forget($cacheKey);

                    return true;
                }
                \Log::debug('Column not found or no configure closure defined');

                return false;
            } elseif ($elementType === 'widget') {
                $widgets = $parentCrud->getOperationSetting('widgets') ?? [];
                foreach ($widgets as $widget) {
                    if ($widget['type'] === 'datatable' &&
                        (isset($widget['name']) && $widget['name'] === $elementName) &&
                        isset($widget['configure'])) {
                        self::applyWidgetDatatableConfig($parentCrud, $crud, $elementName, $entry);
                        // clear the cache after applying the configuration
                        Cache::forget($cacheKey);

                        return true;
                    }
                }
                \Log::debug('Widget not found or no configure closure defined');

                return false;
            }
        } catch (\Exception $e) {
            \Log::error('Error applying cached datatable config: '.$e->getMessage(), [
                'exception' => $e,
            ]);
        }

        \Log::debug('No matching configuration found');

        return false;
    }

    private static function applyColumnDatatableConfig($parentCrud, $crud, $elementName, $entry)
    {
        $column = $parentCrud->columns()[$elementName];
        if (isset($column['configure'])) {
            ($column['configure'])($crud, $entry);

            return true;
        }

        return false;
    }

    private static function applyWidgetDatatableConfig($parentCrud, $crud, $elementName, $entry)
    {
        $widgets = $parentCrud->getOperationSetting('widgets') ?? [];
        foreach ($widgets as $widget) {
            if ($widget['type'] === 'datatable' &&
                (isset($widget['name']) && $widget['name'] === $elementName) &&
                isset($widget['configure'])) {
                ($widget['configure'])($crud, $entry);

                return true;
            }
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
