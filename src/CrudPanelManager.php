<?php

namespace Backpack\CRUD;

use Backpack\CRUD\app\Http\Controllers\Contracts\CrudControllerContract;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Support\Facades\Facade;

final class CrudPanelManager
{
    private array $cruds = [];

    private ?string $currentlyActiveCrudController = null;

    private CrudPanel $crudPanelInstance;

    private $requestController = null;

    public function __construct()
    {
        $this->crudPanelInstance = new CrudPanel();
    }

    public function getCrudPanelInstance(): CrudPanel
    {
        return $this->crudPanelInstance;
    }

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
        $controller = new $controller();

        $crud = $this->crud($controller);

        // Use provided operation or default to 'list'
        $operation = $operation ?? 'list';
        $crud->setOperation($operation);

        $primaryControllerRequest = $this->cruds[array_key_first($this->cruds)]->getRequest();
        if (! $crud->isInitialized()) {
            $controller->initializeCrudController($primaryControllerRequest, $crud);
        }

        return $crud;
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

    public function getCrudPanel(): CrudPanel
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

        return self::getCrudPanelInstance();
    }

    public function getCruds(): array
    {
        return $this->cruds;
    }
}
