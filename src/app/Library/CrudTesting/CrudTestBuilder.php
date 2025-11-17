<?php

namespace Backpack\CRUD\app\Library\CrudTesting;

use Backpack\CRUD\app\Library\CrudTesting\OperationStrategies\OperationStrategyFactory;
use Backpack\CRUD\app\Library\CrudTesting\OperationStrategies\OperationStrategyInterface;

/**
 * Builds test configurations for CRUD operations based on discovered controllers.
 */
class CrudTestBuilder
{
    protected array $controllerInfo;
    protected string $operation;
    protected object $crudPanel;
    protected OperationStrategyInterface $strategy;

    public function __construct(array $controllerInfo, string $operation = 'list')
    {
        $this->controllerInfo = $controllerInfo;
        $this->operation = $operation;
        $this->crudPanel = CrudControllerDiscovery::buildCrudPanel(
            $controllerInfo['class'],
            $operation
        );
        $this->strategy = OperationStrategyFactory::make(
            $operation,
            $this->crudPanel,
            $controllerInfo
        );
    }

    /**
     * Get test configuration for the current operation.
     *
     * @return array
     */
    public function getTestConfiguration(): array
    {
        $baseConfig = [
            'controller' => $this->controllerInfo['class'],
            'operation' => $this->operation,
            'route' => $this->crudPanel->route,
            'entity_name' => $this->crudPanel->entity_name,
            'entity_name_plural' => $this->crudPanel->entity_name_plural,
            'model' => $this->crudPanel->model,
        ];

        return array_merge($baseConfig, $this->strategy->getOperationConfiguration());
    }

    /**
     * Generate test methods for this operation.
     *
     * @return array
     */
    public function generateTestMethods(): array
    {
        return $this->strategy->generateTestMethods();
    }
}

