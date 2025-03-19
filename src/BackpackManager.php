<?php

namespace Backpack\CRUD;

use Backpack\CRUD\app\Http\Controllers\Contracts\CrudControllerContract;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;

final class BackpackManager
{
    private array $cruds;

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

    public function crudFromController(string $controller): CrudPanel
    {
        $controller = new $controller();

        $crud = $this->crud($controller);

        $crud->setOperation('list');

        $primaryControllerRequest = $this->cruds[array_key_first($this->cruds)]->getRequest();

        $controller->initializeCrud($primaryControllerRequest, 'list');

        return $crud;
    }

    public function hasCrudController(string $controller): bool
    {
        return isset($this->cruds[$controller]);
    }

    public function getControllerCrud(string $controller): CrudPanel
    {
        if (! isset($this->cruds[$controller])) {
            return $this->crudFromController($this->requestController ?? $controller);
        }

        return $this->cruds[$controller];
    }
}
