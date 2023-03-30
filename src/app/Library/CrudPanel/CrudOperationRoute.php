<?php

namespace Backpack\CRUD\app\Library\CrudPanel;

final class CrudOperationRoute
{
    public function __construct(private string $verb = 'GET', private string $route = '/', private array $configuration = [])
    {
    }

    public function getOperation()
    {
        return $this->configuration['operation'];
    }

    public function getVerb()
    {
        return $this->verb;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }
}
