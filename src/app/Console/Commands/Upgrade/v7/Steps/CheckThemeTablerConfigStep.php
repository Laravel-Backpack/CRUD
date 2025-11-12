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

    public function title(): string
    {
        return 'Tabler theme configuration';
    }

    public function run(): StepResult
    {
        $this->currentContents = $this->context()->readFile($this->relativePath);
        $this->issues = [];

        $contents = $this->currentContents;

        if ($contents === null) {
            return StepResult::skipped('Tabler theme config not published. Publish it if you want to lock the legacy layout.');
        }

        if (str_contains($contents, "'layout' => 'horizontal'")) {
            $this->issues[] = "Set 'layout' => 'horizontal_overlap' to keep the v6 look.";
        }

        if ($this->hasActiveStyle($contents, 'glass.css')) {
            $this->issues[] = 'Remove glass.css from the styles array; the skin was dropped.';
        }

        if ($this->hasActiveStyle($contents, 'fuzzy-background.css')) {
            $this->issues[] = 'Remove fuzzy-background.css from the styles array; the asset was dropped.';
        }

        if (empty($this->issues)) {
            return StepResult::success('Tabler theme config already matches the new defaults.');
        }

        return StepResult::warning('Review config/backpack/theme-tabler.php.', $this->issues);
    }

    public function canFix(StepResult $result): bool
    {
        return $result->status === StepStatus::Warning && $this->currentContents !== null && ! empty($this->issues);
    }

    public function fixMessage(StepResult $result): string
    {
        return 'We can update config/backpack/theme-tabler.php with the recommended Backpack v7 options automatically. Apply this change?';
    }

    public function fix(StepResult $result): StepResult
    {
        if ($this->currentContents === null) {
            return StepResult::skipped('Tabler theme config not published.');
        }

        $updated = $this->currentContents;
        $changed = false;

        if (str_contains($updated, "'layout' => 'horizontal'")) {
            $updated = preg_replace("/'layout'\s*=>\s*'horizontal'/", "'layout' => 'horizontal_overlap'", $updated, 1) ?? $updated;
            $changed = true;
        }

        $removals = [
            "base_path('vendor/backpack/theme-tabler/resources/assets/css/skins/glass.css')",
            "base_path('vendor/backpack/theme-tabler/resources/assets/css/skins/fuzzy-background.css')",
        ];

        foreach ($removals as $removal) {
            $pattern = '~^[\t ]*'.preg_quote($removal, '~').',\s*\r?\n?~m';
            $new = preg_replace($pattern, '', $updated, -1, $count);

            if ($new !== null) {
                $updated = $new;
                if ($count > 0) {
                    $changed = true;
                }
            }
        }

        $updated = preg_replace('/^base_path/m', '        base_path', $updated);

        if (! $changed) {
            return StepResult::failure('Could not adjust Tabler config automatically.');
        }

        if (! $this->context()->writeFile($this->relativePath, $updated)) {
            return StepResult::failure('Failed writing changes to config/backpack/theme-tabler.php.');
        }

        return StepResult::success('Updated Tabler theme configuration for Backpack v7.');
    }

    private function hasActiveStyle(string $contents, string $needle): bool
    {
        $pattern = '~^[\t ]*(?!//).*'.preg_quote($needle, '~').'.*$~m';

        return (bool) preg_match($pattern, $contents);
    }
}
