<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\CrudManager;
use Backpack\CRUD\Tests\config\CrudPanel\BaseCrudPanel;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Operations
 */
class CrudPanelOperationsTest extends BaseCrudPanel
{
    public function testItCanSetAndGetTheCurrentOperation()
    {
        $this->crudPanel->setOperation('create');
        $operation = $this->crudPanel->getOperation();
        $this->assertEquals('create', $operation);
    }

    public function testItCanConfigureOperations()
    {
        CrudManager::setActiveController('TestController');

        $this->crudPanel->operation(['create', 'update'], function () {
            $this->crudPanel->addField(['name' => 'test', 'type' => 'text']);
        });
        $this->crudPanel->applyConfigurationFromSettings('create');

        $this->assertEquals(count($this->crudPanel->fields()), 1);

        CrudManager::unsetActiveController();
    }
}
