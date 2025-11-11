<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepStatus;

class EnsureMinimumStabilityStep extends Step
{
    private ?string $currentStability = null;

    public function title(): string
    {
        return 'composer.json minimum-stability';
    }

    public function run(): StepResult
    {
        $this->currentStability = $this->context()->composerMinimumStability() ?? 'stable';
        $stability = $this->currentStability;

        if (in_array($stability, ['beta', 'alpha', 'dev'], true)) {
            return StepResult::success("minimum-stability is set to {$stability}.");
        }

        return StepResult::failure(
            'Set minimum-stability to beta (or more permissive) so Composer can install Backpack v7 beta releases.',
            ["Current minimum-stability: {$stability}"]
        );
    }

    public function canFix(StepResult $result): bool
    {
        return $result->status === StepStatus::Failed;
    }

    public function fix(StepResult $result): StepResult
    {
        $updated = $this->context()->updateComposerJson(function (array &$composer) {
            $composer['minimum-stability'] = 'beta';
        });

        if (! $updated) {
            return StepResult::failure('Could not update minimum-stability automatically.');
        }

        return StepResult::success('minimum-stability set to beta in composer.json.');
    }
}
