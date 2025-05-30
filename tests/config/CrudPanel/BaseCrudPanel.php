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

        $this->crudPanel = app('crud');
        $this->crudPanel->setModel(TestModel::class);
        $this->crudPanel->setRequest();
        $this->model = TestModel::class;
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

        $app->scoped('crud', function () {
            return new CrudPanel();
        });
    }
}
