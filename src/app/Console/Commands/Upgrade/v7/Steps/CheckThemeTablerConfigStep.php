<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepStatus;
use Backpack\CRUD\app\Console\Commands\Upgrade\Support\ConfigFilesHelper;
use Backpack\CRUD\app\Console\Commands\Upgrade\UpgradeContext;

class CheckThemeTablerConfigStep extends Step
{
    protected ConfigFilesHelper $configs;

    protected string $configFilename = 'theme-tabler.php';

    private ?string $currentContents = null;

    private bool $needsPublish = false;

    private ?string $selectedOption = null;

    private bool $acceptedNewStyle = false;

    public function __construct(UpgradeContext $context)
    {
        parent::__construct($context);

        $this->configs = new ConfigFilesHelper(
            $context,
            config_path('backpack/theme-tabler.php'),
            base_path('vendor/backpack/theme-tabler/config/theme-tabler.php')
        );

        $this->configs->setDefaultConfigFile($this->configFilename);
    }

    public function title(): string
    {
        return 'Check if Theme tabler config is published';
    }

    public function run(): StepResult
    {
        $this->needsPublish = false;
        $this->selectedOption = null;
        $this->currentContents = $this->configs->readPublishedFile($this->configFilename);

        if ($this->currentContents === null) {
            if ($this->acceptedNewStyle) {
                return StepResult::success('Using the Backpack v7 Tabler defaults without publishing the config.');
            }

            $this->needsPublish = true;

            return StepResult::warning(
                'Tabler theme config not published yet. Backpack v7 ships with a new tabler skin and layout.'
            );
        }

        return StepResult::success('Tabler theme config already published.');
    }

    public function canFix(StepResult $result): bool
    {
        return $result->status === StepStatus::Warning && $this->needsPublish;
    }

    public function fixMessage(StepResult $result): string
    {
        if ($this->needsPublish) {
            return 'Do you want to keep the OLD Backpack style?';
        }

        return 'Do you want to revert to v6 skin and layout?';
    }

    public function fixOptions(StepResult $result): array
    {
        if (! $this->needsPublish || $this->acceptedNewStyle) {
            return [];
        }

        return [
            [
                'key' => 'publish-old',
                'label' => 'Yes',
                'default' => true,
            ],
            [
                'key' => 'try-new',
                'label' => 'No',
            ],
        ];
    }

    public function selectFixOption(?string $option): void
    {
        $this->selectedOption = $option;
    }

    public function fix(StepResult $result): StepResult
    {
        if (! $this->needsPublish) {
            return StepResult::skipped('Tabler theme config already published.');
        }

        return $this->handleMissingConfigFix();
    }

    private function handleMissingConfigFix(): StepResult
    {
        $option = $this->selectedOption ?? 'publish-old';

        if ($option === 'try-new') {
            $this->acceptedNewStyle = true;
            $this->needsPublish = false;
            $this->currentContents = null;
            $this->selectedOption = null;

            return StepResult::success('No configuration file published.');
        }

        if ($option !== 'publish-old') {
            $this->selectedOption = null;

            return StepResult::skipped('No Tabler config changes applied.');
        }

        $packagePath = $this->configs->packageConfigPath($this->configFilename);

        if (! is_file($packagePath)) {
            return StepResult::failure('Could not publish config/backpack/theme-tabler.php automatically.');
        }

        $defaultContents = @file_get_contents($packagePath);

        if ($defaultContents === false) {
            return StepResult::failure('Could not read the default Tabler config to publish it automatically.');
        }

        if (! $this->configs->writePublishedFile($this->configFilename, $defaultContents)) {
            return StepResult::failure('Failed writing changes to config/backpack/theme-tabler.php.');
        }

        $this->needsPublish = false;
        $this->acceptedNewStyle = false;
        $this->selectedOption = null;
        $this->currentContents = $defaultContents;

        return StepResult::success('Published config/backpack/theme-tabler.php.');
    }
}
