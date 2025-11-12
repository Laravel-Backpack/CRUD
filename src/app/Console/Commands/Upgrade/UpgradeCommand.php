<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade;

use Backpack\CRUD\app\Console\Commands\Traits\PrettyCommandOutput;
use Backpack\CRUD\app\Console\Commands\Upgrade\Concerns\ExtractsFirstInteger;
use Illuminate\Console\Command;
use RuntimeException;

class UpgradeCommand extends Command
{
    use PrettyCommandOutput, ExtractsFirstInteger;

    protected $signature = 'backpack:upgrade
                                {version=7 : Target Backpack version to prepare for.}
                                {--stop-on-failure : Stop executing once a step fails.}
                                {--format=cli : Output format (cli, json).}
                                {--debug : Show debug information for executed processes.}';

    protected $description = 'Run opinionated upgrade checks to help you move between Backpack major versions.';

    public function handle(): int
    {
        $format = $this->outputFormat();

        if (! in_array($format, ['cli', 'json'], true)) {
            $this->errorBlock(sprintf('Unknown output format "%s". Supported formats: cli, json.', $format));

            return Command::INVALID;
        }

        $version = (string) $this->argument('version');
        $majorVersion = $this->extractMajorVersion($version);

        try {
            $config = $this->resolveConfigForMajor($majorVersion);
        } catch (RuntimeException $exception) {
            $this->errorBlock($exception->getMessage());

            return Command::INVALID;
        }

        $stepClasses = $config->steps();
        if (empty($stepClasses)) {
            $this->errorBlock("No automated checks registered for Backpack v{$majorVersion}.");

            return Command::INVALID;
        }

        $context = new UpgradeContext($majorVersion, addons: $config->addons());

        $this->infoBlock("Backpack v{$majorVersion} upgrade assistant", 'upgrade');

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

            if ($this->shouldOfferFix($step, $result)) {
                $question = trim($step->fixMessage($result));
                $question = $question !== '' ? $question : 'Apply automatic fix?';
                $applyFix = $this->confirm('  '.$question, false);

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

            $results[] = [
                'step' => $stepClass,
                'result' => $result,
            ];

            if ($this->option('stop-on-failure') && $result->status->isFailure()) {
                break;
            }
        }

        $expectedVersionInstalled = $this->hasExpectedBackpackVersion($context, $config);

        return $this->outputSummary($majorVersion, $results, $expectedVersionInstalled, $config);
    }

    protected function outputSummary(
        string $majorVersion,
        array $results,
        bool $expectedVersionInstalled = false,
        ?UpgradeConfigInterface $config = null
    ): int {
        $format = $this->outputFormat();

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

        if ($format === 'json') {
            $payload = [
                'version' => $majorVersion,
                'results' => collect($results)->map(function ($entry) {
                    /** @var StepResult $result */
                    $result = $entry['result'];

                    return [
                        'step' => $entry['step'],
                        'status' => $result->status->value,
                        'summary' => $result->summary,
                        'details' => $result->details,
                    ];
                })->values()->all(),
            ];

            $this->newLine();
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $hasFailure ? Command::FAILURE : Command::SUCCESS;
        }

        $this->newLine();
        $this->infoBlock('Summary', 'done');

        $this->note(sprintf('Checked %d upgrade steps.', count($results)), 'gray');

        if ($hasFailure) {
            $this->note('At least one step reported a failure. Review the messages above before continuing.', 'red', 'red');
        }

        if ($warnings->isNotEmpty()) {
            $this->note(sprintf('%d step(s) reported warnings.', $warnings->count()), 'yellow', 'yellow');
        }

        if (! $hasFailure && $warnings->isEmpty()) {
            $this->note('All checks passed, you are ready to continue with the manual steps from the upgrade guide.', 'green', 'green');
        }

        $postUpgradeCommands = [];

        if ($config !== null) {
            $postUpgradeCommands = ($config)::postUpgradeCommands();
        }

        if ($expectedVersionInstalled && ! $hasFailure && ! empty($postUpgradeCommands)) {
            $this->note("Now that you have v{$majorVersion} installed, don't forget to run the following commands:", 'green', 'green');

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
        if ($this->outputFormat() === 'json') {
            return false;
        }

        if (! $this->input->isInteractive()) {
            return false;
        }

        if (! in_array($result->status, [StepStatus::Warning, StepStatus::Failed], true)) {
            return false;
        }

        return $step->canFix($result);
    }

    protected function outputFormat(): string
    {
        $format = strtolower((string) $this->option('format'));

        return $format !== '' ? $format : 'cli';
    }

    protected function resolveConfigForMajor(string $majorVersion): UpgradeConfigInterface
    {
        $configProviderClass = sprintf('%s\\v%s\\UpgradeCommandConfig', __NAMESPACE__, $majorVersion);

        if (! class_exists($configProviderClass)) {
            throw new RuntimeException(sprintf(
                'Missing upgrade config provider for Backpack v%s. Please create %s.',
                $majorVersion,
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

        return $provider;
    }

    protected function extractMajorVersion(string $version): string
    {
        if (preg_match('/^(\d+)/', $version, $matches)) {
            return $matches[1];
        }

        return $version;
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
