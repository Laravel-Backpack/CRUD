<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7;

use Backpack\CRUD\app\Console\Commands\Upgrade\UpgradeConfigInterface;
use Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps as Step;

class UpgradeCommandConfig implements UpgradeConfigInterface
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
            'backpack/activity-log' => 'dev-next',
            'backpack/calendar-operation' => 'dev-next',
            'backpack/language-switcher' => 'dev-next',
            'backpack/pan-panel' => 'dev-next',
            'backpack/pro' => '^3.0.0-alpha',
            'backpack/translation-manager' => 'dev-next',
            'backpack/ckeditor-field' => 'dev-next',
            'backpack/tinymce-field' => 'dev-next',
        ];
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
