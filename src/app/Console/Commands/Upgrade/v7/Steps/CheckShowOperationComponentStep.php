<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;

class CheckShowOperationComponentStep extends Step
{
    protected string $relativePath = 'config/backpack/operations/show.php';

    public function title(): string
    {
        return 'Show operation component configuration';
    }

    public function run(): StepResult
    {
        $contents = $this->context()->readFile($this->relativePath);

        if ($contents === null) {
            return StepResult::skipped('show.php is not published, core defaults already use the new datagrid component.');
        }

        if (! str_contains($contents, "'component'")) {
            return StepResult::warning(
                "Add the 'component' option to config/backpack/operations/show.php to pick between bp-datagrid and bp-datalist.",
                ['Example:    "component" => "bp-datagrid"']
            );
        }

        if (str_contains($contents, "'bp-datalist'")) {
            return StepResult::success('Show operation will keep the classic bp-datalist component.');
        }

        return StepResult::success('Show operation is configured to use the new component.');
    }
}
