<?php

namespace Backpack\CRUD\app\Library\CrudTesting\Support;

use Illuminate\Routing\Route;

final class MockApplicationRoute
{
    /**
     * Mock the current route with the given parameters.
     *
     * @param  string  $operation
     * @param  ?string  $controller
     * @param  array  $parameters
     * @param  string  $url
     * @param  string  $method
     * @return void
     */
    public static function mockRoute(string $operation, ?string $controller = null, array $parameters = [], string $url = '/', string $method = 'GET'): void
    {
        $action = ['uses' => 'Controller@method', 'operation' => $operation];

        if (empty($parameters) && $controller) {
            $reflectionClass = new \ReflectionClass($controller);
            $attributes = $reflectionClass->getAttributes(\Backpack\CRUD\app\Library\CrudTesting\TestingRouteParameters::class);
            if (! empty($attributes)) {
                $instance = $attributes[0]->newInstance();
                $parameters = $instance->getParameters();
            }
        }

        $route = new Route([$method], $url, $action);

        $route->setContainer(app());
        $route->setRouter(app('router'));

        $request = app('request')->merge(['operation' => $operation, 'id' => 1]);
        $route->bind($request);

        foreach ($parameters as $key => $value) {
            $route->setParameter($key, $value);
        }
        $route->setParameter('id', 1);

        $request->setRouteResolver(fn () => $route);

        $router = app('router');

        try {
            $reflector = new \ReflectionClass($router);
            $property = $reflector->getProperty('current');
            $property->setValue($router, $route);
        } catch (\ReflectionException $e) {
            // do nothing, if we can't set the current route
        }

        if (app()->bound('url')) {
            app('url')->setRequest($request);
        }

        app()->instance('request', $request);
    }
}