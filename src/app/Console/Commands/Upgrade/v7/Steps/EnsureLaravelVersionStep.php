<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;

class EnsureLaravelVersionStep extends Step
{
    public function title(): string
    {
        return 'Laravel 12 or newer';
    }

    public function run(): StepResult
    {
        $prettyVersion = $this->context()->installedPackagePrettyVersion('laravel/framework') ?? app()->version();
        $major = $this->context()->packageMajorVersion('laravel/framework');

        if ($major !== null && $major >= 12) {
            return StepResult::success("Detected Laravel {$prettyVersion}.");
        }

        return StepResult::failure(
            'Upgrade Laravel to version 12 before continuing.',
            ["Detected Laravel version: {$prettyVersion}"]
        );
    }
}
