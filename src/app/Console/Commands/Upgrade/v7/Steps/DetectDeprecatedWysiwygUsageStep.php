<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Concerns\InteractsWithCrudControllers;
use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;

class DetectDeprecatedWysiwygUsageStep extends Step
{
    use InteractsWithCrudControllers;

    private array $paths = [];

    public function title(): string
    {
        return 'Check if any CrudController uses the deprecated wysiwyg alias';
    }

    public function run(): StepResult
    {
        $matches = $this->context()->searchTokens(['wysiwyg']);
        $paths = $this->filterCrudControllerPaths(
            $matches['wysiwyg'] ?? [],
            fn (string $contents): bool => $this->containsWysiwygAlias($contents)
        );
        $this->paths = $paths;

        if (empty($paths)) {
            return StepResult::success('No wysiwyg aliases detected.');
        }

        $details = $this->previewList($paths);

        return StepResult::warning(
            'Wysiwyg found as a field/column type in the following CrudController file(s):',
            $details,
            ['paths' => $paths]
        );
    }

    public function canFix(StepResult $result): bool
    {
        if (! $result->status->isWarning()) {
            return false;
        }

        return ! empty($this->crudControllerPaths());
    }

    public function fixMessage(StepResult $result): string
    {
        return 'We will replace the wysiwyg alias with ckeditor in CrudControllers. Proceed?';
    }

    public function fix(StepResult $result): StepResult
    {
        $paths = $this->crudControllerPaths();

        if (empty($paths)) {
            return StepResult::skipped('No wysiwyg aliases detected to update automatically.');
        }

        $updatedFiles = [];

        foreach ($paths as $path) {
            $contents = $this->context()->readFile($path);

            if ($contents === null) {
                continue;
            }

            $replacementCount = 0;
            $updatedContents = $this->replaceWysiwygAliases($contents, $replacementCount);

            if ($replacementCount === 0) {
                continue;
            }

            if (! $this->context()->writeFile($path, $updatedContents)) {
                return StepResult::failure("Could not update {$path} automatically.");
            }

            $updatedFiles[] = $path;
        }

        if (empty($updatedFiles)) {
            return StepResult::skipped('No wysiwyg aliases could be updated automatically. Manual review required.');
        }

        $details = array_map(fn ($path) => "- {$path}", $updatedFiles);

        return StepResult::success(
            'Replaced wysiwyg aliases with ckeditor in the listed CrudController file(s).',
            $details
        );
    }

    private function crudControllerPaths(): array
    {
        return $this->paths;
    }

    private function replaceWysiwygAliases(string $contents, int &$replacementCount = 0): string
    {
        $patterns = $this->wysiwygPatterns();

        foreach ($patterns as $pattern => $replacement) {
            $contents = preg_replace_callback(
                $pattern,
                function (array $matches) use (&$replacementCount, $replacement) {
                    $replacementCount++;

                    return $replacement($matches);
                },
                $contents
            );
        }

        return $contents;
    }

    private function containsWysiwygAlias(string $contents): bool
    {
        foreach (array_keys($this->wysiwygPatterns()) as $pattern) {
            if (preg_match($pattern, $contents) === 1) {
                return true;
            }
        }

        return false;
    }

    private function wysiwygPatterns(): array
    {
        return [
            '~->type\((["\'])wysiwyg\1\)~' => function (array $matches) {
                return '->type('.$matches[1].'ckeditor'.$matches[1].')';
            },
            '~(["\']type["\']\s*=>\s*)(["\'])wysiwyg\2~' => function (array $matches) {
                return $matches[1].$matches[2].'ckeditor'.$matches[2];
            },
            '~(CRUD::(?:addField|field|addColumn|column)\(\s*)(["\'])wysiwyg\2~' => function (array $matches) {
                return $matches[1].$matches[2].'ckeditor'.$matches[2];
            },
            '~($this->crud->(?:addField|field|addColumn|column)\(\s*)(["\'])wysiwyg\2~' => function (array $matches) {
                return $matches[1].$matches[2].'ckeditor'.$matches[2];
            },
            '~($crud->(?:addField|field|addColumn|column)\(\s*)(["\'])wysiwyg\2~' => function (array $matches) {
                return $matches[1].$matches[2].'ckeditor'.$matches[2];
            },
        ];
    }
}
