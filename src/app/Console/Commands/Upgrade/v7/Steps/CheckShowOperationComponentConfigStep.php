<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepStatus;
use Backpack\CRUD\app\Console\Commands\Upgrade\Support\ConfigFilesHelper;
use Backpack\CRUD\app\Console\Commands\Upgrade\UpgradeContext;

class CheckShowOperationComponentConfigStep extends Step
{
    protected string $operationFilename = 'show.php';

    protected bool $missingComponent = false;

    protected ConfigFilesHelper $configs;

    public function __construct(UpgradeContext $context)
    {
        parent::__construct($context);

        $this->configs = new ConfigFilesHelper(
            $context,
            config_path('backpack/operations/show.php'),
            base_path('vendor/backpack/crud/src/config/backpack/operations/show.php')
        );
    }

    public function title(): string
    {
        return 'Check if Show operation config has the component option';
    }

    public function run(): StepResult
    {
        $this->missingComponent = false;

        if (! $this->configs->configFilesPublished()) {
            return StepResult::skipped('show.php config file is not published, core defaults already use the new datagrid component.');
        }

        if (! $this->configs->publishedFileContainsKey($this->operationFilename, 'component')) {
            $this->missingComponent = true;

            return StepResult::warning(
                "The 'component' key is missing from the show operation config file.",
            );
        }

        return StepResult::success('Show operation config file already has the new "component" key.');
    }

    public function canFix(StepResult $result): bool
    {
        return $result->status === StepStatus::Warning && $this->missingComponent;
    }

    public function fixMessage(StepResult $result): string
    {
        return 'Add the component key to the config file?';
    }

    public function fix(StepResult $result): StepResult
    {
        if (! $this->configs->configFilesPublished()) {
            return StepResult::skipped('show.php config file is not published, core defaults already use the new datagrid component.');
        }

        if ($this->configs->publishedFileContainsKey($this->operationFilename, 'component')) {
            return StepResult::success('Show operation config already defines the component option.');
        }

        $relativePath = $this->configs->publishedRelativePath($this->operationFilename);
        $snippet = '    // Which component to use for displaying the Show page?'.PHP_EOL
            ."    'component' => 'bp-datagrid', // options: bp-datagrid, bp-datalist, or a custom component alias";

        $error = null;

        if (! $this->configs->addKeyToConfigFile($this->operationFilename, $snippet, $error)) {
            return StepResult::failure($error ?? 'Could not update show.php automatically.');
        }

        $this->missingComponent = false;

        return StepResult::success("Added the 'component' option to {$relativePath}.");
    }
}
