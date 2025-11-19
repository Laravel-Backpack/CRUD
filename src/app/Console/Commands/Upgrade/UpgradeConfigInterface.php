<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade;

interface UpgradeConfigInterface
{
    /**
     * @return array<class-string<\Backpack\CRUD\app\Console\Commands\Upgrade\Step>>
     */
    public function steps(): array;

    /**
     * @return array<string, string>
     */
    public function addons(): array;

    public function upgradeCommandDescription(): ?callable;

    public static function backpackCrudRequirement(): string;

    /**
     * @return array<int, string>
     */
    public static function postUpgradeCommands(): array;
}
