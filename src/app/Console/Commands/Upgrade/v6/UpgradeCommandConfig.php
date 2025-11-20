<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v6;

use Backpack\CRUD\app\Console\Commands\Upgrade\UpgradeCommand;
use Backpack\CRUD\app\Console\Commands\Upgrade\UpgradeConfigInterface;

class UpgradeCommandConfig implements UpgradeConfigInterface
{
    public function steps(): array
    {
        return [];
    }

    public function addons(): array
    {
        return [];
    }

    public function upgradeCommandDescription(): ?callable
    {
        return function (UpgradeCommand $command): void {
            $command->note(
                'Please select the upgrade path you wish to follow:',
                'yellow',
                'yellow'
            );
        };
    }

    public static function backpackCrudRequirement(): string
    {
        return '^6.0';
    }

    public static function postUpgradeCommands(): array
    {
        return [];
    }
}
