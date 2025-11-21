<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;

class EnsureLaravelVersionStep extends Step
{
    public function title(): string
    {
        return 'Check if Laravel version 12 or higher is installed';
    }

    public function run(): StepResult
    {
        $prettyVersion = $this->context()->installedPackagePrettyVersion('laravel/framework') ?? app()->version();
        $major = $this->context()->packageMajorVersion('laravel/framework');

        if ($major === null && preg_match('/(\d+)/', $prettyVersion, $matches)) {
            $major = (int) $matches[1];
        }

        if ($major !== null && $major >= 12) {
            return StepResult::success("Detected Laravel {$prettyVersion}.");
        }

        return StepResult::failure(
            'Upgrade to Laravel 12 before running the Backpack v7 upgrade.',
            [
                "Detected Laravel version: {$prettyVersion}",
                'Follow the official upgrade guide: https://laravel.com/docs/12.x/upgrade',
                'After upgrading to Laravel 12, test everything is working in your app and admin panel.',
            ]
        );
    }

    public function isBlocking(): bool
    {
        return true;
    }
}
