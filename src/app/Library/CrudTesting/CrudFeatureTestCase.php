<?php

namespace Backpack\CRUD\app\Library\CrudTesting;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Foundation\Testing\TestCase as IlluminateTestCase;

/**
 * Base test case for CRUD feature tests.
 * Provides common functionality for testing CRUD operations.
 */
abstract class CrudFeatureTestCase extends IlluminateTestCase
{
    /**
     * The controller class being tested.
     *
     * @var string
     */
    protected string $controller;

    /**
     * The CRUD route being tested.
     *
     * @var string
     */
    protected string $route;

    /**
     * The model class being tested.
     *
     * @var string
     */
    protected string $model;

    public string $operation = 'list'; // Default operation, can be overridden in child classes

    public CrudTestConfiguration $testHelper;

    public CrudPanel $crudPanel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testHelper = config('backpack.testing.configurations.'.$this->controller) !== null ?
        new (config('backpack.testing.configurations.'.$this->controller))($this->controller, $this->operation, $this->route, $this->model) :
        new TestConfigHelper($this->controller, $this->operation, $this->route, $this->model);

        $this->app['crud']->clearFilters();

        // Disable CSRF protection for feature tests as they make direct requests
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }
}
