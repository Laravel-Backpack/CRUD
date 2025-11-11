<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepStatus;

class EnsureFirstPartyAddonsAreCompatibleStep extends Step
{
    protected array $recommendations = [
        'backpack/pro' => '^3.0.0-alpha',
        'backpack/filemanager' => 'dev-next',
        'backpack/theme-coreuiv2' => 'dev-next',
        'backpack/theme-coreuiv4' => 'dev-next',
        'backpack/theme-tabler' => 'dev-next',
        'backpack/logmanager' => 'dev-next',
        'backpack/settings' => 'dev-next',
        'backpack/newscrud' => 'dev-next',
        'backpack/permissionmanager' => 'dev-next',
        'backpack/pagemanager' => 'dev-next',
        'backpack/menucrud' => 'dev-next',
        'backpack/backupmanager' => 'dev-next',
        'backpack/editable-columns' => 'dev-next',
        'backpack/revise-operation' => 'dev-next',
        'backpack/medialibrary-uploaders' => 'dev-next',
        'backpack/devtools' => 'dev-next',
        'backpack/generators' => 'dev-next',
    ];

    private array $mismatched = [];

    public function title(): string
    {
        return 'Ensure Backpack add-ons target v7 compatible releases';
    }

    public function run(): StepResult
    {
        $this->mismatched = [];

        foreach ($this->recommendations as $package => $expectedConstraint) {
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

    protected function extractFirstInteger(string $value): ?int
    {
        if (preg_match('/(\d+)/', $value, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    public function canFix(StepResult $result): bool
    {
        return $result->status === StepStatus::Warning && ! empty($this->mismatched);
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
