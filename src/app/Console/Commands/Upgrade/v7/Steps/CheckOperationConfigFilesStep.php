<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;
use Backpack\CRUD\app\Console\Commands\Upgrade\Support\ConfigFilesHelper;
use Backpack\CRUD\app\Console\Commands\Upgrade\UpgradeContext;

class CheckOperationConfigFilesStep extends Step
{
    protected ConfigFilesHelper $configs;

    public function __construct(UpgradeContext $context)
    {
        parent::__construct($context);

        $this->configs = new ConfigFilesHelper(
            $context,
            config_path('backpack/operations'),
            base_path('vendor/backpack/crud/src/config/backpack/operations')
        );
    }

    public function title(): string
    {
        return 'Check if operation config files are published and up to date';
    }

    public function run(): StepResult
    {
        $issues = [];

        if (! $this->configs->configFilesPublished()) {
            return StepResult::skipped('Operation config files are not published.');
        }

        foreach ($this->configs->missingKeysPerFile() as $relativePath => $missingKeys) {
            if (empty($missingKeys)) {
                continue;
            }

            $issues[] = sprintf('Add the missing keys to %s:', $relativePath);
            $issues = array_merge($issues, $this->previewList($missingKeys));
        }

        if (empty($issues)) {
            return StepResult::success('Published operation config are up to date with package version.');
        }

        return StepResult::warning(
            'Copy the new configuration options into your published operation config files so you can keep up to date with the latest features.',
            $issues,
            [
                'missing_entries' => $this->configs->collectedEntries(),
                'missing_entries_per_file' => $this->configs->topLevelMissingKeysPerFile(),
            ]
        );
    }

    public function canFix(StepResult $result): bool
    {
        if (! $result->status->isWarning()) {
            return false;
        }

        return ! empty($this->configs->topLevelEntriesPerFile());
    }

    public function fixMessage(StepResult $result): string
    {
        return 'Add the missing configuration keys to your published operation config files?';
    }

    public function fix(StepResult $result): StepResult
    {
        $entriesPerFile = $this->configs->topLevelEntriesPerFile();
        $absolutePaths = $this->configs->absolutePaths();

        if (empty($entriesPerFile)) {
            return StepResult::skipped('No missing configuration keys detected to apply automatically.');
        }

        $updatedFiles = [];

        foreach ($entriesPerFile as $displayPath => $entries) {
            if (empty($entries)) {
                continue;
            }

            $error = null;

            $absolutePath = $absolutePaths[$displayPath] ?? null;

            if ($absolutePath === null) {
                return StepResult::failure("Could not locate {$displayPath} on disk.");
            }

            if (! $this->configs->addEntriesToPublishedFile($absolutePath, $entries, $error)) {
                return StepResult::failure($error ?? "Could not update {$displayPath} automatically.");
            }

            $updatedFiles[] = $displayPath;
        }

        if (empty($updatedFiles)) {
            return StepResult::skipped('No missing configuration keys were eligible for automatic updates.');
        }

        $details = $this->previewList($updatedFiles, limit: count($updatedFiles));

        return StepResult::success('Added missing operation configuration keys automatically.', $details);
    }
}
