<?php

namespace Backpack\CRUD\app\Console\Commands;

use Backpack\CRUD\app\Library\CrudTesting\CrudControllerDiscovery;
use Backpack\CRUD\app\Library\CrudTesting\CrudTestBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateCrudTests extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backpack:tests
                            {--controller= : Only generate tests for the given controller class name}
                            {--operation= : Only generate tests for the given CRUD operation}
                            {--force : Overwrite existing test classes}
                            {--dry-run : Show what would be generated without writing files}';

    /**
     * The console command description.
     */
    protected $description = 'Generate browser tests for discovered Backpack CRUD controllers.';

    public function handle(): int
    {
        $controllers = collect($this->discoverControllers());

        if ($controllers->isEmpty()) {
            $this->warn('No CRUD controllers were discovered.');

            return self::SUCCESS;
        }

        if ($filter = $this->option('controller')) {
            $controllers = $controllers->filter(function (array $controller) use ($filter) {
                return Str::contains($controller['class'], $filter);
            });

            if ($controllers->isEmpty()) {
                $this->error("No controllers match filter '{$filter}'.");

                return self::FAILURE;
            }
        }

        $operationFilter = $this->option('operation');

        foreach ($controllers as $controllerInfo) {
            $this->generateTestsForController($controllerInfo, $operationFilter);
        }

        $this->info('Test generation finished.');

        return self::SUCCESS;
    }

    /**
     * Discover CRUD controllers using configured paths.
     */
    protected function discoverControllers(): array
    {
        $paths = config('backpack.crud-testing.discovery_paths', [app_path('Http/Controllers')]);

        return CrudControllerDiscovery::discover($paths);
    }

    /**
     * Generate tests for a specific controller.
     */
    protected function generateTestsForController(array $controllerInfo, ?string $operationFilter = null): void
    {
        $this->line('');
        $this->info('Generating tests for '.$controllerInfo['short_name']);

        $operations = collect($controllerInfo['operations']);

        if ($operationFilter) {
            $operations = $operations->filter(fn ($operation) => $operation === $operationFilter);
        }

        if ($operations->isEmpty()) {
            $this->warn('  No operations queued for generation.');

            return;
        }

        $operations->each(function (string $operation) use ($controllerInfo) {
            if (! $this->operationEnabled($operation)) {
                $this->line("  ⏭️  Skipping {$operation} (disabled in configuration)");

                return;
            }

            $this->generateTestForOperation($controllerInfo, $operation);
        });
    }

    /**
     * Generate the test class for a controller operation.
     */
    protected function generateTestForOperation(array $controllerInfo, string $operation): void
    {
        try {
            $builder = new CrudTestBuilder($controllerInfo, $operation);
            $config = $builder->getTestConfiguration();
            $methods = $builder->generateTestMethods();

            if (empty($methods)) {
                $this->line("  ⏭️  Skipping {$operation} (no test methods generated)");

                return;
            }

            $className = $this->resolveClassName($controllerInfo, $operation);
            $namespace = config('backpack.crud-testing.generation.namespace', 'Tests\\Browser\\Crud');
            $operationConfig = $this->extractOperationConfig($config);
            $routeSegment = $this->normalizeRoute($config['route'] ?? '');

            $testClass = $this->renderTestClass([
                'namespace' => $namespace,
                'class' => $className,
                'controller' => $config['controller'],
                'model' => $config['model'],
                'route' => $routeSegment,
                'operation' => $operation,
                'operation_config' => $operationConfig,
                'methods' => $methods,
            ]);

            $filePath = $this->determineOutputPath($className);

            if ($this->shouldSkipExisting($filePath)) {
                $this->line("  ⏭️  Skipping {$operation} (file exists, use --force to overwrite)");

                return;
            }

            if ($this->option('dry-run')) {
                $this->line("  📝 Would write {$filePath}");

                return;
            }

            File::ensureDirectoryExists(dirname($filePath));
            File::put($filePath, $testClass);

            $this->line("  ✅ Generated {$operation} test: {$filePath}");
        } catch (\Throwable $e) {
            $this->error("  ❌ Failed generating {$operation}: {$e->getMessage()}");
        }
    }

    /**
     * Determine if an operation is enabled by configuration.
     */
    protected function operationEnabled(string $operation): bool
    {
        $coverage = config('backpack.crud-testing.coverage.operations', []);

        return ! array_key_exists($operation, $coverage) || (bool) $coverage[$operation];
    }

    /**
     * Decide whether to skip writing a class if it already exists.
     */
    protected function shouldSkipExisting(string $filePath): bool
    {
        if (! File::exists($filePath)) {
            return false;
        }

        if ($this->option('force')) {
            return false;
        }

        return ! config('backpack.crud-testing.generation.overwrite_existing', false);
    }

    /**
     * Render the test class contents.
     */
    protected function renderTestClass(array $data): string
    {
        $stubPath = __DIR__.'/../../../resources/stubs/crud-testing/browser-test.stub';
        $stub = File::get($stubPath);

        $operationConfigProperty = $this->renderOperationConfigProperty($data['operation_config']);
        $methodsBlock = $this->renderMethodsBlock($data['methods']);

        $replacements = [
            'DummyNamespace' => $data['namespace'],
            'DummyClass' => $data['class'],
            'DummyController' => $data['controller'],
            'DummyModel' => $data['model'],
            'DummyRoute' => $this->escapeString($data['route']),
            'DummyOperationConfigProperty' => $operationConfigProperty,
            'DummyMethods' => $methodsBlock,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $stub);
    }

    /**
     * Render the property holding operation-specific configuration.
     */
    protected function renderOperationConfigProperty(array $config): string
    {
        $export = $this->exportArray($config, 2);

        return '    protected array $operationConfig = '.$export.';';
    }

    /**
     * Render all generated methods into a code block.
     */
    protected function renderMethodsBlock(array $methods): string
    {
        return collect($methods)
            ->map(fn ($method) => $this->renderMethod($method))
            ->filter()
            ->implode("\n\n");
    }

    /**
     * Render a single generated method.
     */
    protected function renderMethod(array $method): ?string
    {
        $name = $method['name'];
        $description = $method['description'] ?? 'Generated test.';
        $operation = $method['operation'];
        $testerMethod = $method['tester_method'] ?? null;

        if (! $testerMethod) {
            return $this->renderTodoMethod($name, $description);
        }

        $requiresEntries = (bool) ($method['requires_entries'] ?? false);
        $requiresEntry = (bool) ($method['requires_entry'] ?? false);

        $bodyLines = [];

        if ($requiresEntries) {
            $bodyLines[] = '$this->createTestEntries(config(\'backpack.crud-testing.operations.list_test_entries\', 5));';
        }

        if ($requiresEntry) {
            $bodyLines[] = '$entry = $this->createTestEntry();';
        }

        if (! empty($bodyLines)) {
            $bodyLines[] = '';
        }

        $useVariables = [];
        $callArguments = '$browser';

        if ($requiresEntry) {
            $useVariables[] = '$entry';
            $callArguments .= ', $entry->getKey()';
        }

        $useClause = empty($useVariables) ? '' : ' use ('.implode(', ', $useVariables).')';

        $bodyLines[] = '$this->browse(function (Browser $browser)'.$useClause.' {';
        $bodyLines[] = '    $this->loginAsAdmin($browser);';
        $bodyLines[] = "    \$tester = \$this->getOperationTester('{$operation}', \$this->operationConfig);";
        $bodyLines[] = "    \$tester->{$testerMethod}({$callArguments});";
        $bodyLines[] = '});';

        $body = $this->indentLines($bodyLines, 2);

        return <<<PHP
    /**
     * {$description}
     */
    public function {$name}(): void
    {
{$body}
    }
PHP;
    }

    /**
     * Render a fallback TODO method when metadata is incomplete.
     */
    protected function renderTodoMethod(string $name, string $description): string
    {
        $body = $this->indentLines([
            "\$this->markTestIncomplete('Generator could not determine implementation for {$name}.');",
        ], 2);

        return <<<PHP
    /**
     * {$description}
     */
    public function {$name}(): void
    {
{$body}
    }
PHP;
    }

    /**
     * Indent the provided lines by the given level.
     */
    protected function indentLines(array $lines, int $level = 1): string
    {
        $indent = str_repeat('    ', $level);

        return collect($lines)
            ->map(function ($line) use ($indent) {
                if ($line === '') {
                    return '';
                }

                return $indent.$line;
            })
            ->implode("\n");
    }

    /**
     * Export an array to PHP code using short array syntax.
     */
    protected function exportArray(array $value, int $indentLevel = 0): string
    {
        if ($value === []) {
            return '[]';
        }

        $indent = str_repeat('    ', $indentLevel);
        $nextIndent = str_repeat('    ', $indentLevel + 1);
        $isList = function_exists('array_is_list') ? array_is_list($value) : $this->isList($value);

        $lines = ['['];

        foreach ($value as $key => $item) {
            $line = $nextIndent;

            if (! $isList) {
                $line .= $this->exportKey($key).' => ';
            }

            $line .= $this->exportValue($item, $indentLevel + 1);
            $line .= ',';
            $lines[] = $line;
        }

        $lines[] = $indent.']';

        return implode("\n", $lines);
    }

    /**
     * Determine if the array is sequential when array_is_list() is unavailable.
     */
    protected function isList(array $value): bool
    {
        $expected = 0;

        foreach ($value as $key => $unused) {
            if ($key !== $expected) {
                return false;
            }

            $expected++;
        }

        return true;
    }

    /**
     * Export a key for short array syntax.
     */
    protected function exportKey($key): string
    {
        if (is_int($key)) {
            return (string) $key;
        }

        return '\''.addslashes((string) $key).'\'';
    }

    /**
     * Export a value to PHP code.
     */
    protected function exportValue($value, int $indentLevel): string
    {
        if (is_array($value)) {
            return $this->exportArray($value, $indentLevel);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_null($value)) {
            return 'null';
        }

        if (is_string($value)) {
            return '\''.addslashes($value).'\'';
        }

        return (string) $value;
    }

    /**
     * Escape a string for inclusion inside single quotes.
     */
    protected function escapeString(string $value): string
    {
        return addslashes($value);
    }

    /**
     * Extract the subset of configuration that operation testers need.
     */
    protected function extractOperationConfig(array $config): array
    {
        $keys = [
            'entity_name',
            'entity_name_plural',
            'columns',
            'filters',
            'buttons',
            'fields',
            'save_actions',
            'required_fields',
        ];

        $operationConfig = Arr::only($config, $keys);

        return array_filter($operationConfig, static function ($value) {
            return $value !== null;
        });
    }

    /**
     * Resolve the class name for a generated test.
     */
    protected function resolveClassName(array $controllerInfo, string $operation): string
    {
        $pattern = config('backpack.crud-testing.generation.class_name_pattern', '{controller}{operation}Test');
        $controllerBase = Str::replaceLast('CrudController', '', $controllerInfo['short_name']);
        $controllerBase = $controllerBase !== '' ? $controllerBase : $controllerInfo['short_name'];

        $replacements = [
            '{controller}' => $controllerBase,
            '{operation}' => Str::studly($operation),
        ];

        return strtr($pattern, $replacements);
    }

    /**
     * Decide where the generated file should be stored.
     */
    protected function determineOutputPath(string $className): string
    {
        $baseDir = config('backpack.crud-testing.generation.output_path', base_path('tests/Browser/Crud'));
        $baseDir = rtrim($baseDir, '\\/');

        return $baseDir.DIRECTORY_SEPARATOR.$className.'.php';
    }

    /**
     * Normalize the CRUD route to a relative segment.
     */
    protected function normalizeRoute(string $route): string
    {
        $route = trim($route, '/');
        $prefix = trim((string) config('backpack.base.route_prefix', 'admin'), '/');

        if ($route === '') {
            return '';
        }

        if ($prefix !== '' && Str::startsWith($route, $prefix.'/')) {
            return Str::after($route, $prefix.'/');
        }

        return $route;
    }
}
