<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepStatus;
use Composer\Semver\Semver;

class EnsureLaravelVersionStep extends Step
{
    private ?string $composerConstraint = null;

    private ?string $composerSection = null;

    private ?string $suggestedConstraint = null;

    private ?int $detectedMajor = null;

    public function title(): string
    {
        return 'Laravel 12 or newer';
    }

    public function run(): StepResult
    {
        $this->resetState();

        $prettyVersion = $this->context()->installedPackagePrettyVersion('laravel/framework') ?? app()->version();
        $major = $this->context()->packageMajorVersion('laravel/framework');

        if ($major === null && preg_match('/(\d+)/', $prettyVersion, $matches)) {
            $major = (int) $matches[1];
        }

        $this->detectedMajor = $major;
        $this->composerConstraint = $this->context()->composerRequirement('laravel/framework');
        $this->composerSection = $this->context()->composerRequirementSection('laravel/framework');

        if ($major !== null && $major >= 12) {
            return StepResult::success("Detected Laravel {$prettyVersion}.");
        }

        if ($this->shouldOfferConstraintFix()) {
            $details = [
                "Detected Laravel version: {$prettyVersion}",
                sprintf(
                    'composer.json constraint (%s): %s',
                    $this->composerSection,
                    $this->composerConstraint
                ),
                "Suggested constraint update: {$this->suggestedConstraint}",
            ];

            return StepResult::failure(
                'Upgrade Laravel to version 12 before continuing.',
                $details,
                [
                    'suggested_constraint' => $this->suggestedConstraint,
                    'composer_section' => $this->composerSection,
                ]
            );
        }

        return StepResult::failure(
            'Upgrade Laravel to version 12 before continuing.',
            [
                "Laravel 12 is allowed by your composer.json constraints, you just need to run composer update to get it.",
                "Detected Laravel version: {$prettyVersion}"
            ]
        );
    }

    public function canFix(StepResult $result): bool
    {
        return $result->status === StepStatus::Failed && $this->suggestedConstraint !== null && $this->composerSection !== null;
    }

    public function fixMessage(StepResult $result): string
    {
        return 'We can update composer.json to allow installing Laravel 12 automatically. Apply this change?';
    }

    public function fix(StepResult $result): StepResult
    {
        if ($this->suggestedConstraint === null || $this->composerSection === null) {
            return StepResult::skipped('No composer constraint update required.');
        }

        $section = $this->composerSection;
        $constraint = $this->suggestedConstraint;

        $updated = $this->context()->updateComposerJson(function (array &$composer) use ($section, $constraint) {
            $composer[$section] = $composer[$section] ?? [];
            $composer[$section]['laravel/framework'] = $constraint;
        });

        if (! $updated) {
            return StepResult::failure('Could not update composer.json automatically.');
        }

        return StepResult::success('Updated laravel/framework composer constraint to allow Laravel 12.');
    }

    private function resetState(): void
    {
        $this->composerConstraint = null;
        $this->composerSection = null;
        $this->suggestedConstraint = null;
        $this->detectedMajor = null;
    }

    private function shouldOfferConstraintFix(): bool
    {
        if ($this->composerConstraint === null || $this->detectedMajor === null) {
            return false;
        }

        if ($this->detectedMajor < 11) {
            return false;
        }

        if ($this->composerSection === null) {
            return false;
        }

        if (! preg_match('/\d+/', $this->composerConstraint)) {
            return false;
        }

        if ($this->constraintAllowsMajor($this->composerConstraint, 12)) {
            return false;
        }

        $this->suggestedConstraint = $this->buildSuggestedConstraint($this->composerConstraint);

        return $this->suggestedConstraint !== null;
    }

    private function constraintAllowsMajor(string $constraint, int $major): bool
    {
        $constraint = trim($constraint);

        if ($constraint === '') {
            return true;
        }

        try {
            if (Semver::satisfies(sprintf('%d.0.0', $major), $constraint)) {
                return true;
            }

            if (Semver::satisfies(sprintf('%d.999.999', $major), $constraint)) {
                return true;
            }
        } catch (\Throwable $exception) {
            return str_contains($constraint, (string) $major);
        }

        return false;
    }

    private function buildSuggestedConstraint(string $constraint): ?string
    {
        $normalized = trim(preg_replace('/\s+/', ' ', $constraint) ?? '');
        $normalized = preg_replace('/\s*\|\|\s*/', ' || ', $normalized ?? '') ?? '';
        $normalized = preg_replace('/\s*\|\s*/', ' || ', $normalized) ?? '';

        if ($normalized === '') {
            return '^12.0';
        }

        if ($this->constraintAllowsMajor($normalized, 12)) {
            return $normalized;
        }

        if (str_contains($normalized, '12')) {
            return $normalized;
        }

        return rtrim($normalized, ' ') . ' || ^12.0';
    }
}
