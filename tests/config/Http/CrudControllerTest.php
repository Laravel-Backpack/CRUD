<?php

namespace Backpack\CRUD\Tests\Unit\Http;

use Backpack\CRUD\Tests\BaseTestClass;

/**
 * @covers Backpack\CRUD\app\Http\Controllers\CrudController
 * @covers Backpack\CRUD\app\Library\CrudPanel\CrudPanel
 */
class CrudControllerTest extends BaseTestClass
{
    protected function setUp(): void
    {
        parent::setUp();

        \Backpack\CRUD\CrudManager::getCrudPanel(\Backpack\CRUD\app\Http\Controllers\CrudController::class);
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
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);
    }

    public function testSetRouteName()
    {
        $crudPanel = app('crud');
        $crudPanel->setRouteName('users');

        $this->assertEquals(url('admin/users'), $crudPanel->getRoute());
    }

    public function testSetRoute()
    {
        $crudPanel = app('crud');
        $crudPanel->setRoute(backpack_url('users'));
        $crudPanel->setEntityNameStrings('singular', 'plural');
        $this->assertEquals(config('backpack.base.route_prefix').'/users', $crudPanel->getRoute());
    }

    public function testCrudRequestUpdatesOnEachRequest()
    {
        $controllerClass = \Backpack\CRUD\Tests\config\Http\Controllers\UserCrudController::class;

        // create a first request
        $firstRequest = request()->create('admin/users/1/edit', 'GET');

        app()->handle($firstRequest);
        $firstRequest = app()->request;

        // see if the first global request has been passed to the CRUD object
        $this->assertSame(\Backpack\CRUD\CrudManager::getCrudPanel($controllerClass)->getRequest(), $firstRequest);

        // create a second request
        $secondRequest = request()->create('admin/users/1', 'PUT', ['name' => 'foo']);
        app()->handle($secondRequest);
        $secondRequest = app()->request;

        // see if the second global request has been passed to the CRUD object
        $this->assertSame(\Backpack\CRUD\CrudManager::getCrudPanel($controllerClass)->getRequest(), $secondRequest);

        // the CRUD object's request should no longer hold the first request, but the second one
        $this->assertNotSame(\Backpack\CRUD\CrudManager::getCrudPanel($controllerClass)->getRequest(), $firstRequest);
    }
}
