<?php

namespace Backpack\CRUD\Tests;

use Backpack\Basset\BassetServiceProvider;
use Backpack\CRUD\BackpackServiceProvider;
use Backpack\CRUD\Tests\config\TestsServiceProvider;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase;
use Prologue\Alerts\AlertsServiceProvider;

abstract class BaseTestClass extends TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Route::group([
            (array) config('backpack.base.web_middleware', 'web'),
            (array) config('backpack.base.middleware_key', 'admin'),
            'prefix' => config('backpack.base.route_prefix', 'admin'),
        ],
            function () {
                Route::get('articles/{id}/show/{detail}', ['as' => 'article.show.detail', 'action' => 'Backpack\CRUD\Tests\config\Http\Controllers\ArticleCrudController@detail']);
                Route::crud('users', 'Backpack\CRUD\Tests\config\Http\Controllers\UserCrudController');
                Route::crud('articles', 'Backpack\CRUD\Tests\config\Http\Controllers\ArticleCrudController');
            }
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            TestsServiceProvider::class,
            AlertsServiceProvider::class,
            BassetServiceProvider::class,
            BackpackServiceProvider::class,
        ];
    }

    // allow us to run crud panel private/protected methods like `inferFieldTypeFromDbColumnType`
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
