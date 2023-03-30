<?php

namespace Backpack\CRUD\app\Library\CrudPanel;

final class OperationRepository
{
    private $operations = [];

    public function add($controller, $operations)
    {
        $this->operations[$controller] = array_merge($this->getControllerOperations($controller), array_unique($operations));
    }

    public function getControllerOperations($controller)
    {
        return $this->operations[$controller] ?? [];
    }
}
