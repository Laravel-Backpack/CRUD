<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade;

abstract class Step
{
    public function __construct(protected UpgradeContext $context)
    {
    }

    abstract public function title(): string;

    public function description(): ?string
    {
        return null;
    }

    abstract public function run(): StepResult;

    protected function context(): UpgradeContext
    {
        return $this->context;
    }

    public function canFix(StepResult $result): bool
    {
        return false;
    }

    public function fix(StepResult $result): StepResult
    {
        return StepResult::skipped('No automatic fix available.');
    }
}
