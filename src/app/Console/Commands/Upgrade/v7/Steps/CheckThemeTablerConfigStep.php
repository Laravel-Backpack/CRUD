<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepStatus;

class CheckThemeTablerConfigStep extends Step
{
    protected string $relativePath = 'config/backpack/theme-tabler.php';

    private ?string $currentContents = null;

    private array $issues = [];

    private bool $needsPublish = false;

    public function title(): string
    {
        return 'Check if Theme tabler config is published';
    }

    public function run(): StepResult
    {
        $this->currentContents = $this->context()->readFile($this->relativePath);
        $this->issues = [];
        $this->needsPublish = false;

        $contents = $this->currentContents;

        if ($contents === null) {
            $this->needsPublish = true;

            return StepResult::warning(
                'Tabler theme config not published yet. Backpack v7 ships with a new tabler skin and layout.',
            );
        }

        if (str_contains($contents, "'layout' => 'horizontal'")) {
            $this->issues[] = "Set 'layout' => 'horizontal_overlap' to keep the v6 look.";
        }

        if ($this->hasActiveStyle($contents, 'glass.css')) {
            $this->issues[] = 'Comment out glass.css in the styles array to disable the new v7 skin.';
        }

        if ($this->hasActiveStyle($contents, 'fuzzy-background.css')) {
            $this->issues[] = 'Comment out fuzzy-background.css in the styles array to disable the new v7 skin.';
        }

        if (empty($this->issues)) {
            return StepResult::success('Tabler theme config already aligned with the recommended Backpack v7 settings.');
        }

        return StepResult::warning('Review config/backpack/theme-tabler.php.', $this->issues);
    }

    public function canFix(StepResult $result): bool
    {
        return $result->status === StepStatus::Warning
            && ($this->needsPublish || ($this->currentContents !== null && ! empty($this->issues)));
    }

    public function fixMessage(StepResult $result): string
    {
        return 'Do you want to revert to v6 skin and layout?';
    }

    public function fix(StepResult $result): StepResult
    {
        $updated = $this->currentContents;
        $changed = false;

        if ($this->currentContents === null) {
            $defaultConfig = $this->context()->readFile('vendor/backpack/theme-tabler/config/theme-tabler.php');

            if ($defaultConfig === null) {
                return StepResult::failure('Could not publish config/backpack/theme-tabler.php automatically.');
            }

            $updated = $defaultConfig;
            $changed = true;
        }

        if (str_contains($updated, "'layout' => 'horizontal'")) {
            $updated = preg_replace("/'layout'\s*=>\s*'horizontal'/", "'layout' => 'horizontal_overlap'", $updated, 1) ?? $updated;
            $changed = true;
        }

        $commentTargets = [
            "base_path('vendor/backpack/theme-tabler/resources/assets/css/skins/glass.css')",
            "base_path('vendor/backpack/theme-tabler/resources/assets/css/skins/fuzzy-background.css')",
        ];

        foreach ($commentTargets as $target) {
            $pattern = '~^[\t ]*'.preg_quote($target, '~').'([\t ]*,?[\t ]*)\r?$~m';
            $new = preg_replace_callback(
                $pattern,
                function (array $matches) use ($target) {
                    $position = strpos($matches[0], $target);
                    $indentation = $position === false ? '' : substr($matches[0], 0, $position);

                    return $indentation.'// '.$target.$matches[1];
                },
                $updated,
                1,
                $count
            );

            if ($new !== null) {
                $updated = $new;
                if ($count > 0) {
                    $changed = true;
                }
            }
        }

        if (! $changed) {
            return StepResult::failure('Could not adjust Tabler config automatically.');
        }

        if (! $this->context()->writeFile($this->relativePath, $updated)) {
            return StepResult::failure('Failed writing changes to config/backpack/theme-tabler.php.');
        }

        $this->currentContents = $updated;
        $this->needsPublish = false;

        return StepResult::success('Published and updated config/backpack/theme-tabler.php to keep the Backpack v6 layout.');
    }

    private function hasActiveStyle(string $contents, string $needle): bool
    {
        $pattern = '~^[\t ]*(?!//).*'.preg_quote($needle, '~').'.*$~m';

        return (bool) preg_match($pattern, $contents);
    }
}
