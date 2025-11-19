<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade;

interface UpgradeConfigSummaryInterface
{
    public function upgradeCommandSummary(): ?string;
}
