<?php

namespace Backpack\CRUD\app\Library\CrudTesting;

/**
 * Builds test configurations for CRUD operations based on discovered controllers.
 */
class CrudTestBuilder
{
    protected array $controllerInfo;
    protected string $operation;
    protected object $crudPanel;

    public function __construct(array $controllerInfo, string $operation = 'list')
    {
        $this->controllerInfo = $controllerInfo;
        $this->operation = $operation;
        $this->crudPanel = CrudControllerDiscovery::buildCrudPanel(
            $controllerInfo['class'],
            $operation
        );
    }

    /**
     * Get test configuration for the current operation.
     *
     * @return array
     */
    public function getTestConfiguration(): array
    {
        $config = [
            'controller' => $this->controllerInfo['class'],
            'operation' => $this->operation,
            'route' => $this->crudPanel->route,
            'entity_name' => $this->crudPanel->entity_name,
            'entity_name_plural' => $this->crudPanel->entity_name_plural,
            'model' => is_object($this->crudPanel->model) ? get_class($this->crudPanel->model) : $this->crudPanel->model,
        ];

        if ($this->operation === 'list') {
            $config['filters'] = $this->crudPanel->filters();
            $config['columns'] = $this->crudPanel->columns();
        }

        if ($this->operation === 'create' || $this->operation === 'update') {
            $config['fields'] = $this->crudPanel->fields();
        }

        return $config;
    }
}

