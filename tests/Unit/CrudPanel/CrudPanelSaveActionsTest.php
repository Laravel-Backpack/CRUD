<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\SaveActions
 */
class CrudPanelSaveActionsTest extends \Backpack\CRUD\Tests\config\CrudPanel\BaseCrudPanel
{
    private $singleSaveAction;

    private $multipleSaveActions;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->crudPanel->setOperation('create');

        $this->singleSaveAction = [
            'name' => 'save_action_one',
            'button_text' => 'custom',
            'redirect' => function ($crud, $request, $itemId) {
                return $crud->route;
            },
            'visible' => function ($crud) {
                return true;
            },
        ];

        $this->multipleSaveActions = [
            [
                'name' => 'save_action_one',
                'redirect' => function ($crud, $request, $itemId) {
                    return $crud->route;
                },
                'visible' => function ($crud) {
                    return true;
                },
            ],
            [
                'name' => 'save_action_two',
                'redirect' => function ($crud, $request, $itemId) {
                    return $crud->route;
                },
                'visible' => function ($crud) {
                    return true;
                },
            ],
        ];
    }

    public function testAddDefaultSaveActions()
    {
        $this->crudPanel->setupDefaultSaveActions();
        $this->assertEquals(3, count($this->crudPanel->getOperationSetting('save_actions')));
    }

    public function testAddOneSaveAction()
    {
        $this->crudPanel->setupDefaultSaveActions();
        $this->crudPanel->addSaveAction($this->singleSaveAction);

        $this->assertEquals(4, count($this->crudPanel->getOperationSetting('save_actions')));
        $this->assertEquals(['save_and_back', 'save_and_edit', 'save_and_new', 'save_action_one'], array_keys($this->crudPanel->getOperationSetting('save_actions')));
    }

    public function testAddMultipleSaveActions()
    {
        $this->crudPanel->setupDefaultSaveActions();
        $this->crudPanel->addSaveActions($this->multipleSaveActions);

        $this->assertEquals(5, count($this->crudPanel->getOperationSetting('save_actions')));
        $this->assertEquals(['save_and_back', 'save_and_edit', 'save_and_new', 'save_action_one', 'save_action_two'], array_keys($this->crudPanel->getOperationSetting('save_actions')));
    }

    public function testRemoveOneSaveAction()
    {
        $this->crudPanel->setupDefaultSaveActions();
        $this->crudPanel->removeSaveAction('save_and_new');
        $this->assertEquals(2, count($this->crudPanel->getOperationSetting('save_actions')));
        $this->assertEquals(['save_and_back', 'save_and_edit'], array_keys($this->crudPanel->getOperationSetting('save_actions')));
    }

    public function testRemoveMultipleSaveActions()
    {
        $this->crudPanel->setupDefaultSaveActions();
        $this->crudPanel->removeSaveActions(['save_and_new', 'save_and_edit']);
        $this->assertEquals(1, count($this->crudPanel->getOperationSetting('save_actions')));
        $this->assertEquals(['save_and_back'], array_keys($this->crudPanel->getOperationSetting('save_actions')));
    }

    public function testReplaceSaveActionsWithOneSaveAction()
    {
        $this->crudPanel->setupDefaultSaveActions();
        $this->crudPanel->setSaveActions($this->singleSaveAction);
        $this->assertEquals(1, count($this->crudPanel->getOperationSetting('save_actions')));
        $this->assertEquals(['save_action_one'], array_keys($this->crudPanel->getOperationSetting('save_actions')));
    }

    public function testReplaceSaveActionsWithMultipleSaveActions()
    {
        $this->crudPanel->setupDefaultSaveActions();
        $this->crudPanel->replaceSaveActions($this->multipleSaveActions);
        $this->assertEquals(2, count($this->crudPanel->getOperationSetting('save_actions')));
        $this->assertEquals(['save_action_one', 'save_action_two'], array_keys($this->crudPanel->getOperationSetting('save_actions')));
    }

    public function testOrderOneSaveAction()
    {
        $this->crudPanel->setupDefaultSaveActions();
        $this->crudPanel->orderSaveAction('save_and_new', 1);
        $this->assertEquals(1, $this->crudPanel->getOperationSetting('save_actions')['save_and_new']['order']);
        $this->assertEquals('save_and_new', $this->crudPanel->getFallBackSaveAction());
    }

    public function testOrderMultipleSaveActions()
    {
        $this->crudPanel->setupDefaultSaveActions();
        $this->crudPanel->orderSaveActions(['save_and_new', 'save_and_back']);
        $this->assertEquals(1, $this->crudPanel->getOperationSetting('save_actions')['save_and_new']['order']);
        $this->assertEquals(2, $this->crudPanel->getOperationSetting('save_actions')['save_and_back']['order']);
        $this->assertEquals(3, $this->crudPanel->getOperationSetting('save_actions')['save_and_edit']['order']);
        $this->crudPanel->orderSaveActions(['save_and_edit' => 1]);
        $this->assertEquals('save_and_edit', $this->crudPanel->getFallBackSaveAction());
        $this->assertEquals(['save_and_edit', 'save_and_back', 'save_and_new'], array_keys($this->crudPanel->getOrderedSaveActions()));
    }

    public function testItCanGetTheDefaultSaveActionForCurrentOperation()
    {
        $this->crudPanel->setupDefaultSaveActions();
        $saveAction = $this->crudPanel->getSaveActionDefaultForCurrentOperation();
        $this->assertEquals('save_and_back', $saveAction);
    }

    public function testItCanGetTheDefaultSaveActionFromOperationSettings()
    {
        $this->crudPanel->setupDefaultSaveActions();
        $this->assertEquals('save_and_back', $this->crudPanel->getFallBackSaveAction());
        $this->crudPanel->setOperationSetting('defaultSaveAction', 'save_and_new');
        $this->assertEquals('save_and_new', $this->crudPanel->getFallBackSaveAction());
    }

    public function testItCanRemoveAllTheSaveActions()
    {
        $this->crudPanel->setupDefaultSaveActions();
        $this->assertCount(3, $this->crudPanel->getOperationSetting('save_actions'));
        $this->crudPanel->removeAllSaveActions();
        $this->assertCount(0, $this->crudPanel->getOperationSetting('save_actions'));
    }

    public function testItCanHideSaveActions()
    {
        $this->crudPanel->allowAccess(['create', 'update', 'list']);
        $saveAction = $this->singleSaveAction;
        $saveAction['visible'] = false;
        $this->crudPanel->setupDefaultSaveActions();
        $this->crudPanel->addSaveAction($saveAction);
        $this->assertCount(4, $this->crudPanel->getOperationSetting('save_actions'));
        $this->assertCount(3, $this->crudPanel->getVisibleSaveActions());
    }

    public function testItCanGetSaveActionFromSession()
    {
        $this->crudPanel->allowAccess(['create', 'update', 'list']);
        $this->crudPanel->addSaveAction($this->singleSaveAction);
        $this->crudPanel->setupDefaultSaveActions();
        $saveActions = $this->crudPanel->getSaveAction();

        $expected = [
            'active' => [
                'value' => 'save_action_one',
                'label' => 'custom',
            ],
            'options' => [
                'save_and_back' => 'Save and back',
                'save_and_edit' => 'Save and edit this item',
                'save_and_new' => 'Save and new item',
            ],
        ];
        $this->assertEquals($expected, $saveActions);
    }
}
