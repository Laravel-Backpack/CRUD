<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepStatus;

class EnsureBackpackCrudRequirementStep extends Step
{
    private ?string $currentConstraint = null;

    private bool $missingRequirement = false;

    public function title(): string
    {
        return 'Update composer requirement for backpack/crud';
    }

    public function run(): StepResult
    {
        $this->currentConstraint = null;
        $this->missingRequirement = false;

        $constraint = $this->context()->composerRequirement('backpack/crud');
        $this->currentConstraint = $constraint;

        if ($constraint === null) {
            $this->missingRequirement = true;

            return StepResult::failure('The composer.json file does not declare backpack/crud.');
        }

        $requiredMajor = $this->extractFirstInteger($constraint);

        if ($requiredMajor === null || $requiredMajor < 7) {
            return StepResult::failure(
                'Update composer.json to require backpack/crud:^7.0.0-beta (or newer).',
                ["Current constraint: {$constraint}"]
            );
        }

        $installedMajor = $this->context()->packageMajorVersion('backpack/crud');
        $installedPretty = $this->context()->installedPackagePrettyVersion('backpack/crud');

        if ($installedMajor !== null && $installedMajor < 7) {
            return StepResult::warning(
                'Composer requirement is updated, but Backpack v7 is not installed yet. Run composer update.',
                ["Installed version: {$installedPretty}"]
            );
        }

        return StepResult::success("Composer.json requires backpack/crud {$constraint}.");
    }

    protected function extractFirstInteger(string $value): ?int
    {
        if (preg_match('/(\d+)/', $value, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    public function canFix(StepResult $result): bool
    {
        if ($result->status !== StepStatus::Failed) {
            return false;
        }

        if ($this->missingRequirement) {
            return true;
        }

        if ($this->currentConstraint === null) {
            return false;
        }

        $requiredMajor = $this->extractFirstInteger($this->currentConstraint);

        return $requiredMajor === null || $requiredMajor < 7;
    }

    public function fix(StepResult $result): StepResult
    {
        $targetConstraint = '^7.0.0-beta';

        $section = $this->context()->composerRequirementSection('backpack/crud') ?? 'require';

        $updated = $this->context()->updateComposerJson(function (array &$composer) use ($section, $targetConstraint) {
            $composer[$section] = $composer[$section] ?? [];
            $composer[$section]['backpack/crud'] = $targetConstraint;
        });

        if (! $updated) {
            return StepResult::failure('Could not update composer.json automatically.');
        }

        return StepResult::success("Set backpack/crud requirement to {$targetConstraint} in composer.json.");
    }
}
