<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade;

enum StepStatus: string
{
    case Passed = 'passed';
    case Warning = 'warning';
    case Failed = 'failed';
    case Skipped = 'skipped';

    public function label(): string
    {
        return match ($this) {
            self::Passed => 'done',
            self::Warning => 'warn',
            self::Failed => 'fail',
            self::Skipped => 'skip',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Passed => 'green',
            self::Warning => 'yellow',
            self::Failed => 'red',
            self::Skipped => 'gray',
        };
    }

    public function isFailure(): bool
    {
        return $this === self::Failed;
    }

    public function isWarning(): bool
    {
        return $this === self::Warning;
    }
}
