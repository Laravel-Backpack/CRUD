<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;

class CheckOperationConfigFilesStep extends Step
{
    protected array $operationFiles = [
        'create.php',
        'form.php',
        'list.php',
        'reorder.php',
        'show.php',
        'update.php',
    ];

    public function title(): string
    {
        return 'Operation config files';
    }

    public function run(): StepResult
    {
        $issues = [];
        $checkedAny = false;

        foreach ($this->operationFiles as $filename) {
            $relativePath = 'config/backpack/operations/'.$filename;

            if (! $this->context()->fileExists($relativePath)) {
                continue;
            }

            $checkedAny = true;

            $publishedConfig = $this->loadConfigArray($this->context()->basePath($relativePath));
            $packageConfig = $this->loadConfigArray($this->packageConfigPath($filename));

            if ($publishedConfig === null || $packageConfig === null) {
                continue;
            }

            $missingKeys = array_diff(
                $this->flattenKeys($packageConfig),
                $this->flattenKeys($publishedConfig)
            );

            if (! empty($missingKeys)) {
                sort($missingKeys);

                $issues[] = sprintf('Add the missing keys to %s:', $relativePath);

                $preview = array_slice($missingKeys, 0, 10);

                foreach ($preview as $key) {
                    $issues[] = "- {$key}";
                }

                if (count($missingKeys) > count($preview)) {
                    $issues[] = sprintf('… %d more key(s) omitted.', count($missingKeys) - count($preview));
                }
            }
        }

        if (! $checkedAny) {
            return StepResult::skipped('Operation config files are not published.');
        }

        if (empty($issues)) {
            return StepResult::success('Published operation config files include the latest options.');
        }

        return StepResult::warning(
            'Copy the new configuration options into your published operation config files.',
            $issues
        );
    }

    private function loadConfigArray(string $path): ?array
    {
        if (! is_file($path)) {
            return null;
        }

        $data = include $path;

        return is_array($data) ? $data : null;
    }

    private function packageConfigPath(string $filename): string
    {
        return dirname(__DIR__, 6)
            .DIRECTORY_SEPARATOR.'config'
            .DIRECTORY_SEPARATOR.'backpack'
            .DIRECTORY_SEPARATOR.'operations'
            .DIRECTORY_SEPARATOR.$filename;
    }

    private function flattenKeys(array $config, string $prefix = ''): array
    {
        $keys = [];

        foreach ($config as $key => $value) {
            if (is_int($key)) {
                if (is_array($value)) {
                    $keys = array_merge($keys, $this->flattenKeys($value, $prefix));
                }

                continue;
            }

            $key = (string) $key;
            $fullKey = $prefix === '' ? $key : $prefix.'.'.$key;
            $keys[] = $fullKey;

            if (is_array($value)) {
                $keys = array_merge($keys, $this->flattenKeys($value, $fullKey));
            }
        }

        return array_values(array_unique($keys));
    }
}
