<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7;

use Backpack\CRUD\app\Console\Commands\Upgrade\UpgradeCommand;
use Backpack\CRUD\app\Console\Commands\Upgrade\UpgradeConfigInterface;
use Backpack\CRUD\app\Console\Commands\Upgrade\UpgradeConfigSummaryInterface;
use Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps as Step;

class UpgradeCommandConfig implements UpgradeConfigInterface, UpgradeConfigSummaryInterface
{
    public function steps(): array
    {
        return [
            Step\CheckOperationConfigFilesStep::class,
            Step\CheckThemeTablerConfigStep::class,
            Step\DetectDeprecatedWysiwygUsageStep::class,
            Step\DetectEditorAddonRequirementsStep::class,
            Step\CheckShowOperationViewPublishedStep::class,
            Step\CheckShowOperationComponentConfigStep::class,
            Step\CheckFileManagerPublishedViewsStep::class,
            Step\CheckListOperationViewPublishedStep::class,
        ];
    }

    public function addons(): array
    {
        return [];
    }

    public function upgradeCommandDescription(): ?callable
    {
        return function (UpgradeCommand $command): void {
            $command->note(
                'Before you start, make sure you have a fresh <fg=red>FULL BACKUP</> of your project and database stored safely.',
                'yellow',
                'yellow'
            );
        };
    }

    public function upgradeCommandSummary(): ?string
    {
        return null;
    }

    public static function backpackCrudRequirement(): string
    {
        return '^7.0';
    }

    public static function postUpgradeCommands(): array
    {
        return [
            'php artisan optimize:clear',
            'php artisan basset:clear',
            'php artisan basset:cache'
        ];
    }
}
