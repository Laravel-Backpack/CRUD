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
                'Thank you for choosing Backpack. If you are reading this, most likely you are upgrading from v6 to v7.'.PHP_EOL.
                '    We have prepared an upgrade guide to help you with the process: <fg=cyan>https://backpackforlaravel.com/docs/7.x/upgrade-guide</>',
                'green',
                'green'
            );

            $command->note(
                'You can choose <fg=magenta>v7</> option bellow, or call it later with: <fg=magenta>backpack:upgrade --version=7</>.',
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
