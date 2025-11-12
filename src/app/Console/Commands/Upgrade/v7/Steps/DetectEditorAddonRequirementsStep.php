<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Concerns\InteractsWithCrudControllers;
use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepStatus;

class DetectEditorAddonRequirementsStep extends Step
{
    use InteractsWithCrudControllers;

    protected array $editors = [
        'ckeditor' => [
            'package' => 'backpack/ckeditor-field',
            'constraint' => 'dev-next',
        ],
        'tinymce' => [
            'package' => 'backpack/tinymce-field',
            'constraint' => 'dev-next',
        ],
    ];

    private array $missingPackages = [];

    private array $uninstalledPackages = [];

    public function title(): string
    {
        return 'Ensure rich text editor add-ons are installed';
    }

    public function run(): StepResult
    {
        $this->missingPackages = [];
        $this->uninstalledPackages = [];

        $matches = $this->context()->searchTokens(array_keys($this->editors));

        foreach ($this->editors as $keyword => $config) {
            $package = $config['package'];
            $recommendedConstraint = $config['constraint'] ?? 'dev-next';

            $paths = $this->filterCrudControllerPaths($matches[$keyword] ?? []);

            if (empty($paths)) {
                continue;
            }

            $installedVersion = $this->context()->installedPackageVersion($package);
            $composerConstraint = $this->context()->composerRequirement($package);

            if ($composerConstraint !== null && $installedVersion !== null) {
                continue;
            }

            $details = $this->previewList(
                $paths,
                10,
                fn (string $path): string => "- {$keyword} usage: {$path}",
                '... %d more occurrence(s) omitted.'
            );

            if ($composerConstraint === null) {
                $this->missingPackages[$package] = [
                    'keyword' => $keyword,
                    'details' => $details,
                    'paths' => $paths,
                    'constraint' => $recommendedConstraint,
                ];

                continue;
            }

            if ($installedVersion === null) {
                $this->uninstalledPackages[$package] = [
                    'keyword' => $keyword,
                    'details' => $details,
                    'paths' => $paths,
                    'constraint' => $composerConstraint,
                ];
            }
        }

        if (empty($this->missingPackages) && empty($this->uninstalledPackages)) {
            return StepResult::success('No missing editor add-ons detected.');
        }

        $detailLines = [];

        if (! empty($this->missingPackages)) {
            $detailLines[] = 'Missing editor packages (add to composer.json):';

            foreach ($this->missingPackages as $package => $data) {
                $detailLines[] = sprintf('- %s (%s field/column usage detected)', $package, $data['keyword']);
                $detailLines = array_merge($detailLines, $data['details']);
            }
        }

        if (! empty($this->uninstalledPackages) && empty($this->missingPackages)) {
            $detailLines[] = 'Declared but not installed (run composer update):';

            foreach ($this->uninstalledPackages as $package => $data) {
                $detailLines[] = sprintf('- %s (%s field/column usage detected)', $package, $data['keyword']);
                $detailLines = array_merge($detailLines, $data['details']);
            }
        }

        $context = [
            'missing_packages' => array_keys($this->missingPackages),
            'uninstalled_packages' => array_keys($this->uninstalledPackages),
        ];

        if (! empty($this->missingPackages)) {
            return StepResult::failure(
                'Install the missing editor add-ons before continuing the upgrade.',
                $detailLines,
                $context
            );
        }

        return StepResult::warning(
            'Run composer update to install the declared editor add-ons.',
            $detailLines,
            $context
        );
    }

    public function canFix(StepResult $result): bool
    {
        return $result->status === StepStatus::Failed && ! empty($this->missingPackages);
    }

    public function fixMessage(StepResult $result): string
    {
        return 'We can add the missing editor packages to composer.json automatically. Apply this change?';
    }

    public function fix(StepResult $result): StepResult
    {
        if (empty($this->missingPackages)) {
            return StepResult::skipped('No missing editor packages detected.');
        }

        $missingPackages = $this->missingPackages;

        $updated = $this->context()->updateComposerJson(function (array &$composer) use ($missingPackages) {
            $composer['require'] = $composer['require'] ?? [];

            foreach ($missingPackages as $package => $data) {
                $constraint = $data['constraint'] ?? 'dev-next';

                if (! array_key_exists($package, $composer['require'])) {
                    $composer['require'][$package] = $constraint;
                }
            }
        });

        if (! $updated) {
            return StepResult::failure('Could not update composer.json automatically.');
        }

        return StepResult::success('Added the missing editor packages to composer.json.');
    }
}
