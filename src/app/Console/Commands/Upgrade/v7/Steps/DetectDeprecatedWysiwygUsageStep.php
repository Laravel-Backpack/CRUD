<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;

class DetectDeprecatedWysiwygUsageStep extends Step
{
    public function title(): string
    {
        return 'Detect deprecated wysiwyg field/column aliases';
    }

    public function run(): StepResult
    {
        $matches = $this->context()->searchTokens(['wysiwyg']);
        $paths = $matches['wysiwyg'] ?? [];

        if (empty($paths)) {
            return StepResult::success('No wysiwyg aliases detected.');
        }

        $preview = array_slice($paths, 0, 10);
        $details = array_map(fn ($path) => "- {$path}", $preview);

        if (count($paths) > count($preview)) {
            $details[] = sprintf('… %d more occurrence(s) omitted.', count($paths) - count($preview));
        }

        return StepResult::warning(
            'Replace the wysiwyg field/column with ckeditor or text (the alias was removed).',
            $details
        );
    }
}
