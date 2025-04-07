<?php

namespace Backpack\CRUD\Tests\Unit\Http;

use Backpack\CRUD\Backpack;
use Backpack\CRUD\Tests\BaseTestClass;

/**
 * @covers Backpack\CRUD\app\Http\Controllers\CrudController
 * @covers Backpack\CRUD\app\Library\CrudPanel\CrudPanel
 */
class CrudControllerTest extends BaseTestClass
{
    private $crudPanel;

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        $this->crudPanel = Backpack::getCrudPanelInstance();
        $this->crudPanel->setRequest();
    }

    public function testSetRouteName()
    {
        $this->crudPanel->setRouteName('users');

        $this->assertEquals(url('admin/users'), $this->crudPanel->getRoute());
    }

    public function testSetRoute()
    {
        $this->crudPanel->setRoute(backpack_url('users'));
        $this->crudPanel->setEntityNameStrings('singular', 'plural');
        $this->assertEquals(route('users.index'), $this->crudPanel->getRoute());
    }

    public function testCrudRequestUpdatesOnEachRequest()
    {
        // create a first request
        $firstRequest = request()->create('admin/users/1/edit', 'GET');

        app()->handle($firstRequest);
        $firstRequest = app()->request;

        // see if the first global request has been passed to the CRUD object
        $this->assertSame(app('crud')->getRequest(), $firstRequest);

        // create a second request
        $secondRequest = request()->create('admin/users/1', 'PUT', ['name' => 'foo']);
        app()->handle($secondRequest);
        $secondRequest = app()->request;

        // see if the second global request has been passed to the CRUD object
        $this->assertSame(app('crud')->getRequest(), $secondRequest);

        // the CRUD object's request should no longer hold the first request, but the second one
        $this->assertNotSame(app('crud')->getRequest(), $firstRequest);
    }
}
