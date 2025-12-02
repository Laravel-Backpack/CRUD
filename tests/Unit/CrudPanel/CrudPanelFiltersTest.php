<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\app\Library\CrudPanel\CrudFilter;
use Backpack\CRUD\Tests\config\CrudPanel\BaseCrudPanel;
use Backpack\CRUD\Tests\config\Models\User;
use Config;
use Illuminate\Routing\Route;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Filters
 * @covers Backpack\CRUD\app\Library\CrudPanel\CrudFilter
 */
class CrudPanelFiltersTest extends BaseCrudPanel
{
    protected $testFilter = [[
        'name' => 'my_filter',
        'type' => 'simple',
        'label' => 'filter label',
    ], false, false, false];

    protected $testFilter_2 = [[
        'name' => 'my_filter_2',
        'label' => 'filter label 2',
    ], false, false, false];

    public function testItEnablesTheFiltersButConsiderThemDisableIfEmpty()
    {
        $this->crudPanel->enableFilters();
        $this->assertFalse($this->crudPanel->filtersEnabled());
    }

    public function testItCanAddFiltersToCrudPanel()
    {
        $this->crudPanel->addFilter(...$this->testFilter);

        $this->assertCount(1, $this->crudPanel->filters());
    }

    public function testItCanClearFilters()
    {
        $this->crudPanel->addFilter(...$this->testFilter);

        $this->crudPanel->clearFilters();
        $this->assertCount(0, $this->crudPanel->filters());
    }

    public function testItCanCheckIfFilterIsActiveFromRequest()
    {
        $this->crudPanel->setModel(User::class);
        $request = request()->create('/admin/users', 'GET', ['my_custom_filter' => 'foo']);
        $request->setRouteResolver(function () use ($request) {
            return (new Route('GET', 'admin/users', ['UserCrudController', 'index']))->bind($request);
        });
        $this->app->instance('request', $request);

        $isActive = CrudFilter::name('my_custom_filter')->isActive();
        $this->assertTrue($isActive);
    }

    public function testItCanCreateAFilterFluently()
    {
        CrudFilter::name('my_filter')
                    ->type('simple')
                    ->label('custom_label')
                    ->options(['test' => 'test'])
                    ->view('simple')
                    ->viewNamespace('crud::filters')
                    ->ifActive(function () {
                        return true;
                    })
                    ->ifInactive(function () {
                        return true;
                    });

        $this->assertCount(1, $this->crudPanel->filters());
        $filter = $this->crudPanel->filters()[0];
        $this->assertTrue(is_callable($filter->fallbackLogic));
        $this->assertTrue(is_callable($filter->logic));
        $this->assertEquals(['test' => 'test'], $filter->values);
        $this->assertEquals('custom_label', $filter->label);
        $this->assertEquals('simple', $filter->type);
        $this->assertEquals('simple', $filter->view);
    }

    public function testWhenActiveAndWhenInactiveAliases()
    {
        $filter = CrudFilter::name('my_filter')
                        ->whenActive(function () {
                            return true;
                        })
                        ->whenInactive(function () {
                            return true;
                        });

        $this->assertCount(1, $this->crudPanel->filters());
        $this->assertTrue(is_callable($filter->fallbackLogic));
        $this->assertTrue(is_callable($filter->logic));
    }

    public function testWhenNotActiveAlias()
    {
        $filter = CrudFilter::name('my_filter')->whenNotActive(function () {
            return true;
        });
        $this->assertCount(1, $this->crudPanel->filters());
        $this->assertTrue(is_callable($filter->fallbackLogic));
    }

    public function testIfNotActiveAlias()
    {
        $filter = CrudFilter::name('my_filter')->ifNotActive(function () {
            return true;
        });
        $this->assertCount(1, $this->crudPanel->filters());
        $this->assertTrue(is_callable($filter->fallbackLogic));
    }

    public function testElseAlias()
    {
        $filter = CrudFilter::name('my_filter')->else(function () {
            return true;
        });
        $this->assertCount(1, $this->crudPanel->filters());
        $this->assertTrue(is_callable($filter->fallbackLogic));
    }

