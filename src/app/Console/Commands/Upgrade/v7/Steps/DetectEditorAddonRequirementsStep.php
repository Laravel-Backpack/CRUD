<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;

class DetectEditorAddonRequirementsStep extends Step
{
    protected array $editors = [
        'ckeditor' => 'backpack/ckeditor-field',
        'tinymce' => 'backpack/tinymce-field',
    ];

    public function title(): string
    {
        return 'Ensure rich text editor add-ons are installed';
    }

    public function run(): StepResult
    {
        $matches = $this->context()->searchTokens(array_keys($this->editors));
        $issues = [];

        foreach ($this->editors as $keyword => $package) {
            $paths = $matches[$keyword] ?? [];

            if (empty($paths)) {
                continue;
            }

            if ($this->context()->hasComposerPackage($package)) {
                continue;
            }

            $preview = array_slice($paths, 0, 10);
            $details = array_map(fn ($path) => "- {$keyword} usage: {$path}", $preview);

            if (count($paths) > count($preview)) {
                $details[] = sprintf('… %d more occurrence(s) omitted.', count($paths) - count($preview));
            }

            $issues[] = [
                'summary' => sprintf('Install %s to keep using the %s field/column.', $package, $keyword),
                'details' => $details,
            ];
        }

        if (empty($issues)) {
            return StepResult::success('No missing editor add-ons detected.');
        }

        $summary = 'Install the missing editor packages listed below.';
        $detailLines = [];

        foreach ($issues as $issue) {
            $detailLines[] = $issue['summary'];
            $detailLines = array_merge($detailLines, $issue['details']);
        }

        return StepResult::warning($summary, $detailLines);
    }
}
