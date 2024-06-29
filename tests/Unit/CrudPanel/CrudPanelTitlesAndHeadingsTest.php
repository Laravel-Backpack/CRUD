<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\config\Models\User;
use Illuminate\Routing\Route;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\HeadingsAndTitles
 * @covers Backpack\CRUD\app\Library\CrudPanel\CrudPanel
 */
class CrudPanelTitlesAndHeadingsTest extends \Backpack\CRUD\Tests\config\CrudPanel\BaseCrudPanel
{
    public function testItCanSetAndGetTheTitleFromTheAction()
    {
        $this->crudPanel->setTitle('test', 'create');
        $this->assertEquals('test', $this->crudPanel->getTitle('create'));
        $this->assertEquals($this->crudPanel->get('create.title'), $this->crudPanel->getTitle('create'));
    }

    public function testItCanSetAndGetTheHeadingFromTheAction()
    {
        $this->crudPanel->setHeading('test', 'create');
        $this->assertEquals('test', $this->crudPanel->getHeading('create'));
        $this->assertEquals($this->crudPanel->get('create.heading'), $this->crudPanel->getHeading('create'));
    }

    public function testItCanSetAndGetTheSubheadingFromTheAction()
    {
        $this->crudPanel->setSubheading('test', 'create');
        $this->assertEquals('test', $this->crudPanel->getSubheading('create'));
        $this->assertEquals($this->crudPanel->get('create.subheading'), $this->crudPanel->getSubheading('create'));
    }

    public function testItCanSetAndGetTheSubheading()
    {
        $this->crudPanel->setModel(User::class);
        $request = request()->create('/admin/users/create', 'POST', ['name' => 'foo']);
        $request->setRouteResolver(function () use ($request) {
            return (new Route('POST', 'admin/users/create', ['UserCrudController', 'create']))->bind($request);
        });
        $this->crudPanel->setRequest($request);

        $this->crudPanel->setOperation('create');
        $this->crudPanel->setSubheading('test');
        $this->assertEquals('test', $this->crudPanel->getSubheading());
        $this->assertEquals($this->crudPanel->get('create.subheading'), $this->crudPanel->getSubheading());
    }

    public function testItCanSetAndGetTheHeading()
    {
        $this->crudPanel->setModel(User::class);
        $request = request()->create('/admin/users/create', 'POST', ['name' => 'foo']);
        $request->setRouteResolver(function () use ($request) {
            return (new Route('POST', 'admin/users/create', ['UserCrudController', 'create']))->bind($request);
        });
        $this->crudPanel->setRequest($request);

        $this->crudPanel->setOperation('create');
        $this->crudPanel->setHeading('test');
        $this->assertEquals('test', $this->crudPanel->getHeading());
        $this->assertEquals($this->crudPanel->get('create.heading'), $this->crudPanel->getHeading());
    }

    public function testItCanSetAndGetTheTitle()
    {
        $this->crudPanel->setModel(User::class);
        $request = request()->create('/admin/users/create', 'POST', ['name' => 'foo']);
        $request->setRouteResolver(function () use ($request) {
            return (new Route('POST', 'admin/users/create', ['UserCrudController', 'create']))->bind($request);
        });
        $this->crudPanel->setRequest($request);

        $this->crudPanel->setOperation('create');
        $this->crudPanel->setTitle('test');

        $this->assertEquals('test', $this->crudPanel->getTitle());
        $this->assertEquals($this->crudPanel->get('create.title'), $this->crudPanel->getTitle());
    }
}
