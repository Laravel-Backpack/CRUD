<?php

namespace Backpack\CRUD;

use Backpack\CRUD\app\Http\Controllers\Contracts\CrudControllerContract;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Support\Facades\Facade;

/**
 * CrudPanelManager - Central registry and factory for CRUD panels.
 *
 * This class manages multiple CrudPanel instances across different controllers.
 * It acts as a singleton registry that:
 * - Creates and stores CrudPanel instances for each controller
 * - Tracks which operations have been initialized for each controller
 * - Manages the currently active controller context
 * - Provides methods to retrieve the appropriate CrudPanel based on context
 *
 * This allows multiple CRUD controllers to coexist and share state properly
 * within a single request lifecycle.
 */
final class CrudPanelManager
{
    /** @var array<string, CrudPanel> Registry of CrudPanel instances indexed by controller class name */
    private array $cruds = [];

    /** @var array<string, array<string>> Tracks which operations have been initialized for each controller */
    private array $initializedOperations = [];

    /** The currently active controller class name */
    private ?string $currentlyActiveCrudController = null;

    /**
     * Get or create a CrudPanel instance for the given controller.
     */
    public function getCrudPanel(CrudControllerContract|string $controller): CrudPanel
    {
        $controllerClass = is_string($controller) ? $controller : get_class($controller);

        if (isset($this->cruds[$controllerClass])) {
            return $this->cruds[$controllerClass];
        }

        $instance = new CrudPanel();

        $this->cruds[$controllerClass] = $instance;

        return $this->cruds[$controllerClass];
    }

    /**
     * Setup and initialize a CrudPanel for the given controller and operation.
     *
     * @param  string  $controller  The controller class name
     * @param  ?string  $operation  The operation to set (defaults to 'list')
     * @return CrudPanel The initialized CrudPanel instance
     */
    public function setupCrudPanel(string $controller, ?string $operation = null): CrudPanel
    {
        // Resolve potential active controller and ensure we have an instance
        $controller = $this->getActiveController() ?? $controller;
        $controller = is_string($controller) ? app($controller) : $controller;

        $crud = $this->getCrudPanel($controller);

        // Use provided operation or default to 'list'
        $operation = $operation ?? 'list';

        $shouldIsolate = $this->shouldIsolateOperation($controller::class, $operation);

        // primary controller request is used when doing a full initialization
        $primaryControllerRequest = $this->cruds[array_key_first($this->cruds)]->getRequest();

        // If the panel is already initialized but a different operation is requested
        // and we don't need to isolate that operation, do a simple setup and return early.
        if ($crud->isInitialized() && $crud->getOperation() !== $operation && ! $shouldIsolate) {
            return $this->performSimpleOperationSwitch($controller, $operation, $crud);
        }

        // If the panel (or this specific operation) hasn't been initialized yet,
        // perform the required initialization (full or operation-specific).
        if (! $crud->isInitialized() || ! $this->isOperationInitialized($controller::class, $operation)) {
            return $this->performInitialization($controller, $operation, $crud, $primaryControllerRequest, $shouldIsolate);
        }

        // Already initialized and operation matches: nothing to do.
        return $this->cruds[$controller::class];
    }

    /**
     * Perform a lightweight operation switch when the panel is initialized and
     * isolation is not required.
     */
    private function performSimpleOperationSwitch($controller, string $operation, CrudPanel $crud): CrudPanel
    {
        self::setActiveController($controller::class);

        $crud->setOperation($operation);
        $this->setupSpecificOperation($controller, $operation, $crud);

        // Mark this operation as initialized
        $this->storeInitializedOperation($controller::class, $operation);

        self::unsetActiveController();

        return $this->cruds[$controller::class];
    }

    /**
     * Perform full or operation-specific initialization when needed.
     */
    private function performInitialization($controller, string $operation, CrudPanel $crud, $primaryControllerRequest, bool $shouldIsolate): CrudPanel
    {
        self::setActiveController($controller::class);

        // If the panel isn't initialized at all, do full initialization
        if (! $crud->isInitialized()) {
            // Set the operation for full initialization
            $crud->setOperation($operation);
            $crud->initialized = false;
            $controller->initializeCrudPanel($primaryControllerRequest, $crud);
        } else {
            // Panel is initialized, just setup this specific operation
            if ($shouldIsolate) {
                $this->setupIsolatedOperation($controller, $operation, $crud);
            } else {
                // Set the operation for standard setup
                $crud->setOperation($operation);
                $this->setupSpecificOperation($controller, $operation, $crud);
            }
        }

        // Mark this operation as initialized
        $this->storeInitializedOperation($controller::class, $operation);

        self::unsetActiveController();

        return $this->cruds[$controller::class];
    }

