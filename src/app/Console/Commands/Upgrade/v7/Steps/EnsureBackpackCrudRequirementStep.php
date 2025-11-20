<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepStatus;
use Backpack\CRUD\app\Console\Commands\Upgrade\v7\UpgradeCommandConfig;

class EnsureBackpackCrudRequirementStep extends Step
{
    private ?string $currentConstraint = null;

    private bool $missingRequirement = false;

    public function title(): string
    {
        return 'Check if Backpack CRUD is installed and the correct version is required';
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
        $targetConstraint = $this->targetConstraint();
        $targetMajor = $this->targetMajor();

        if ($requiredMajor === null) {
            return StepResult::failure(
                sprintf('Update composer.json to require backpack/crud:%s (or newer).', $targetConstraint),
                ["Current constraint: {$constraint}"]
            );
        }

        if ($targetMajor !== null && $requiredMajor < $targetMajor) {
            return StepResult::failure(
                sprintf('Update composer.json to require backpack/crud:%s (or newer).', $targetConstraint),
                ["Current constraint: {$constraint}"]
            );
        }

        $installedMajor = $this->context()->packageMajorVersion('backpack/crud');
        $installedPretty = $this->context()->installedPackagePrettyVersion('backpack/crud');

        $comparisonMajor = $targetMajor ?? $requiredMajor;

        if ($comparisonMajor !== null && $installedMajor !== null && $installedMajor < $comparisonMajor) {
            return StepResult::warning(
                sprintf(
                    'Backpack %s is required in the composer.json, but is not installed yet.',
                    $targetConstraint
                ),
                ["Installed version: {$installedPretty}"]
            );
        }

        return StepResult::success("Composer.json requires backpack/crud {$constraint}.");
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
        $targetMajor = $this->targetMajor();

        if ($targetMajor === null) {
            return $requiredMajor === null;
        }

        return $requiredMajor === null || $requiredMajor < $targetMajor;
    }

    public function fixMessage(StepResult $result): string
    {
        return sprintf(
            'We will update the composer.json to require backpack/crud:%s . Proceed?',
            $this->targetConstraint()
        );
    }

    public function fix(StepResult $result): StepResult
    {
        $targetConstraint = $this->targetConstraint();

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

    private function targetConstraint(): string
    {
        return UpgradeCommandConfig::backpackCrudRequirement();
    }

    private function targetMajor(): ?int
    {
        $constraintMajor = $this->extractFirstInteger($this->targetConstraint());

        if ($constraintMajor !== null) {
            return $constraintMajor;
        }

        return $this->extractFirstInteger($this->context()->targetVersion());
    }
}
