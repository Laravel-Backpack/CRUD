<?php

namespace Backpack\CRUD\app\Library\Support;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\CrudManager;
use Illuminate\Support\Facades\Cache;

final class DataformCache extends SetupCache
{
    public function __construct()
    {
        $this->cachePrefix = 'dataform_config_';
        $this->cacheDuration = 60; // 1 hour
    }

    /**
     * Cache setup closure for a dataform component.
     *
     * @param  string  $formId  The form ID to use as cache key
     * @param  string  $controllerClass  The controller class
     * @param  array  $fieldsConfig  The field configuration after setup was applied
     * @param  CrudPanel  $crud  The CRUD panel instance to update with form_id
     * @return bool Whether the operation was successful
     */
    public function cacheForComponent(string $formId, string $controllerClass, array $fieldsConfig, ?CrudPanel $crud = null): bool
    {
        if (empty($fieldsConfig)) {
            return false;
        }

        $cruds = CrudManager::getCrudPanels();
        $parentCrud = reset($cruds);

        $parentEntry = null;
        $parentController = null;

        if ($parentCrud && $parentCrud->getCurrentEntry()) {
            $parentEntry = $parentCrud->getCurrentEntry();
            $parentController = $parentCrud->controller;
        }

        $this->store(
            $formId,
            $controllerClass,
            $parentController,
            $parentEntry
        );

        Cache::put($this->cachePrefix.$formId.'_fields', $fieldsConfig, now()->addMinutes($this->cacheDuration));

        // Set the form_id in the CRUD panel if provided
        if ($crud) {
            $crud->set('form.form_id', $formId);
        }

        return true;
    }

    public static function applyAndStoreSetupClosure(
        string $formId,
        string $controllerClass,
        \Closure $setupClosure,
        ?CrudPanel $crud = null,
        $parentEntry = null
    ): bool {
        $instance = new self();
        // Apply the setup closure to the CrudPanel instance
        if ($instance->applyClosure($crud, $controllerClass, $setupClosure, $parentEntry)) {
            // Capture the resulting field configuration after setup
            $fieldsAfterSetup = [];
            foreach ($crud->fields() as $fieldName => $field) {
                $fieldsAfterSetup[$fieldName] = $field;
            }

            // Cache the field configuration (not the closure, since it won't persist across requests)
            $cached = $instance->cacheForComponent($formId, $controllerClass, $fieldsAfterSetup, $crud);

            return $cached;
        }

        return false;
    }

    /**
     * Apply cached setup to a CRUD instance using the request's form_id.
     *
     * @param  CrudPanel  $crud  The CRUD panel instance
     * @return bool Whether the operation was successful
     */
    public static function applySetupClosure(CrudPanel $crud): bool
    {
        $instance = new self();
        // Check if the request has a _form_id parameter
        $formId = request('_form_id');
        if (! $formId) {
            return false;
        }

        return $instance->apply($formId, $crud);
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
    private function applyClosure(CrudPanel $crud, string $controllerClass, \Closure $setupClosure, $entry = null): bool
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
        } catch (\Exception $e) {
            return false;
        } finally {
            // Clean up
            CrudManager::unsetActiveController();
        }
    }

    /**
     * Prepare dataform data for storage in the cache.
     *
     * @param  string  $controllerClass  The controller class
     * @param  string  $parentController  The parent controller
     * @param  mixed  $parentEntry  The parent entry
     * @param  ?string  $elementName  The element name
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
            'operations' => $parentController ? CrudManager::getInitializedOperations($parentController) : [],
        ];
    }

    /**
     * Apply data from the cache to configure a dataform.
     *
     * @param  array  $cachedData  The cached data
     * @param  CrudPanel  $crud  The CRUD panel instance
     * @return bool Whether the operation was successful
     */
    protected function applyFromCache($cachedData, ...$args): bool
    {
        [$crud] = $args;

        try {
            // Initialize operations for the parent controller (if it exists)
            if (! empty($cachedData['parentController'])) {
                $this->initializeOperations($cachedData['parentController'], $cachedData['operations']);
            }
            $entry = $cachedData['parent_entry'] ?? null;

            if ($entry) {
                $crud->entry = $entry;
            }

            $formId = $crud->get('form.form_id') ?? request()->input('_form_id');

            if ($formId) {
                $fieldsConfig = Cache::get($this->cachePrefix.$formId.'_fields');

                if ($fieldsConfig && is_array($fieldsConfig)) {
                    // Clear all existing fields
                    $crud->setOperationSetting('fields', []);

                    // Restore the cached field configuration
                    foreach ($fieldsConfig as $fieldName => $field) {
                        $crud->addField($field);
                    }

                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
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
