<?php

namespace Backpack\CRUD;

use Backpack\CRUD\app\Http\Controllers\Contracts\CrudControllerContract;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Support\Facades\Facade;

final class CrudPanelManager
{
    private array $cruds = [];

    private array $initializedOperations = [];

    private ?string $currentlyActiveCrudController = null;

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

    public function setupCrudPanel(string $controller, ?string $operation = null): CrudPanel
    {
        $controller = $this->getActiveController() ?? $controller;

        $controller = is_string($controller) ? app($controller) : $controller;

        $crud = $this->getCrudPanel($controller);

        // Use provided operation or default to 'list'
        $operation = $operation ?? 'list';
        $crud->setOperation($operation);

        $primaryControllerRequest = $this->cruds[array_key_first($this->cruds)]->getRequest();
        if (! $crud->isInitialized()) {
            self::setActiveController($controller::class);
            $controller->initializeCrudPanel($primaryControllerRequest, $crud);
            self::unsetActiveController();
            $crud = $this->cruds[$controller::class];

            return $this->cruds[$controller::class];
        }

        return $this->cruds[$controller::class];
    }

    public function storeInitializedOperation(string $controller, string $operation): void
    {
        $this->initializedOperations[$controller][] = $operation;
    }

    public function getInitializedOperations(string $controller): array
    {
        return $this->initializedOperations[$controller] ?? [];
    }

    public function storeCrudPanel(string $controller, CrudPanel $crud): void
    {
        $this->cruds[$controller] = $crud;
    }

    public function hasCrudPanel(string $controller): bool
    {
        return isset($this->cruds[$controller]);
    }

    public function getActiveCrudPanel(string $controller): CrudPanel
    {
        if (! isset($this->cruds[$controller])) {
            return $this->getCrudPanel($this->getActiveController() ?? $this->getParentController() ?? $controller);
        }

        return $this->cruds[$controller];
    }

    public function getParentController(): ?string
    {
        if (! empty($this->cruds)) {
            return array_key_first($this->cruds);
        }

        return $this->getActiveController();
    }

    public function setActiveController(string $controller): void
    {
        Facade::clearResolvedInstance('crud');
        $this->currentlyActiveCrudController = $controller;
    }

    public function getActiveController(): ?string
    {
        return $this->currentlyActiveCrudController;
    }

    public function unsetActiveController(): void
    {
        $this->currentlyActiveCrudController = null;
    }

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
                is_a($step['class'], CrudControllerContract::class, true)) {
                $controller = (string) $step['class'];
                break;
            }
        }

        if ($controller) {
            $crudPanel = self::getActiveCrudPanel($controller);

            return $crudPanel;
        }

        $cruds = self::getCrudPanels();

        if (! empty($cruds)) {
            $crudPanel = reset($cruds);

            return $crudPanel;
        }

        $this->cruds[CrudController::class] = new CrudPanel();

        return $this->cruds[CrudController::class];
    }

    public function getCrudPanels(): array
    {
        return $this->cruds;
    }
}
