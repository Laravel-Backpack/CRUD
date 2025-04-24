<?php

namespace Backpack\CRUD;

use Backpack\CRUD\app\Http\Controllers\Contracts\CrudControllerContract;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Support\Facades\Facade;

final class BackpackManager
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

    public function crudFromController(string $controller): CrudPanel
    {
        $controller = new $controller();

        $crud = $this->crud($controller);

        $crud->setOperation('list');

        $primaryControllerRequest = $this->cruds[array_key_first($this->cruds)]->getRequest();
        if(! $crud->isInitialized()) {
            $controller->initializeCrud($primaryControllerRequest, $crud, 'list');
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

    public function getCruds(): array
    {
        return $this->cruds;
    }
}
