<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade;

use Backpack\CRUD\app\Console\Commands\Traits\PrettyCommandOutput;
use Backpack\CRUD\app\Console\Commands\Upgrade\Concerns\ExtractsFirstInteger;
use Composer\InstalledVersions;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use OutOfBoundsException;
use RuntimeException;

class UpgradeCommand extends Command
{
    use PrettyCommandOutput;
    use ExtractsFirstInteger;

    protected $signature = 'backpack:upgrade
                                {version? : Target Backpack version to prepare for (e.g. v7, 7.1, v7-1).}
                                {--debug : Show debug information for executed processes.}';

    protected $description = 'Run opinionated upgrade checks to help you move between Backpack major versions.';

    protected ?array $availableConfigCache = null;

    protected array $resolvedConfigCache = [];

    protected array $renderedUpgradeDescriptions = [];

    protected array $descriptorChoiceSummaries = [];

    public function handle(): int
    {
        try {
            $descriptor = $this->determineTargetDescriptor($this->argument('version'));
            $config = $this->resolveConfigForDescriptor($descriptor);
        } catch (RuntimeException $exception) {
            $this->errorBlock($exception->getMessage());

            return Command::INVALID;
        }

        $stepClasses = $config->steps();
        if (empty($stepClasses)) {
            $this->errorBlock("No automated checks registered for Backpack {$descriptor['label']}.");

            return Command::INVALID;
        }

        $context = new UpgradeContext($descriptor['version'], addons: $config->addons());

        $this->infoBlock("Backpack {$descriptor['label']} upgrade assistant", 'upgrade');

        if ($this->displayDescriptorDescription($descriptor, $config)) {
            $this->newLine();
        }

        $results = [];

        foreach ($stepClasses as $stepClass) {
            /** @var Step $step */
            $step = new $stepClass($context);

            $this->progressBlock($step->title());

            try {
                $result = $step->run();
            } catch (\Throwable $exception) {
                $result = StepResult::failure(
                    $exception->getMessage(),
                    [
                        'Step: '.$stepClass,
                    ]
                );
            }

            $this->closeProgressBlock(strtoupper($result->status->label()), $result->status->color());

            $this->printResultDetails($result);

            if ($result->status->isFailure() && $step->isBlocking()) {
                $this->note(
                    sprintf(
                        'Please solve the issue above, then re-run `php artisan backpack:upgrade %s`.',
                        $descriptor['label']
                    ),
                    'red',
                    'red'
                );

                return Command::FAILURE;
            }

            if ($this->shouldOfferFix($step, $result)) {
                $options = $step->fixOptions($result);

                if (! empty($options)) {
                    [$choiceMap, $defaultLabel] = $this->normalizeFixOptions($options);

                    if (! empty($choiceMap)) {
                        $question = trim($step->fixMessage($result));
                        $question = $question !== '' ? $question : 'Select an automatic fix option';
                        $selectedLabel = $this->choice('  '.$question, array_keys($choiceMap), $defaultLabel);
                        $selectedOption = $choiceMap[$selectedLabel] ?? null;

                        if ($selectedOption !== null && $selectedOption !== '') {
                            $step->selectFixOption((string) $selectedOption);

                            $this->progressBlock('Applying automatic fix');
                            $fixResult = $step->fix($result);
                            $this->closeProgressBlock(strtoupper($fixResult->status->label()), $fixResult->status->color());
                            $this->printResultDetails($fixResult);

                            if (! $fixResult->status->isFailure()) {
                                $this->progressBlock('Re-running '.$step->title());

                                try {
                                    $result = $step->run();
                                } catch (\Throwable $exception) {
                                    $result = StepResult::failure(
                                        $exception->getMessage(),
                                        [
                                            'Step: '.$stepClass,
                                        ]
                                    );
                                }

                                $this->closeProgressBlock(strtoupper($result->status->label()), $result->status->color());
                                $this->printResultDetails($result);
                            }
                        }
                    }
                } else {
                    $question = trim($step->fixMessage($result));
                    $question = $question !== '' ? $question : 'Apply automatic fix?';
                    $applyFix = $this->confirm('  '.$question, true);

                    if ($applyFix) {
                        $this->progressBlock('Applying automatic fix');
                        $fixResult = $step->fix($result);
                        $this->closeProgressBlock(strtoupper($fixResult->status->label()), $fixResult->status->color());
                        $this->printResultDetails($fixResult);

                        if (! $fixResult->status->isFailure()) {
                            $this->progressBlock('Re-running '.$step->title());

                            try {
                                $result = $step->run();
                            } catch (\Throwable $exception) {
                                $result = StepResult::failure(
                                    $exception->getMessage(),
                                    [
                                        'Step: '.$stepClass,
                                    ]
                                );
                            }

                            $this->closeProgressBlock(strtoupper($result->status->label()), $result->status->color());
                            $this->printResultDetails($result);
                        }
                    }
                }
            }

            $results[] = [
                'step' => $stepClass,
                'title' => $step->title(),
                'result' => $result,
            ];
        }

        $expectedVersionInstalled = $this->hasExpectedBackpackVersion($context, $config);

        $this->outputSummary($descriptor['label'], $results, $expectedVersionInstalled, $config);

        $this->note('The script has only updated what could be automated. '.PHP_EOL.'    Please run composer update to finish Step 1, then go back to the Upgrade Guide and follow all other steps, to make sure your admin panel is correctly upgraded: https://backpackforlaravel.com/docs/7.x/upgrade-guide#step-2', 'white', 'white');

        return Command::SUCCESS;
    }

