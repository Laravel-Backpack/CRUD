<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Concerns\InteractsWithCrudControllers;
use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;

class DetectDeprecatedDropzoneUsageStep extends Step
{
    use InteractsWithCrudControllers;

    public function title(): string
    {
        return 'Check for usage of deprecated DropzoneOperation';
    }

    public function run(): StepResult
    {
        $matches = $this->context()->searchTokens(['DropzoneOperation']);
        $controllers = $this->filterCrudControllerPaths($matches['DropzoneOperation'] ?? [], function ($contents) {
            return str_contains($contents, 'DropzoneOperation');
        });

        if (empty($controllers)) {
            return StepResult::success('No usage of DropzoneOperation found.');
        }

        return StepResult::failure(
            'Found usage of deprecated DropzoneOperation in CrudControllers.',
            $controllers
        );
    }

    public function recommendedAction(StepResult $result): string
    {
        return 'We can automatically replace DropzoneOperation with AjaxUploadOperation in your CrudControllers.';
    }

    public function canFix(StepResult $result): bool
    {
        return true;
    }

    public function fix(StepResult $result): StepResult
    {
        $controllers = $result->details;
        $errors = [];

        foreach ($controllers as $file) {
            try {
                $content = $this->context()->readFile($file);

                $content = preg_replace(
                    '/use\s+\\\\?Backpack\\\\Pro\\\\Http\\\\Controllers\\\\Operations\\\\DropzoneOperation/',
                    'use \Backpack\Pro\Http\Controllers\Operations\AjaxUploadOperation',
                    $content
                );

                $content = preg_replace(
                    '/use\s+DropzoneOperation\b/',
                    'use AjaxUploadOperation',
                    $content
                );

                $content = preg_replace(
                    '/dropzoneUpload\s+as\s+traitDropzoneUpload/',
                    'ajaxUpload as traitAjaxUpload',
                    $content
                );

                $content = preg_replace(
                    '/function\s+dropzoneUpload\s*\(/',
                    'function ajaxUpload(',
                    $content
                );

                $content = str_replace('setupDropzoneOperation', 'setupAjaxUploadOperation', $content);

                $content = str_replace('traitDropzoneUpload', 'traitAjaxUpload', $content);

                $content = str_replace('$this->dropzoneUpload', '$this->ajaxUpload', $content);

                $this->context()->writeFile($file, $content);
            } catch (\Throwable $e) {
                $errors[] = "Failed to update {$file}: {$e->getMessage()}";
            }
        }

        if (! empty($errors)) {
            return StepResult::warning('Updated some controllers but failed on others.', $errors);
        }

        return StepResult::success('Updated CrudControllers to use AjaxUploadOperation.');
    }
}
