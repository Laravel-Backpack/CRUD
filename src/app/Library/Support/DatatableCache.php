<?php

namespace Backpack\CRUD\app\Library\Support;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\Widget;
use Backpack\CRUD\CrudManager;

final class DatatableCache extends SetupCache
{
    public function __construct()
    {
        $this->cachePrefix = 'datatable_config_';
        $this->cacheDuration = 60; // 1 hour
    }

    /**
     * Cache setup closure for a datatable component.
     *
     * @param  string  $tableId  The table ID to use as cache key
     * @param  string  $controllerClass  The controller class
     * @param  \Closure|null  $setup  The setup closure
     * @param  string|null  $name  The element name
     * @param  CrudPanel  $crud  The CRUD panel instance to update with datatable_id
     * @return bool Whether the operation was successful
     */
    public function cacheForComponent(string $tableId, string $controllerClass, ?\Closure $setup = null, ?string $name = null, ?CrudPanel $crud = null): bool
    {
        if (! $setup) {
            return false;
        }

        $cruds = CrudManager::getCrudPanels();
        $parentCrud = reset($cruds);

        if ($parentCrud && $parentCrud->getCurrentEntry()) {
            $parentEntry = $parentCrud->getCurrentEntry();
            $parentController = $parentCrud->controller;

            // Store in cache
            $this->store(
                $tableId,
                $controllerClass,
                $parentController,
                $parentEntry,
                $name
            );

            // Set the datatable_id in the CRUD panel if provided
            if ($crud) {
                $crud->set('list.datatable_id', $tableId);
            }

            return true;
        }

        return false;
    }

    public static function applyAndStoreSetupClosure(
        string $tableId,
        string $controllerClass,
        \Closure $setupClosure,
        ?string $name = null,
        ?CrudPanel $crud = null,
        $parentEntry = null
    ): bool {
        $instance = new self();
        // Cache the setup closure for the datatable component
        if ($instance->applySetupClosure($crud, $controllerClass, $setupClosure, $parentEntry)) {
            // Apply the setup closure to the CrudPanel instance
            return $instance->cacheForComponent($tableId, $controllerClass, $setupClosure, $name, $crud);
        }

        return false;
    }

    /**
     * Apply cached setup to a CRUD instance using the request's datatable_id.
     *
     * @param  CrudPanel  $crud  The CRUD panel instance
     * @return bool Whether the operation was successful
     */
    public static function applyFromRequest(CrudPanel $crud): bool
    {
        $instance = new self();
        // Check if the request has a datatable_id parameter
        $tableId = request('datatable_id');

        if (! $tableId) {
            \Log::debug('Missing datatable_id in request parameters');

            return false;
        }

        return $instance->apply($tableId, $crud);
    }

    /**
     * Apply a setup closure to a CrudPanel instance.
     *
     * @param  CrudPanel  $crud  The CRUD panel instance
     * @param  string  $controllerClass  The controller class
     * @param  \Closure  $setupClosure  The setup closure
     * @param  mixed  $entry  The entry to pass to the setup closure
     * @return bool Whether the operation was successful
     */
    public function applySetupClosure(CrudPanel $crud, string $controllerClass, \Closure $setupClosure, $entry = null): bool
    {
        $originalSetup = $setupClosure;
        $modifiedSetup = function ($crud, $entry) use ($originalSetup, $controllerClass) {
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

    /**
     * Prepare datatable data for storage in the cache.
     *
     * @param  string  $controllerClass  The controller class
     * @param  string  $parentController  The parent controller
     * @param  mixed  $parentEntry  The parent entry
     * @param  string|null  $elementName  The element name
     * @return array The data to be cached
     */
    protected function prepareDataForStorage(...$args): array
    {
        [$controllerClass, $parentController, $parentEntry, $elementName] = $args;

        return [
            'controller' => $controllerClass,
            'parentController' => $parentController,
            'parent_entry' => $parentEntry,
            'element_name' => $elementName,
            'operations' => CrudManager::getInitializedOperations($parentController),
        ];
    }

    /**
     * Apply data from the cache to configure a datatable.
     *
     * @param  array  $cachedData  The cached data
     * @param  CrudPanel  $crud  The CRUD panel instance
     * @return bool Whether the operation was successful
     */
    protected function applyFromCache($cachedData, ...$args): bool
    {
        [$crud] = $args;

        try {
            // Initialize operations for the parent controller
            $this->initializeOperations($cachedData['parentController'], $cachedData['operations']);
            $entry = $cachedData['parent_entry'];
            $elementName = $cachedData['element_name'];

            $widgets = Widget::collection();
            $found = false;

            foreach ($widgets as $widget) {
                if ($widget['type'] === 'datatable' &&
                    (isset($widget['name']) && $widget['name'] === $elementName) &&
                    (isset($widget['setup']) && $widget['setup'] instanceof \Closure)) {
                    $this->applySetupClosure($crud, $cachedData['controller'], $widget['setup'], $entry);
                    $found = true;
                    break;
                }
            }

            return $found;
        } catch (\Exception $e) {
            \Log::error('Error applying cached datatable config: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            return false;
        }
    }

    /**
     * Initialize operations for a parent controller.
     */
    private function initializeOperations(string $parentController, $operations): void
    {
        $parentCrud = CrudManager::setupCrudPanel($parentController);

        foreach ($operations as $operation) {
            $parentCrud->initialized = false;
            CrudManager::setupCrudPanel($parentController, $operation);
        }
    }
}