    protected function outputSummary(
        string $versionLabel,
        array $results,
        bool $expectedVersionInstalled = false,
        ?UpgradeConfigInterface $config = null
    ): int {
        $resultsCollection = collect($results);

        $hasFailure = $resultsCollection->contains(function ($entry) {
            /** @var StepResult $result */
            $result = $entry['result'];

            return $result->status->isFailure();
        });

        $warnings = $resultsCollection->filter(function ($entry) {
            /** @var StepResult $result */
            $result = $entry['result'];

            return $result->status === StepStatus::Warning;
        });

        $failedTitles = $resultsCollection
            ->filter(function ($entry) {
                /** @var StepResult $result */
                $result = $entry['result'];

                return $result->status->isFailure();
            })
            ->pluck('title');

        $warningTitles = $warnings->pluck('title');

        $this->newLine();
        $this->infoBlock('Summary', 'done');

        $this->note(sprintf('Checked %d upgrade steps.', count($results)), 'gray');

        if ($hasFailure) {
            $this->note('At least one step reported a failure. Review the messages above before continuing.', 'red', 'red');
        }

        if (! $hasFailure && $warnings->isEmpty()) {
            $this->note('All checks passed, you are ready to continue with the manual steps from the upgrade guide.', 'green', 'green');
        }

        if ($failedTitles->isNotEmpty()) {
            $this->note('Failed steps:', 'red', 'red');

            foreach ($failedTitles as $title) {
                $this->note(' - '.$title, 'red', 'red');
            }
        }

        if ($warningTitles->isNotEmpty()) {
            $this->note(sprintf('(%d) Warnings:', $warningTitles->count()), 'yellow', 'yellow');

            foreach ($warningTitles as $title) {
                $this->note(' - '.$title, 'yellow', 'yellow');
            }
        }

        $postUpgradeCommands = [];

        if ($config !== null) {
            $postUpgradeCommands = ($config)::postUpgradeCommands();
        }

        if ($expectedVersionInstalled && ! $hasFailure && ! empty($postUpgradeCommands)) {
            $this->note("Now that you have {$versionLabel} installed, don't forget to run the following commands:", 'green', 'green');

            foreach ($postUpgradeCommands as $command) {
                $this->note($command);
            }
        }

        $this->newLine();

        return $hasFailure ? Command::FAILURE : Command::SUCCESS;
    }

    protected function printResultDetails(StepResult $result): void
    {
        $color = match ($result->status) {
            StepStatus::Passed => 'green',
            StepStatus::Warning => 'yellow',
            StepStatus::Failed => 'red',
            StepStatus::Skipped => 'gray',
        };

        if ($result->summary !== '') {
            $this->note($result->summary, $color, $color);
        }

        foreach ($result->details as $detail) {
            $this->note($detail, 'gray');
        }

        $this->newLine();
    }

    protected function shouldOfferFix(Step $step, StepResult $result): bool
    {
        if (! $this->input->isInteractive()) {
            return false;
        }

        if (! in_array($result->status, [StepStatus::Warning, StepStatus::Failed], true)) {
            return false;
        }

        return $step->canFix($result);
    }

