<?php

namespace Backpack\CRUD\app\Library\CrudTesting;

use Illuminate\Support\Str;

/**
 * Builds test configurations for CRUD operations based on discovered controllers.
 */
class CrudTestBuilder
{
    protected array $controllerInfo;
    protected string $operation;
    protected ?object $crudPanel = null;

    public function __construct(array $controllerInfo, string $operation = 'list')
    {
        $this->controllerInfo = $controllerInfo;
        $this->operation = $operation;

        try {
            $this->crudPanel = CrudControllerDiscovery::buildCrudPanel(
                $controllerInfo['class'],
                $operation
            );
        } catch (\Throwable $e) {
            // CrudPanel initialization failed — we'll fall back to conventions in getTestConfiguration()
        }
    }

    /**
     * Get test configuration for the current operation.
     *
     * @return array
     */
    public function getTestConfiguration(): array
    {
        if ($this->crudPanel) {
            return $this->getTestConfigurationFromCrudPanel();
        }

        return $this->getTestConfigurationFromConventions();
    }

    /**
     * Determine if the test configuration was built using conventions
     * instead of the actual CrudPanel.
     */
    public function usedConventions(): bool
    {
        return $this->crudPanel === null;
    }

    /**
     * Build test configuration from the initialized CrudPanel.
     */
    protected function getTestConfigurationFromCrudPanel(): array
    {
        $config = [
            'controller' => $this->controllerInfo['class'],
            'operation' => $this->operation,
            'route' => $this->crudPanel->route,
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

    /**
     * Build test configuration from naming conventions when CrudPanel initialization fails.
     *
     * For example, MonsterCrudController assumes:
     *  - model: \App\Models\Monster
     *  - route: admin/monster
     */
    protected function getTestConfigurationFromConventions(): array
    {
        $controllerClass = $this->controllerInfo['class'];

        // Derive the entity name: MonsterCrudController -> Monster
        $entityName = Str::replaceLast('CrudController', '', class_basename($controllerClass));

        // Build the conventional model class: \App\Models\Monster
        $model = 'App\\Models\\'.$entityName;

        // Build the conventional route: admin/monster (kebab-case)
        $routePrefix = trim((string) config('backpack.base.route_prefix', 'admin'), '/');
        $routeSegment = Str::kebab($entityName);
        $route = $routePrefix ? $routePrefix.'/'.$routeSegment : $routeSegment;

        return [
            'controller' => $controllerClass,
            'operation' => $this->operation,
            'route' => $route,
            'model' => $model,
        ];
    }
}
