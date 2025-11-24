<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use PHPUnit\Framework\Attributes\DataProvider;

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
                return 'https://backpackforlaravel.com';
            },
            'referrer_url' => function ($crud, $request, $itemId) {
                return 'https://backpackforlaravel.com';
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
        $this->setupDefaultSaveActionsOnCrudPanel();
        $saveAction = $this->singleSaveAction;
        $saveAction['visible'] = false;
        $this->crudPanel->addSaveAction($saveAction);
        $this->assertCount(4, $this->crudPanel->getOperationSetting('save_actions'));
        $this->assertCount(3, $this->crudPanel->getVisibleSaveActions());
    }

    public function testItCanGetSaveActionFromSession()
    {
        $this->setupDefaultSaveActionsOnCrudPanel();
        $this->crudPanel->addSaveAction($this->singleSaveAction);

        session()->put('create.saveAction', 'save_action_one');

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

    public function testItGetsTheFirstSaveActionIfTheRequiredActionIsNotASaveAction()
    {
        $this->setupDefaultSaveActionsOnCrudPanel();
        session()->put('create.saveAction', 'not_a_save_action');
        $this->assertEquals('save_and_back', $this->crudPanel->getSaveAction()['active']['value']);
    }

    public function testItCanSetTheSaveActionInSessionFromRequest()
    {
        $this->setupDefaultSaveActionsOnCrudPanel();

        $this->setupUserCreateRequest();

        $this->crudPanel->getRequest()->merge(['_save_action' => 'save_action_one']);

        $this->crudPanel->setSaveAction();

        $this->assertEquals('save_action_one', session()->get('create.saveAction'));
    }

    public function testItCanPerformTheSaveActionAndReturnTheRedirect()
    {
        $this->setupDefaultSaveActionsOnCrudPanel();

        $redirect = $this->crudPanel->performSaveAction();
        $this->assertEquals(url('/'), $redirect->getTargetUrl());
    }

    public function testItCanPerformTheSaveActionAndReturnTheRedirectFromTheRequest()
    {
        $this->setupDefaultSaveActionsOnCrudPanel();

        $this->setupUserCreateRequest();

        $this->crudPanel->addSaveAction($this->singleSaveAction);

        $this->crudPanel->getRequest()->merge(['_save_action' => 'save_action_one']);

        $redirect = $this->crudPanel->performSaveAction();

        $this->assertEquals('https://backpackforlaravel.com', $redirect->getTargetUrl());
    }

    public function testItCanSetGetTheRefeererFromSaveAction()
    {
        $this->setupDefaultSaveActionsOnCrudPanel();

        $this->crudPanel->addSaveAction($this->singleSaveAction);

        $this->crudPanel->getRequest()->merge(['_save_action' => 'save_action_one']);

        $this->crudPanel->performSaveAction();

        $referer = session('referrer_url_override');

        $this->assertEquals('https://backpackforlaravel.com', $referer);
    }

    public function testItCanPerformTheSaveActionAndRespondWithJson()
    {
        $this->setupDefaultSaveActionsOnCrudPanel();

        $this->crudPanel->getRequest()->headers->set('X-Requested-With', 'XMLHttpRequest');

        $this->crudPanel->getRequest()->merge(['_save_action' => 'save_and_back']);

        $response = $this->crudPanel->performSaveAction();

        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        $this->assertEquals([
            'success' => true,
            'redirect_url' => null,
            'referrer_url' => false,
            'data' => null,
        ], json_decode($response->getContent(), true));
    }

    #[DataProvider('saveActionsDataProvider')]
    public function testSaveActionsRedirectAndRefererUrl($action, $redirect, $referrer)
    {
        $this->setupDefaultSaveActionsOnCrudPanel();

        // Add SaveAndPreview for testing since it's not in the defaults
        if ($action === 'save_and_preview') {
            $this->crudPanel->allowAccess('show');
            $this->crudPanel->addSaveAction(new \Backpack\CRUD\app\Library\CrudPanel\SaveActions\SaveAndPreview());
        }

        $this->crudPanel->getRequest()->merge(['_save_action' => $action, 'id' => 1, '_locale' => 'pt', '_current_tab' => 'tab1']);

        $redirectUrl = $this->crudPanel->performSaveAction();

        $this->assertEquals($redirect, $redirectUrl->getTargetUrl());

        $this->assertEquals($referrer, session('referrer_url_override') ?? false);
    }

    public static function saveActionsDataProvider()
    {
        return [
            [
                'action' => 'save_and_back',
                'redirect' => 'http://localhost',
                'referrer' => false,
            ],
            [
                'action' => 'save_and_edit',
                'redirect' => 'http://localhost/1/edit?_locale=pt#tab1',
                'referrer' => 'http://localhost/1/edit',
            ],
            [
                'action' => 'save_and_new',
                'redirect' => 'http://localhost/create',
                'referrer' => false,
            ],
            [
                'action' => 'save_and_preview',
                'redirect' => 'http://localhost/1/show?_locale=pt#tab1',
                'referrer' => 'http://localhost/1/show',
            ],
        ];
    }

    private function setupDefaultSaveActionsOnCrudPanel()
    {
        $this->crudPanel->allowAccess(['create', 'update', 'list']);
        $this->crudPanel->setupDefaultSaveActions();
    }
}