    /**
     * @param  array<int|string, mixed>  $options
     * @return array{0: array<string, ?string>, 1: ?string}
     */
    protected function normalizeFixOptions(array $options): array
    {
        $choices = [];
        $defaultLabel = null;

        foreach ($options as $key => $option) {
            $label = null;
            $value = null;
            $isDefault = false;

            if (is_array($option)) {
                $label = $option['label'] ?? null;
                $value = $option['key'] ?? (is_string($key) ? $key : null);
                $isDefault = (bool) ($option['default'] ?? false);
            } else {
                $label = (string) $option;
                $value = is_string($key) ? $key : $label;
            }

            if (! is_string($label)) {
                continue;
            }

            $label = trim($label);

            if ($label === '') {
                continue;
            }

            $value = $value === null ? null : (string) $value;
            $choices[$label] = $value;

            if ($isDefault && $defaultLabel === null) {
                $defaultLabel = $label;
            }
        }

        if ($defaultLabel === null && ! empty($choices)) {
            $defaultLabel = array_key_first($choices);
        }

        return [$choices, $defaultLabel];
    }

    protected function resolveConfigForDescriptor(array $descriptor): UpgradeConfigInterface
    {
        $descriptorKey = $descriptor['key'] ?? null;

        if ($descriptorKey !== null && isset($this->resolvedConfigCache[$descriptorKey])) {
            return $this->resolvedConfigCache[$descriptorKey];
        }

        $configProviderClass = sprintf('%s\\%s\\UpgradeCommandConfig', __NAMESPACE__, $descriptor['namespace']);

        if (! class_exists($configProviderClass)) {
            $this->manuallyLoadConfigDirectory($descriptor);
        }

        if (! class_exists($configProviderClass)) {
            throw new RuntimeException(sprintf(
                'Missing upgrade config provider for Backpack %s. Please create %s.',
                $descriptor['label'],
                $configProviderClass
            ));
        }

        $provider = $this->laravel
            ? $this->laravel->make($configProviderClass)
            : new $configProviderClass();

        if (! $provider instanceof UpgradeConfigInterface) {
            throw new RuntimeException(sprintf(
                'Upgrade config provider [%s] must implement %s.',
                $configProviderClass,
                UpgradeConfigInterface::class
            ));
        }

        $steps = $provider->steps();

        if (! is_array($steps)) {
            throw new RuntimeException(sprintf(
                'Upgrade config provider [%s] must return an array of step class names.',
                $configProviderClass
            ));
        }

        if ($descriptorKey !== null) {
            $this->resolvedConfigCache[$descriptorKey] = $provider;
        }

        return $provider;
    }

    protected function determineTargetDescriptor(mixed $requestedVersion): array
    {
        $available = $this->availableVersionDescriptors();

        if (empty($available)) {
            throw new RuntimeException('No upgrade configurations were found. Please create one under '.basename(__DIR__).'.');
        }

        $normalizedRequested = $this->normalizeVersionKey(
            is_string($requestedVersion) ? $requestedVersion : null
        );

        if ($normalizedRequested !== null) {
            if (isset($available[$normalizedRequested])) {
                return $available[$normalizedRequested];
            }

            $knownTargets = implode(', ', array_map(
                fn (array $descriptor) => $descriptor['label'],
                $this->sortDescriptors($available)
            ));

            throw new RuntimeException(sprintf(
                'Unknown upgrade target [%s]. Available targets: %s.',
                (string) $requestedVersion,
                $knownTargets !== '' ? $knownTargets : 'none'
            ));
        }

        $currentKey = $this->detectCurrentVersionKey($available);

        if ($this->input->isInteractive()) {
            $sorted = $this->sortDescriptors($available);

            if (count($sorted) === 1) {
                $singleDescriptor = $sorted[0];
                $singleConfig = $this->resolveConfigForDescriptor($singleDescriptor);

                if (! $this->descriptorHasSteps($singleConfig)) {
                    if ($this->displayDescriptorDescription($singleDescriptor, $singleConfig)) {
                        $this->newLine();
                    }
                }

                return $singleDescriptor;
            }

            $choices = [];
            $defaultChoice = null;
            $summaries = [];

            foreach ($sorted as $descriptor) {
                $config = $this->resolveConfigForDescriptor($descriptor);
                $hasSteps = $this->descriptorHasSteps($config);
                $summary = $hasSteps ? $this->descriptorChoiceSummary($descriptor, $config) : null;

                if (! $hasSteps) {
                    if ($this->displayDescriptorDescription($descriptor, $config)) {
                        $this->newLine();
                    }

                    continue;
                }

                $isCurrent = $currentKey !== null && $descriptor['key'] === $currentKey;
                $label = $this->buildChoiceLabel($descriptor, $isCurrent);

                $choices[$label] = $descriptor['key'];

                if ($isCurrent && $defaultChoice === null) {
                    $defaultChoice = $label;
                }

                $summaries[] = [
                    'label' => $label,
                    'summary' => $summary ?? 'Automated checks are available for this version.',
                    'is_current' => $isCurrent,
                ];
            }

            if (empty($choices)) {
                throw new RuntimeException('No upgrade targets with automated checks are available.');
            }

            $this->outputDescriptorSummaryList($summaries);

            if ($defaultChoice === null) {
                $defaultChoice = array_key_first($choices);
            }

            $selectedLabel = $this->choice(
                'Select the Backpack upgrade path you want to run',
                array_keys($choices),
                $defaultChoice
            );

            $selectedKey = $choices[$selectedLabel] ?? null;

            if ($selectedKey !== null && isset($available[$selectedKey])) {
                return $available[$selectedKey];
            }

            throw new RuntimeException('Invalid upgrade target selection.');
        }

        if ($currentKey !== null && isset($available[$currentKey])) {
            return $available[$currentKey];
        }

        $sorted = $this->sortDescriptors($available, 'desc');

        return $sorted[0];
    }

