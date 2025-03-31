<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade;

use Backpack\CRUD\app\Console\Commands\Upgrade\Concerns\ExtractsFirstInteger;

abstract class Step
{
    use ExtractsFirstInteger;

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

    public function fixMessage(StepResult $result): string
    {
        return 'Apply automatic fix?';
    }

    public function fix(StepResult $result): StepResult
    {
        return StepResult::skipped('No automatic fix available.');
    }

    /**
     * Provide optional choices for automatic fixes. When empty, a yes/no confirmation is shown.
     *
     * @return array<int|string, mixed>
     */
    public function fixOptions(StepResult $result): array
    {
        return [];
    }

    public function selectFixOption(?string $option): void
    {
    }

    public function isBlocking(): bool
    {
        return false;
    }

    /**
     * Build a preview of items with an optional formatter and overflow message.
     *
     * @param  array<int, mixed>  $items
     * @param  ?callable  $formatter
     * @return array<int, string>
     */
    protected function previewList(
        array $items,
        int $limit = 10,
        ?callable $formatter = null,
        ?string $overflowMessage = null
    ): array {
        if (empty($items)) {
            return [];
        }

        $formatter ??= static fn ($item): string => '- '.(string) $item;
        $preview = array_slice($items, 0, $limit);
        $details = array_map($formatter, $preview);

        $remaining = count($items) - count($preview);

        if ($remaining > 0) {
            $details[] = sprintf($overflowMessage ?? '... %d more item(s) omitted.', $remaining);
        }

        return $details;
    }
}
