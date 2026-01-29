<?php

namespace Backpack\CRUD\app\Library\CrudPanel;

use Illuminate\Support\Facades\App;
use ReflectionClass;

final class CrudRouter
{
    public static function setupControllerRoutes(string $name, string $routeName, string $controller, string $groupNamespace = ''): void
    {
        $namespacedController = App::getAlias(class_exists($controller) ? $controller : $groupNamespace.$controller);

        $controllerReflection = new ReflectionClass($namespacedController);
        $setupRoutesMethod = $controllerReflection->getMethod('setupRoutes');

        // check if method has #[DeprecatedIgnoreOnRuntime] attribute
        if (empty($setupRoutesMethod->getAttributes(\Backpack\CRUD\app\Library\Attributes\DeprecatedIgnoreOnRuntime::class))) {
            // when the attribute is not found the developer has overwritten the method
            // we will keep the old behavior for backwards compatibility
            $setupRoutesMethod->invoke(App::make($namespacedController), $name, $routeName, $controller);

            return;
        }

        $controllerInstance = $controllerReflection->newInstanceWithoutConstructor();
        foreach ($controllerReflection->getMethods() as $method) {
            if (($method->isPublic() ||
                $method->isProtected()) &&
                $method->getName() !== 'setupRoutes' &&
                str_starts_with($method->getName(), 'setup') &&
                str_ends_with($method->getName(), 'Routes')
            ) {
                $method->setAccessible(true);
                $method->invoke($controllerInstance, $name, $routeName, $controller);
            }
        }
    }
}
