<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepStatus;

class CheckShowOperationComponentConfigStep extends Step
{
    protected string $relativePath = 'config/backpack/operations/show.php';

    protected bool $missingComponent = false;

    public function title(): string
    {
        return 'Show operation configuration';
    }

    public function run(): StepResult
    {
        $this->missingComponent = false;

        $contents = $this->context()->readFile($this->relativePath);

        if ($contents === null) {
            return StepResult::skipped('show.php config file is not published, core defaults already use the new datagrid component.');
        }

        if (! str_contains($contents, "'component'")) {
            $this->missingComponent = true;

            return StepResult::warning(
                "Add the 'component' option to config/backpack/operations/show.php to pick between bp-datagrid and bp-datalist.",
                ['Example:    "component" => "bp-datagrid"']
            );
        }

        if (str_contains($contents, "'bp-datalist'")) {
            return StepResult::success('Show operation will keep the classic bp-datalist component.');
        }

        return StepResult::success('Show operation config file already has the new "component" key.');
    }

    public function canFix(StepResult $result): bool
    {
        return $result->status === StepStatus::Warning && $this->missingComponent;
    }

    public function fixMessage(StepResult $result): string
    {
        return 'We can add the component option to config/backpack/operations/show.php automatically. Apply this change?';
    }

    public function fix(StepResult $result): StepResult
    {
        $contents = $this->context()->readFile($this->relativePath);

        if ($contents === null) {
            return StepResult::skipped('show.php config file is not published, core defaults already use the new datagrid component.');
        }

        if (str_contains($contents, "'component'")) {
            return StepResult::success('Show operation config already defines the component option.');
        }

        $newline = str_contains($contents, "\r\n") ? "\r\n" : "\n";
        $pattern = '/(return\s*\[\s*(?:\r?\n))/';
        $replacement = '$1'
            .'    // Which component to use for displaying the Show page?'
            .$newline
            ."    'component' => 'bp-datagrid', // options: bp-datagrid, bp-datalist, or a custom component alias"
            .$newline.$newline;

        $updatedContents = preg_replace($pattern, $replacement, $contents, 1, $replacements);

        if ($updatedContents === null || $replacements === 0) {
            return StepResult::failure('Could not update show.php automatically.');
        }

        if (! $this->context()->writeFile($this->relativePath, $updatedContents)) {
            return StepResult::failure('Could not save the updated show.php configuration.');
        }

        $this->missingComponent = false;

        return StepResult::success("Added the 'component' option to config/backpack/operations/show.php.");
    }
}
