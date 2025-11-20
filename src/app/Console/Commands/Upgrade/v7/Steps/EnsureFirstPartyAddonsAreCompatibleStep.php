<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepStatus;

class EnsureFirstPartyAddonsAreCompatibleStep extends Step
{
    private array $mismatched = [];

    public function title(): string
    {
        return 'Check if Backpack add-ons versions are compatible with v7';
    }

    public function run(): StepResult
    {
        $this->mismatched = [];

        foreach ($this->packagesToCheck() as $package => $expectedConstraint) {
            $constraint = $this->context()->composerRequirement($package);

            if ($constraint === null) {
                continue;
            }

            if (! $this->matchesExpectedConstraint($constraint, $expectedConstraint)) {
                $this->mismatched[] = [
                    'package' => $package,
                    'current' => $constraint,
                    'expected' => $expectedConstraint,
                    'section' => $this->context()->composerRequirementSection($package) ?? 'require',
                ];
            }
        }

        if (empty($this->mismatched)) {
            return StepResult::success('Detected Backpack add-ons already targeting v7 compatible releases.');
        }

        return StepResult::warning(
            'Update the following Backpack add-ons to their v7 compatible versions.',
            array_map(fn ($item) => sprintf('%s (current: %s, expected: %s)', $item['package'], $item['current'], $item['expected']), $this->mismatched)
        );
    }

    protected function packagesToCheck(): array
    {
        return $this->context()->addons();
    }

    protected function matchesExpectedConstraint(string $constraint, string $expected): bool
    {
        if ($expected === 'dev-next') {
            return str_contains($constraint, 'dev-next');
        }

        if (str_starts_with($expected, '^')) {
            $expectedMajor = $this->extractFirstInteger($expected);
            $constraintMajor = $this->extractFirstInteger($constraint);

            return $constraintMajor !== null && $expectedMajor !== null && $constraintMajor >= $expectedMajor;
        }

        return trim($constraint) === trim($expected);
    }

    public function canFix(StepResult $result): bool
    {
        return $result->status === StepStatus::Warning && ! empty($this->mismatched);
    }

    public function fixMessage(StepResult $result): string
    {
        return 'We can update composer.json to target the recommended Backpack add-on versions for v7 automatically. Apply these changes?';
    }

    public function fix(StepResult $result): StepResult
    {
        if (empty($this->mismatched)) {
            return StepResult::skipped('No add-on constraints require updates.');
        }

        $mismatched = $this->mismatched;

        $updated = $this->context()->updateComposerJson(function (array &$composer) use ($mismatched) {
            foreach ($mismatched as $item) {
                $section = $item['section'] ?? 'require';
                $composer[$section] = $composer[$section] ?? [];
                $composer[$section][$item['package']] = $item['expected'];
            }
        });

        if (! $updated) {
            return StepResult::failure('Could not update composer.json automatically.');
        }

        return StepResult::success('Updated composer.json constraints for Backpack add-ons.');
    }
}