    public function testItCanAddAFilterBeforeOtherFilter()
    {
        $this->crudPanel->addFilter(...$this->testFilter);
        CrudFilter::name('test_filter_2')->label('just_an_hack_before_fix_gets_merged')->before('my_filter');
        $filterCollection = $this->crudPanel->filters();
        $this->assertCount(2, $filterCollection);
        $firstFilter = $filterCollection[0];
        $this->assertEquals('test_filter_2', $firstFilter->name);
    }

    public function testItCanAddAFilterAfterOtherFilter()
    {
        $this->crudPanel->addFilter(...$this->testFilter);
        $this->crudPanel->addFilter(...$this->testFilter_2);
        CrudFilter::name('test_filter_2')->label('just_an_hack_before_fix_gets_merged')->after('my_filter');
        $filterCollection = $this->crudPanel->filters();
        $this->assertCount(3, $filterCollection);
        $secondFilter = $filterCollection[1];
        $this->assertEquals('test_filter_2', $secondFilter->name);
    }

    public function testItCanMakeAFilterFirst()
    {
        $this->crudPanel->addFilter(...$this->testFilter);
        CrudFilter::name('test_filter_2')->label('just_an_hack_before_fix_gets_merged')->makeFirst();
        $filterCollection = $this->crudPanel->filters();
        $this->assertCount(2, $filterCollection);
        $firstFilter = $filterCollection[0];
        $this->assertEquals('test_filter_2', $firstFilter->name);
    }

    public function testItCanMakeAFilterLast()
    {
        $this->crudPanel->addFilter(...$this->testFilter);
        $this->crudPanel->addFilter(...$this->testFilter_2);
        CrudFilter::name('my_filter')->makeLast();
        $filterCollection = $this->crudPanel->filters();
        $this->assertCount(2, $filterCollection);
        $this->assertEquals(['my_filter_2', 'my_filter'], $filterCollection->pluck('name')->toArray());
    }

    public function testItCanRemoveAFilter()
    {
        $this->crudPanel->addFilter(...$this->testFilter);
        $this->crudPanel->addFilter(...$this->testFilter_2);
        CrudFilter::name('my_filter')->remove();
        $filterCollection = $this->crudPanel->filters();
        $this->assertCount(1, $filterCollection);
        $this->assertEquals(['my_filter_2'], $filterCollection->pluck('name')->toArray());
    }

    public function testItCanRemoveAFilterAttribute()
    {
        $this->crudPanel->addFilter(...$this->testFilter);
        $this->crudPanel->addFilter(...$this->testFilter_2);
        CrudFilter::name('my_filter')->forget('type');
        $filterCollection = $this->crudPanel->filters();
        $this->assertCount(2, $filterCollection);
        $this->assertFalse($filterCollection[0]->type);
    }

    public function testItCanGetTheViewWithNamespace()
    {
        $this->crudPanel->addFilter(...$this->testFilter);
        $namespacedFilterView = CrudFilter::name('my_filter')->getViewWithNamespace();
        $filterCollection = $this->crudPanel->filters();
        $this->assertCount(1, $filterCollection);
        $this->assertEquals($filterCollection[0]->viewNamespace.'.'.$filterCollection[0]->view, $namespacedFilterView);
    }

    public function testItCanGetAllFilterViewNamespacesWithFallbacks()
    {
        Config::set('backpack.crud.view_namespaces', ['filters' => ['pro::filters']]);
        $this->crudPanel->addFilter(...$this->testFilter);
        $filterNamespaceWithFallback = CrudFilter::name('my_filter')->getNamespacedViewWithFallbacks();
        $this->assertEquals(['pro::filters.simple'], $filterNamespaceWithFallback);
    }

    public function testItCanCheckIfFilterWasApplied()
    {
        $this->crudPanel->addFilter(...$this->testFilter);
        $filter = CrudFilter::name('my_filter');
        $this->assertTrue($filter->wasApplied());
        $this->assertFalse($filter->wasNotApplied());
    }
}
