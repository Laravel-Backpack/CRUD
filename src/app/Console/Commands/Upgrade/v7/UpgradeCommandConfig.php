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
            Step\EnsureLaravelVersionStep::class,
            Step\EnsureBackpackCrudRequirementStep::class,
            Step\EnsureMinimumStabilityStep::class,
            Step\EnsureFirstPartyAddonsAreCompatibleStep::class,
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
        return [
            'backpack/crud' => self::backpackCrudRequirement(),
            'backpack/filemanager' => 'dev-next',
            'backpack/theme-coreuiv2' => 'dev-next',
            'backpack/theme-coreuiv4' => '^1.2',
            'backpack/theme-tabler' => 'dev-next',
            'backpack/logmanager' => '^5.1',
            'backpack/settings' => '^3.2',
            'backpack/newscrud' => '^5.2',
            'backpack/permissionmanager' => '^7.3',
            'backpack/pagemanager' => '^3.4',
            'backpack/menucrud' => '^4.1',
            'backpack/backupmanager' => '^5.1',
            'backpack/editable-columns' => '^3.1',
            'backpack/revise-operation' => '^2.1',
            'backpack/medialibrary-uploaders' => '^2.0',
            'backpack/devtools' => '^4.0',
            'backpack/generators' => '^4.1',
            'backpack/activity-log' => '^2.1',
            'backpack/calendar-operation' => '^1.1',
            'backpack/language-switcher' => '^2.1',
            'backpack/pan-panel' => '^1.1',
            'backpack/pro' => '^3.0.0-alpha',
            'backpack/translation-manager' => '^1.1',
            'backpack/ckeditor-field' => '^1.0',
            'backpack/tinymce-field' => '^1.0',
        ];
    }

    public function upgradeCommandDescription(): ?callable
    {
        return function (UpgradeCommand $command): void {
            $command->note(
                'These checks will highlight anything you need to tackle before enjoying the new release.'.PHP_EOL.
                '    Full upgrade instructions: <fg=cyan>https://backpackforlaravel.com/docs/7.x/upgrade-guide</>',
                'green',
                'green'
            );

            $command->note(
                'Before you start, make sure you have a fresh <fg=red>FULL BACKUP</> of your project and database stored safely.'.PHP_EOL.
                '    Run <fg=magenta>backpack:upgrade --version=7</> alongside the guide so you do not miss any manual steps.',
                'yellow',
                'yellow'
            );
        };
    }

    public function upgradeCommandSummary(): ?string
    {
        return 'Run the automated checks, while following the upgrade guide: <fg=cyan>https://backpackforlaravel.com/docs/7.x/upgrade-guide</>';
    }

    public static function backpackCrudRequirement(): string
    {
        return '^7.0.0-beta';
    }

    public static function postUpgradeCommands(): array
    {
        return [
            'php artisan basset:clear',
            'php artisan config:clear',
            'php artisan cache:clear',
            'php artisan view:clear',
        ];
    }
}
