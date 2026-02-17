<?php

namespace Backpack\CRUD\app\Library\CrudTesting;

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
     */
    public string $controller;

    /**
     * The CRUD route being tested.
     *
     */
    public string $route;

    /**
     * The model class being tested.
     *
     */
    public string $model;

    /**
    * The current CRUD operation being tested (e.g., 'list', 'create', 'update', 'delete').
    *
    */
    public string $operation = 'list'; 

    /**
     * The parameters to mock the current route with.
     *
     */
    public array $routeParameters = [];

    public TestConfigHelper $testHelper;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Log::info('CrudFeatureTestCase::setUp - ' . static::class);

        $this->testHelper = new TestConfigHelper($this);

        $this->app['crud']->clearFilters();

        // Disable CSRF protection for feature tests as they make direct requests
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }
}
