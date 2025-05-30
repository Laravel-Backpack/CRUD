<?php

namespace Backpack\CRUD;

use Backpack\CRUD\app\Http\Controllers\Contracts\CrudControllerContract;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Support\Facades\Facade;

final class CrudPanelManager
{
    private array $cruds = [];

    private ?string $currentlyActiveCrudController = null;

    private $requestController = null;

    public function crud(CrudControllerContract $controller): CrudPanel
    {
        $controllerClass = get_class($controller);

        $this->requestController = $controllerClass;

        if (isset($this->cruds[$controllerClass])) {
            return $this->cruds[$controllerClass];
        }

        $instance = new CrudPanel();

        $this->cruds[$controllerClass] = $instance;

        return $this->cruds[$controllerClass];
    }

    public function crudFromController(string $controller, ?string $operation = null): CrudPanel
    {
        $controller = $this->getActiveController() ?? $controller;

        $controller = is_string($controller) ? app($controller) : $controller;

        $crud = $this->crud($controller);

        // Use provided operation or default to 'list'
        $operation = $operation ?? 'list';
        $crud->setOperation($operation);

        $primaryControllerRequest = $this->cruds[array_key_first($this->cruds)]->getRequest();
        if (! $crud->isInitialized()) {
            self::setActiveController($controller::class);
            $controller->initializeCrudController($primaryControllerRequest, $crud);
            self::unsetActiveController();
            $crud = $this->cruds[$controller::class];
            return $this->cruds[$controller::class];
        }

        return $this->cruds[$controller::class];
    }

    public function setControllerCrud(string $controller, CrudPanel $crud): void
    {
        $this->cruds[$controller] = $crud;
    }

    public function hasCrudController(string $controller): bool
    {
        return isset($this->cruds[$controller]);
    }

    public function getControllerCrud(string $controller): CrudPanel
    {
        if (! isset($this->cruds[$controller])) {
            return $this->crudFromController($this->getActiveController() ?? $this->requestController ?? $controller);
        }

        return $this->cruds[$controller];
    }

    public function getRequestController(): ?string
    {
        return $this->requestController;
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

    public function getCrudPanel()
    {
        if ($this->getActiveController()) {
            return $this->crudFromController($this->getActiveController());
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
            $crudPanel = self::getControllerCrud($controller);

            return $crudPanel;
        }

        $cruds = self::getCruds();

        if (! empty($cruds)) {
            $crudPanel = reset($cruds);

            return $crudPanel;
        }

        $this->cruds[CrudController::class] = new CrudPanel();
        return $this->cruds[CrudController::class];
    }

    public function getCruds(): array
    {
        return $this->cruds;
    }
}
