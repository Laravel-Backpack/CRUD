<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade;

class StepResult
{
    public function __construct(
        public readonly StepStatus $status,
        public readonly string $summary,
        public readonly array $details = [],
        public readonly array $context = []
    ) {
    }

    public static function success(string $summary, array $details = [], array $context = []): self
    {
        return new self(StepStatus::Passed, $summary, $details, $context);
    }

    public static function warning(string $summary, array $details = [], array $context = []): self
    {
        return new self(StepStatus::Warning, $summary, $details, $context);
    }

    public static function failure(string $summary, array $details = [], array $context = []): self
    {
        return new self(StepStatus::Failed, $summary, $details, $context);
    }

    public static function skipped(string $summary, array $details = [], array $context = []): self
    {
        return new self(StepStatus::Skipped, $summary, $details, $context);
    }
}
