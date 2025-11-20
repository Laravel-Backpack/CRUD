<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\Concerns;

use Backpack\CRUD\app\Console\Commands\Upgrade\UpgradeContext;

/**
 * @mixin \Backpack\CRUD\app\Console\Commands\Upgrade\Step
 */
trait InteractsWithCrudControllers
{
    /**
     * Filter a list of file paths.
     */
    protected function filterCrudControllerPaths(array $paths, ?callable $contentsValidator = null): array
    {
        if (empty($paths)) {
            return [];
        }

        $filtered = [];

        foreach ($paths as $path) {
            if (! $this->isCrudControllerPath($path)) {
                continue;
            }

            $contents = $this->context()->readFile($path);

            if ($contents === null) {
                continue;
            }

            if ($contentsValidator !== null && $contentsValidator($contents, $path) !== true) {
                continue;
            }

            $filtered[] = $path;
        }

        return $filtered;
    }

    protected function isCrudControllerPath(string $path): bool
    {
        return str_contains($path, 'CrudController');
    }

    abstract protected function context(): UpgradeContext;
}
