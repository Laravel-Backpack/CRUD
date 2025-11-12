<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepStatus;

class CheckShowOperationViewPublishedStep extends Step
{
    protected string $relativePath = 'resources/views/vendor/backpack/crud/show.blade.php';

    public function title(): string
    {
        return 'Published show operation view';
    }

    public function run(): StepResult
    {
        if (! $this->context()->fileExists($this->relativePath)) {
            return StepResult::success('Show operation view is not published, package default will be used.');
        }

        return StepResult::warning(
            'A published show.blade.php was found. Delete it to use the updated Backpack view.',
            ["Published view: {$this->relativePath}"]
        );
    }

    public function canFix(StepResult $result): bool
    {
        return $result->status === StepStatus::Warning && $this->context()->fileExists($this->relativePath);
    }

    public function fixMessage(StepResult $result): string
    {
        return 'We can delete the published show.blade.php so Backpack uses the bundled view. Proceed?';
    }

    public function fix(StepResult $result): StepResult
    {
        if (! $this->context()->fileExists($this->relativePath)) {
            return StepResult::skipped('Published show.blade.php was already removed.');
        }

        if (! $this->context()->deleteFile($this->relativePath)) {
            return StepResult::failure("Could not delete {$this->relativePath} automatically.");
        }

        return StepResult::success('Removed the published show.blade.php so the package view is used.');
    }
}
