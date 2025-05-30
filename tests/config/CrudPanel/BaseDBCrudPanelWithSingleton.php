<?php

namespace Backpack\CRUD\Tests\config\CrudPanel;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;

abstract class BaseDBCrudPanelWithSingleton extends BaseDBCrudPanel
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->crudPanel = app('crud');
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->scoped('crud', function () {
            return new CrudPanel();
        });
    }
}
