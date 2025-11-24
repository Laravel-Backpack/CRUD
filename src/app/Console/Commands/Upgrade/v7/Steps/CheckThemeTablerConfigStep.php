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

    private array $issues = [];

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
        $this->issues = [];
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

        if ($this->configs->configKeyHasValue('layout', 'horizontal')) {
            $this->issues[] = "Set 'layout' => 'horizontal_overlap' to keep the v6 look.";
        }

        foreach ($this->legacyStyleDefinitions() as $style) {
            if ($this->configHasStyleValue($style['value'])) {
                $this->issues[] = $style['issue'];
            }
        }

        if (empty($this->issues)) {
            return StepResult::success('Tabler theme config already aligned with the recommended Backpack v7 settings.');
        }

        return StepResult::warning(
            'The tabler config file has a different layout than the one used in v6.',
        );
    }

    public function canFix(StepResult $result): bool
    {
        return $result->status === StepStatus::Warning
            && ($this->needsPublish || ($this->currentContents !== null && ! empty($this->issues)));
    }

    public function fixMessage(StepResult $result): string
    {
        if ($this->needsPublish) {
            return 'Publish the config file and adjust it to keep v6 skin and layout?';
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
        if ($this->needsPublish) {
            return $this->handleMissingConfigFix();
        }

        return $this->handlePublishedConfigFix();
    }

    private function handleMissingConfigFix(): StepResult
    {
        $option = $this->selectedOption ?? 'publish-old';

        if ($option === 'try-new') {
            $this->acceptedNewStyle = true;
            $this->needsPublish = false;
            $this->currentContents = null;
            $this->selectedOption = null;

            return StepResult::success('Keeping the Backpack v7 Tabler style. No config file was published.');
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

        if (! $this->applyLegacyAdjustmentsToPublishedConfig()) {
            return StepResult::failure('Could not adjust Tabler config automatically.');
        }

        $this->needsPublish = false;
        $this->acceptedNewStyle = false;
        $this->selectedOption = null;

        return StepResult::success('Published and updated config/backpack/theme-tabler.php to keep the Backpack v6 layout.');
    }

    private function handlePublishedConfigFix(): StepResult
    {
        $this->currentContents = $this->configs->readPublishedFile($this->configFilename);

        if ($this->currentContents === null) {
            return StepResult::failure('Could not read config/backpack/theme-tabler.php to update it automatically.');
        }

        if (! $this->applyLegacyAdjustmentsToPublishedConfig()) {
            return StepResult::failure('Could not adjust Tabler config automatically.');
        }

        $this->acceptedNewStyle = false;
        $this->selectedOption = null;

        return StepResult::success('Updated config/backpack/theme-tabler.php to keep the Backpack v6 layout.');
    }

    private function applyLegacyAdjustmentsToPublishedConfig(): bool
    {
        $changed = false;

        if ($this->configs->updateConfigKeyValue('layout', 'horizontal_overlap')) {
            $changed = true;
        }

        foreach ($this->legacyStyleDefinitions() as $style) {
            if ($this->configs->commentOutConfigValue($style['expression'])) {
                $changed = true;
            }
        }

        $this->currentContents = $this->configs->readPublishedFile($this->configFilename);

        return $changed;
    }

    private function legacyStyleDefinitions(): array
    {
        return [
            [
                'expression' => "base_path('vendor/backpack/theme-tabler/resources/assets/css/skins/glass.css')",
                'value' => base_path('vendor/backpack/theme-tabler/resources/assets/css/skins/glass.css'),
                'issue' => 'Comment out glass.css in the styles array to disable the new v7 skin.',
            ],
            [
                'expression' => "base_path('vendor/backpack/theme-tabler/resources/assets/css/skins/fuzzy-background.css')",
                'value' => base_path('vendor/backpack/theme-tabler/resources/assets/css/skins/fuzzy-background.css'),
                'issue' => 'Comment out fuzzy-background.css in the styles array to disable the new v7 skin.',
            ],
        ];
    }

    private function configHasStyleValue(string $value): bool
    {
        return $this->configs->configKeyHasValue('styles', $value)
            || $this->configs->configKeyHasValue('style', $value);
    }
}
