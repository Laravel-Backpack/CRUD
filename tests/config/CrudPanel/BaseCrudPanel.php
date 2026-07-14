<?php

namespace Backpack\CRUD\Tests\Config\CrudPanel;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\Tests\BaseTestClass;
use Backpack\CRUD\Tests\config\Models\TestModel;

abstract class BaseCrudPanel extends BaseTestClass
{
    /**
     * @var CrudPanel
     */
    protected $crudPanel;

    protected $model;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->crudPanel = \Backpack\CRUD\CrudManager::getCrudPanel(\Backpack\CRUD\app\Http\Controllers\CrudController::class);
        $this->crudPanel->setModel(TestModel::class);
        $this->crudPanel->setRequest();
        $this->model = TestModel::class;

        \Backpack\CRUD\CrudManager::pushActiveController(\Backpack\CRUD\app\Http\Controllers\CrudController::class);
    }

    protected function tearDown(): void
    {
        \Backpack\CRUD\CrudManager::popActiveController();

        parent::tearDown();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('backpack.base.route_prefix', 'admin');

        $app->bind('App\Http\Middleware\CheckIfAdmin', function () {
            return new class
            {
                public function handle($request, $next)
                {
                    return $next($request);
                }
            };
        });
    }
}