    protected function displayDescriptorDescription(array $descriptor, ?UpgradeConfigInterface $config = null): bool
    {
        $key = $descriptor['key'] ?? null;

        if ($key === null) {
            return false;
        }

        if (isset($this->renderedUpgradeDescriptions[$key])) {
            return false;
        }

        try {
            $config ??= $this->resolveConfigForDescriptor($descriptor);
        } catch (\Throwable $exception) {
            return false;
        }

        $this->renderedUpgradeDescriptions[$key] = true;

        $description = $config->upgradeCommandDescription();

        if ($description === null) {
            return false;
        }

        $this->executeUpgradeCommandDescription($description);

        return true;
    }

    protected function descriptorHasSteps(UpgradeConfigInterface $config): bool
    {
        return count($config->steps()) > 0;
    }

    protected function descriptorChoiceSummary(array $descriptor, UpgradeConfigInterface $config): ?string
    {
        $key = $descriptor['key'] ?? null;

        if ($key === null) {
            return null;
        }

        if (array_key_exists($key, $this->descriptorChoiceSummaries)) {
            return $this->descriptorChoiceSummaries[$key];
        }

        if (! $config instanceof UpgradeConfigSummaryInterface) {
            return $this->descriptorChoiceSummaries[$key] = null;
        }

        $summary = $config->upgradeCommandSummary();

        if (! is_string($summary)) {
            return $this->descriptorChoiceSummaries[$key] = null;
        }

        $summary = trim($summary);

        if ($summary === '') {
            return $this->descriptorChoiceSummaries[$key] = null;
        }

        return $this->descriptorChoiceSummaries[$key] = $summary;
    }

    protected function outputDescriptorSummaryList(array $summaries): void
    {
        if (empty($summaries)) {
            return;
        }

        $this->newLine();
        $this->line('  <fg=blue>Available upgrade paths</>');

        foreach ($summaries as $entry) {
            $labelColor = $entry['is_current'] ? 'green' : 'yellow';
            $label = sprintf('<fg=%s>%s</>', $labelColor, $entry['label']);
            $this->line(sprintf('    %s <fg=gray>—</> %s', $label, $entry['summary']));
        }

        $this->newLine();
    }

    protected function executeUpgradeCommandDescription(?callable $description): void
    {
        if ($description === null) {
            return;
        }

        try {
            $description($this);
        } catch (\ArgumentCountError|\TypeError $exception) {
            if ($description instanceof \Closure) {
                $description->call($this);

                return;
            }

            $description();
        }
    }

    protected function availableVersionDescriptors(): array
    {
        if ($this->availableConfigCache !== null) {
            return $this->availableConfigCache;
        }

        $filesystem = new Filesystem();

        $descriptors = [];

        foreach ($filesystem->directories(__DIR__) as $directory) {
            $basename = basename($directory);
            $normalizedKey = $this->normalizeDirectoryKey($basename);

            if ($normalizedKey === null) {
                continue;
            }

            $configPath = $directory.DIRECTORY_SEPARATOR.'UpgradeCommandConfig.php';

            if (! $filesystem->exists($configPath)) {
                continue;
            }

            $segments = $this->versionKeySegments($normalizedKey);

            if (empty($segments)) {
                continue;
            }

            $descriptors[$normalizedKey] = [
                'key' => $normalizedKey,
                'directory' => $basename,
                'namespace' => str_replace('-', '_', $normalizedKey),
                'label' => $normalizedKey,
                'version' => ltrim($normalizedKey, 'v'),
                'segments' => $segments,
                'comparable' => $this->segmentsToComparable($segments),
            ];
        }

        return $this->availableConfigCache = $descriptors;
    }

