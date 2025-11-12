<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\Concerns;

use Backpack\CRUD\app\Console\Commands\Upgrade\UpgradeContext;

/**
 * @mixin \Backpack\CRUD\app\Console\Commands\Upgrade\Step
 */
trait InteractsWithCrudControllers
{
    /**
     * Filter a list of file paths to those that look like CrudControllers and optionally validate contents.
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

    /**
     * Build a short list of preview lines for the provided paths.
     */
    protected function previewLines(array $paths, int $limit = 10, ?callable $formatter = null): array
    {
        if (empty($paths)) {
            return [];
        }

        $formatter ??= static fn (string $path): string => "- {$path}";

        $preview = array_slice($paths, 0, $limit);
        $details = array_map($formatter, $preview);

        $remaining = count($paths) - count($preview);

        if ($remaining > 0) {
            $details[] = sprintf('… %d more occurrence(s) omitted.', $remaining);
        }

        return $details;
    }

    protected function isCrudControllerPath(string $path): bool
    {
        return str_contains($path, 'CrudController');
    }

    abstract protected function context(): UpgradeContext;
}
