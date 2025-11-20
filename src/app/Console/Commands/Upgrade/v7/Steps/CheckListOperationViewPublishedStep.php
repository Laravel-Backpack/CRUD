<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepStatus;

class CheckListOperationViewPublishedStep extends Step
{
    protected string $relativePath = 'resources/views/vendor/backpack/crud/list.blade.php';

    public function title(): string
    {
        return 'Check if List operation view is published';
    }

    public function run(): StepResult
    {
        if (! $this->context()->fileExists($this->relativePath)) {
            return StepResult::success('List operation view is not published, package default will be used.');
        }

        return StepResult::warning(
            'A published list.blade.php was found. The bundled view received significant updates; delete the published copy to use the latest Backpack version.',
            ["Published view: {$this->relativePath}"]
        );
    }

    public function canFix(StepResult $result): bool
    {
        return $result->status === StepStatus::Warning && $this->context()->fileExists($this->relativePath);
    }

    public function fixMessage(StepResult $result): string
    {
        return 'Delete the published view and use the package default?';
    }

    public function fix(StepResult $result): StepResult
    {
        if (! $this->context()->fileExists($this->relativePath)) {
            return StepResult::skipped('Published list.blade.php was already removed.');
        }

        if (! $this->context()->deleteFile($this->relativePath)) {
            return StepResult::failure("Could not delete {$this->relativePath} automatically.");
        }

        return StepResult::success('Removed the published list.blade.php.');
    }
}