    protected function normalizeDirectoryKey(string $directory): ?string
    {
        $trimmed = strtolower($directory);

        if (preg_match('/^v\d+(?:[-_]\d+)*$/', $trimmed) !== 1) {
            return null;
        }

        return str_replace('_', '-', $trimmed);
    }

    protected function normalizeVersionKey(?string $version): ?string
    {
        if ($version === null) {
            return null;
        }

        $trimmed = trim(strtolower($version));

        if ($trimmed === '') {
            return null;
        }

        preg_match_all('/\d+/', $trimmed, $matches);

        if (empty($matches[0])) {
            return null;
        }

        return 'v'.implode('-', $matches[0]);
    }

    protected function sortDescriptors(array $descriptors, string $direction = 'asc'): array
    {
        $list = array_values($descriptors);

        usort($list, function (array $a, array $b) use ($direction) {
            $comparison = version_compare($a['comparable'], $b['comparable']);

            return $direction === 'desc' ? -$comparison : $comparison;
        });

        return $list;
    }

    protected function buildChoiceLabel(array $descriptor, bool $isCurrent): string
    {
        $label = $descriptor['label'];

        if ($isCurrent) {
            $label .= ' (current)';
        }

        return $label;
    }

    protected function detectCurrentVersionKey(array $available): ?string
    {
        $installedPretty = $this->installedBackpackPrettyVersion();

        if ($installedPretty === null) {
            return null;
        }

        foreach ($this->possibleKeysForVersion($installedPretty) as $candidate) {
            if (isset($available[$candidate])) {
                return $candidate;
            }
        }

        return null;
    }

    protected function installedBackpackPrettyVersion(): ?string
    {
        try {
            if (! InstalledVersions::isInstalled('backpack/crud')) {
                return null;
            }
        } catch (OutOfBoundsException $exception) {
            return null;
        }

        try {
            $version = InstalledVersions::getPrettyVersion('backpack/crud');

            if ($version === null) {
                $version = InstalledVersions::getVersion('backpack/crud');
            }

            return $version ?: null;
        } catch (OutOfBoundsException $exception) {
            return null;
        }
    }

    protected function possibleKeysForVersion(string $version): array
    {
        preg_match_all('/\d+/', $version, $matches);

        $segments = $matches[0] ?? [];

        if (empty($segments)) {
            return [];
        }

        $keys = [];

        for ($length = count($segments); $length > 0; $length--) {
            $slice = array_slice($segments, 0, $length);
            $keys[] = 'v'.implode('-', $slice);
        }

        return $keys;
    }

    protected function versionKeySegments(string $key): array
    {
        preg_match_all('/\d+/', $key, $matches);

        if (empty($matches[0])) {
            return [];
        }

        return array_map('intval', $matches[0]);
    }

    protected function segmentsToComparable(array $segments): string
    {
        return implode('.', array_map(static function ($segment) {
            return (string) (int) $segment;
        }, $segments));
    }

    protected function manuallyLoadConfigDirectory(array $descriptor): void
    {
        $filesystem = new Filesystem();
        $basePath = __DIR__.DIRECTORY_SEPARATOR.$descriptor['directory'];

        $configPath = $basePath.DIRECTORY_SEPARATOR.'UpgradeCommandConfig.php';

        if ($filesystem->exists($configPath)) {
            require_once $configPath;
        }

        $stepsPath = $basePath.DIRECTORY_SEPARATOR.'Steps';

        if (! $filesystem->isDirectory($stepsPath)) {
            return;
        }

        foreach ($filesystem->allFiles($stepsPath) as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            if (strtolower($file->getExtension()) !== 'php') {
                continue;
            }

            require_once $file->getRealPath();
        }
    }

    protected function hasExpectedBackpackVersion(UpgradeContext $context, UpgradeConfigInterface $config): bool
    {
        $targetConstraint = $config::backpackCrudRequirement();
        $targetMajor = $this->extractFirstInteger($targetConstraint);

        $composerConstraint = $context->composerRequirement('backpack/crud');

        if ($composerConstraint === null) {
            return false;
        }

        $composerMajor = $this->extractFirstInteger($composerConstraint);

        if ($targetMajor !== null && ($composerMajor === null || $composerMajor < $targetMajor)) {
            return false;
        }

        $installedMajor = $context->packageMajorVersion('backpack/crud');

        if ($installedMajor === null) {
            return false;
        }

        if ($targetMajor !== null && $installedMajor < $targetMajor) {
            return false;
        }

        return true;
    }
}