    /**
     * Determine if an operation should be isolated to prevent state interference.
     *
     * @param  string  $controller
     * @param  string  $operation
     * @return bool
     */
    private function shouldIsolateOperation(string $controller, string $operation): bool
    {
        $currentCrud = $this->cruds[$controller] ?? null;
        if (! $currentCrud) {
            return false;
        }

        $currentOperation = $currentCrud->getOperation();

        // If operations don't differ, no need to isolate
        if (! $currentOperation || $currentOperation === $operation) {
            return false;
        }

        // Check backtrace for components implementing IsolatesOperationSetup
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 10);

        foreach ($backtrace as $trace) {
            if (isset($trace['object'])) {
                $object = $trace['object'];

                // If we find a component that implements the marker interface,
                // it signals that the operation setup should be isolated.
                if ($object instanceof \Backpack\CRUD\app\View\Components\Contracts\IsolatesOperationSetup) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Setup an operation in isolation without affecting the main CRUD panel state.
     * This creates a temporary context for operation setup without state interference.
     *
     * @param  object  $controller  The controller instance
     * @param  string  $operation  The operation to setup
     * @param  CrudPanel  $crud  The CRUD panel instance
     */
    private function setupIsolatedOperation($controller, string $operation, CrudPanel $crud): void
    {
        // Store the complete current state
        $originalOperation = $crud->getOperation();
        $originalSettings = $crud->settings();
        $originalColumns = $crud->columns(); // Use the direct method, not operation setting
        $originalRoute = $crud->route ?? null;
        $originalEntityName = $crud->entity_name ?? null;
        $originalEntityNamePlural = $crud->entity_name_plural ?? null;

        // Store operation-specific settings generically
        $originalOperationSettings = $this->extractOperationSettings($crud, $originalOperation);

        // Temporarily setup the requested operation
        $crud->setOperation($operation);

        // Use the controller's own method to setup the operation properly
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('setupConfigurationForCurrentOperation');
        $method->setAccessible(true);
        $method->invoke($controller, $operation);

        // Completely restore the original state
        $crud->setOperation($originalOperation);

        // CRITICAL: Properly restore columns by clearing and re-adding them
        // This is essential to preserve list operation columns
        $crud->removeAllColumns();
        foreach ($originalColumns as $column) {
            $crud->addColumn($column);
        }

        // Restore all original settings one by one, but skip complex objects
        foreach ($originalSettings as $key => $value) {
            try {
                // Skip complex objects that Laravel generates dynamically
                if (is_object($value) && (
                    $value instanceof \Illuminate\Routing\UrlGenerator ||
                    $value instanceof \Illuminate\Http\Request ||
                    $value instanceof \Illuminate\Contracts\Foundation\Application ||
                    $value instanceof \Closure ||
                    method_exists($value, '__toString') === false
                )) {
                    continue;
                }

                $crud->set($key, $value);
            } catch (\Exception $e) {
                // Silently continue with restoration
            }
        }

        // Restore operation-specific settings generically
        $this->restoreOperationSettings($crud, $originalOperation, $originalOperationSettings);

        // Restore core properties if they were changed
        if ($originalRoute !== null) {
            $crud->route = $originalRoute;
        }
        if ($originalEntityName !== null) {
            $crud->entity_name = $originalEntityName;
        }
        if ($originalEntityNamePlural !== null) {
            $crud->entity_name_plural = $originalEntityNamePlural;
        }
    }

    /**
     * Extract all settings for a specific operation.
     *
     * @param  CrudPanel  $crud  The CRUD panel instance
     * @param  string  $operation  The operation name
     * @return array Array of operation-specific settings
     */
    private function extractOperationSettings(CrudPanel $crud, string $operation): array
    {
        $settings = $crud->settings();
        $operationSettings = [];
        $operationPrefix = $operation.'.';

        foreach ($settings as $key => $value) {
            if (str_starts_with($key, $operationPrefix)) {
                $operationSettings[$key] = $value;
            }
        }

        return $operationSettings;
    }

    /**
     * Restore all settings for a specific operation.
     *
     * @param  CrudPanel  $crud  The CRUD panel instance
     * @param  string  $operation  The operation name
     * @param  array  $operationSettings  The settings to restore
     */
    private function restoreOperationSettings(CrudPanel $crud, string $operation, array $operationSettings): void
    {
        foreach ($operationSettings as $key => $value) {
            try {
                // Skip complex objects that Laravel generates dynamically
                if (is_object($value) && (
                    $value instanceof \Illuminate\Routing\UrlGenerator ||
                    $value instanceof \Illuminate\Http\Request ||
                    $value instanceof \Illuminate\Contracts\Foundation\Application ||
                    $value instanceof \Closure ||
                    method_exists($value, '__toString') === false
                )) {
                    continue;
                }

                $crud->set($key, $value);
            } catch (\Exception $e) {
                // Silently continue with restoration
            }
        }
    }

    /**
     * Setup a specific operation without reinitializing the entire CRUD panel.
     *
     * @param  object  $controller  The controller instance
     * @param  string  $operation  The operation to setup
     * @param  CrudPanel  $crud  The CRUD panel instance
     */
    private function setupSpecificOperation($controller, string $operation, CrudPanel $crud): void
    {
        // Setup the specific operation using the existing CrudController infrastructure
        $crud->setOperation($operation);

        $controller->setup();

        // Use the controller's own method to setup the operation properly
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('setupConfigurationForCurrentOperation');
        $method->setAccessible(true);
        $method->invoke($controller, $operation);
    }

    /**
     * Check if a specific operation has been initialized for a controller.
     */
    public function isOperationInitialized(string $controller, string $operation): bool
    {
        return in_array($operation, $this->getInitializedOperations($controller), true);
    }

    /**
     * Record that an operation has been initialized for a controller.
     *
     * @param  string  $controller  The controller class name
     * @param  string  $operation  The operation name (e.g., 'list', 'create', 'update')
     */
    public function storeInitializedOperation(string $controller, ?string $operation): void
    {
        if (! $operation) {
            return;
        }
        $this->initializedOperations[$controller][] = $operation;
    }

    /**
     * Get the list of operations that have been initialized for a controller.
     *
     * @param  string  $controller  The controller class name
     * @return array<string> Array of initialized operation names
     */
    public function getInitializedOperations(string $controller): array
    {
        return $this->initializedOperations[$controller] ?? [];
    }

    /**
     * Store a CrudPanel instance for a specific controller.
     */
    public function storeCrudPanel(string $controller, CrudPanel $crud): void
    {
        $this->cruds[$controller] = $crud;
    }

    /**
     * Check if a CrudPanel exists for the given controller.
     */
    public function hasCrudPanel(string $controller): bool
    {
        return isset($this->cruds[$controller]);
    }

    /**
     * Get the active CrudPanel for a controller, with fallback logic.
     *
     * @param  string  $controller  The controller class name
     * @return CrudPanel The CrudPanel instance, creating one if necessary
     */
    public function getActiveCrudPanel(string $controller): CrudPanel
    {
        if (! isset($this->cruds[$controller])) {
            return $this->getCrudPanel($this->getActiveController() ?? $this->getParentController() ?? $controller);
        }

        return $this->cruds[$controller];
    }

    /**
     * Get the parent (first registered) controller class name.
     *
     * @return ?string The parent controller class name or null if none exists
     */
    public function getParentController(): ?string
    {
        if (! empty($this->cruds)) {
            return array_key_first($this->cruds);
        }

        return $this->getActiveController();
    }

    /**
     * Set the currently active controller and clear the CRUD facade cache.
     *
     * @param  string  $controller  The controller class name to set as active
     */
    public function setActiveController(string $controller): void
    {
        Facade::clearResolvedInstance('crud');
        $this->currentlyActiveCrudController = $controller;
    }

    /**
     * Get the currently active controller class name.
     *
     * @return ?string The active controller class name or null if none is set
     */
    public function getActiveController(): ?string
    {
        return $this->currentlyActiveCrudController;
    }

    /**
     * Clear the currently active controller.
     */
    public function unsetActiveController(): void
    {
        $this->currentlyActiveCrudController = null;
    }

    /**
     * Intelligently identify and return the appropriate CrudPanel based on context.
     *
     * This method uses multiple strategies to find the correct CrudPanel:
     * 1. Use the currently active controller if set
     * 2. Analyze the call stack to find a CRUD controller in the backtrace
     * 3. Return the first available CrudPanel if any exist
     * 4. Create a default CrudPanel as a last resort
     *
     * @return CrudPanel The identified or created CrudPanel instance
     */
    public function identifyCrudPanel(): CrudPanel
    {
        if ($this->getActiveController()) {
            return $this->getCrudPanel($this->getActiveController());
        }

        // Prioritize explicit controller context
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $controller = null;

        foreach ($trace as $step) {
            if (isset($step['class']) &&
                is_a($step['class'], CrudControllerContract::class, true) &&
                ! is_a($step['class'], CrudController::class, true)) {
                $controller = (string) $step['class'];
                break;
            }
        }

        if ($controller) {
            $crudPanel = $this->getActiveCrudPanel($controller);

            return $crudPanel;
        }

        $cruds = $this->getCrudPanels();

        if (! empty($cruds)) {
            $crudPanel = end($cruds);

            return $crudPanel;
        }

        $this->cruds[CrudController::class] = new CrudPanel();

        return $this->cruds[CrudController::class];
    }

    /**
     * Get all registered CrudPanel instances.
     *
     * @return array<string, CrudPanel> Array of CrudPanel instances indexed by controller class name
     */
    public function getCrudPanels(): array
    {
        return $this->cruds;
    }
}
