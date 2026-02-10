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
                            {--type= : The type of test to generate (browser or feature)}
                            {--framework=phpunit : The testing framework to use (phpunit or pest)}
                            {--force : Overwrite existing test classes}';

    /**
     * The console command description.
     */
    protected $description = 'Generate tests for discovered Backpack CRUD controllers.';
    
    /**
     * Operations that were skipped due to missing strategy.
     */
    protected array $skippedOperations = [];

    /**
     * The files that were generated.
     */
    protected array $generatedFiles = [];

    /**
     * Track generated base classes to avoid duplicates during single run.
     *
     * @var array
     */
    protected array $generatedBaseClasses = [];

    /**
     * Track models without factories to warn the user.
     *
     * @var array
     */
    protected array $modelsWithoutFactories = [];

    public function handle(): int
    {
        $controllers = collect($this->discoverControllers());

        if ($controllers->isEmpty()) {
            $this->warn('No CRUD controllers were discovered.');

            return self::SUCCESS;
        }

        if ($filter = $this->option('controller')) {
            // First try strict matching on the short name or full class name
            $exactMatches = $controllers->filter(function (array $controller) use ($filter) {
                return $controller['short_name'] === $filter || $controller['class'] === $filter;
            });

            if ($exactMatches->isNotEmpty()) {
                $controllers = $exactMatches;
            } else {
                $controllers = $controllers->filter(function (array $controller) use ($filter) {
                    return Str::contains($controller['class'], $filter);
                });

                if ($controllers->isEmpty()) {
                    $this->error("No controllers match filter '{$filter}'.");

                    return self::FAILURE;
                }
            }
        }

        $operationFilter = $this->option('operation');

        foreach ($controllers as $controllerInfo) {
            $this->generateTestsForController($controllerInfo, $operationFilter);
        }

        if (! empty($this->skippedOperations)) {
            $this->line('');
            $this->warn('Tests not generated for the following operations (no test strategy defined):');
            foreach ($this->skippedOperations as $key => $operations) {
                $this->line("- {$key}: ".implode(', ', $operations));
            }
        }

        if (! empty($this->modelsWithoutFactories)) {
            $this->line('');
            $this->warn('Some tests were generated but marked as skipped because the model factory is missing:');
            foreach (array_unique($this->modelsWithoutFactories) as $model) {
                $this->line("- {$model}");
            }
            $this->line('  Please explicitly define a factory for these models or implement the tests manually.');
        }

        $this->info('Test generation finished.');

        if (! empty($this->generatedFiles)) {
            $this->info('To run the tests call: php artisan test');
        }

        return self::SUCCESS;
    }

    /**
     * Run the generated tests.
     */
    protected function runGeneratedTests(): void
    {
        $featureTests = $this->generatedFiles['feature'] ?? [];
        $browserTests = $this->generatedFiles['browser'] ?? [];
        $framework = $this->option('framework');

        if (! empty($featureTests)) {
            $this->line('');
            $this->info("Running feature tests ({$framework})...");
            
            $binary = $framework === 'pest' ? 'pest' : 'phpunit';
            $binaryPath = base_path("vendor/bin/{$binary}");
            
            if (file_exists($binaryPath)) {
                $command = '"'.PHP_BINARY.'" "'.$binaryPath.'"';

                if (file_exists(base_path('phpunit.xml'))) {
                    $command .= ' --configuration "'.base_path('phpunit.xml').'"';
                }

                $command .= ' '.implode(' ', array_map(fn ($f) => '"'.$f.'"', $featureTests));
                passthru($command);
            } elseif ($framework === 'phpunit' && $this->getApplication()->has('test')) {
                $this->call('test', ['args' => $featureTests]);
            } else {
                $this->error("Testing binary not found: {$binaryPath}");
            }
        }

        if (! empty($browserTests)) {
            $this->line('');
            $this->info('Running browser tests...');
            if ($this->getApplication()->has('dusk')) {
                $this->call('dusk', ['args' => $browserTests]);
            } else {
                $this->warn('Dusk command not found. Please run "php artisan dusk" manually.');
            }
        }
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
            $allowedOperations = explode(',', $operationFilter);
            $operations = $operations->filter(fn ($operation) => in_array($operation, $allowedOperations));
        }

        if ($operations->isEmpty()) {
            $this->warn('  No operations queued for generation.');

            return;
        }

        $types = $this->option('type') ? [$this->option('type')] : ['feature', 'browser'];

        foreach ($types as $type) {
            $this->line("  Generating {$type} tests...");
            
            $operations->each(function (string $operation) use ($controllerInfo, $type) {
                if (! $this->operationEnabled($operation)) {
                    $this->line("  ⏭️  Skipping {$operation} (disabled in configuration)");
    
                    return;
                }
    
                $this->generateTestForOperation($controllerInfo, $operation, $type);
            });
        }
    }

    /**
     * Generate the test class for a controller operation.
     */
    protected function generateTestForOperation(array $controllerInfo, string $operation, string $type): void
    {
        try {
            $builder = new CrudTestBuilder($controllerInfo, $operation);

            $config = $builder->getTestConfiguration();

            // Check for factory existence
            $model = $config['model'] ?? null;
            if ($model && class_exists($model) && ! method_exists($model, 'factory')) {
                $this->modelsWithoutFactories[] = $model;
            }
            
            // Check for stub override
            $stubName = "{$type}-{$operation}.stub";
            $operationStubPath = $this->getStubPath('operations/'.$stubName);
            
            if (! File::exists($operationStubPath)) {
                $this->skippedOperations[$controllerInfo['short_name']][] = "$operation ($type)";
                return;
            }

            $methods = File::get($operationStubPath);

            if (empty($methods)) {
                $this->line("  ⏭️  Skipping {$operation} (no test methods generated)");

                return;
            }

            // Replace __MARK_TEST_AS_SKIPPED__ placeholder
            $hasFactory = $model && class_exists($model) && method_exists($model, 'factory') && file_exists(database_path('factories/'.class_basename($model).'Factory.php'));
            
            if ($hasFactory) {
                // If the model has a factory, remove the placeholder
                $methods = str_replace('__MARK_TEST_AS_SKIPPED__', '', $methods);
            } else {
                // If no factory, mark the test as skipped
                $methods = str_replace(
                    '__MARK_TEST_AS_SKIPPED__', 
                    '$this->markTestSkipped(\'Factory not found for model \' . $this->model);', 
                    $methods
                );
            }

            $className = $this->resolveClassName($controllerInfo, $operation);

            $controllerShortName = Str::replaceLast('Controller', '', $controllerInfo['short_name']);
            $controllerRelPath = $this->getRelativeNamespace($controllerInfo['class']);
            
            $namespace = $type === 'feature'
                ? 'Tests\\Feature\\'.$controllerRelPath
                : 'Tests\\Browser\\'.$controllerRelPath;
            
            $baseClassName = $controllerShortName.'TestBase';
            $this->ensureBaseTestClassExists($namespace, $baseClassName, $controllerInfo, $config, $type);

            $operationConfig = $this->extractOperationConfig($config);
            
            // SIMPLIFICATION: Remove detailed config to rely on runtime introspection
            // This makes the test file smaller and more resilient to controller changes
            unset($operationConfig['columns'], $operationConfig['fields'], $operationConfig['filters'], $operationConfig['buttons']);

            $routeSegment = $this->normalizeRoute($config['route'] ?? '');

            $testClass = $this->renderTestClass([
                'namespace' => $namespace,
                'class' => $className,
                'base_class' => $baseClassName,
                'controller' => $config['controller'],
                'model' => $config['model'],
                'route' => $routeSegment,
                'operation' => $operation,
                'operation_config' => $operationConfig,
                'methods' => $methods,
            ]);

            $filePath = $this->determineOutputPath($className, $controllerRelPath, $type);

            if ($this->shouldSkipExisting($filePath)) {
                $this->line("  ⏭️  Skipping {$operation} (file exists, use --force to overwrite)");

                return;
            }

            File::ensureDirectoryExists(dirname($filePath));
            File::put($filePath, $testClass);

            $this->generatedFiles[$type][] = $filePath;

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
     * Ensure the base test class exists for the controller.
     */
    protected function ensureBaseTestClassExists(string $namespace, string $className, array $controllerInfo, array $config, string $type): void
    {
        $controllerName = $this->getRelativeNamespace($controllerInfo['class']);
        $filePath = $this->determineOutputPath($className, $controllerName, $type);

        if (in_array($filePath, $this->generatedBaseClasses)) {
            return;
        }

        if ($this->shouldSkipExisting($filePath)) {
             return;
        }

        $stubName = $type === 'feature' ? 'feature-test-base.stub' : 'browser-test-base.stub';
        $stubPath = $this->getStubPath($stubName);
        
        if (! File::exists($stubPath)) {
            return;
        }
        
        $stub = File::get($stubPath);

        $routeSegment = $this->normalizeRoute($config['route'] ?? '');

        $replacements = [
            'DummyNamespace' => $namespace,
            'DummyBaseClass' => $className,
            'DummyControllerClass' => class_basename($config['controller']),
            'DummyController' => $config['controller'],
            'DummyModelClass' => class_basename($config['model']),
            'DummyModel' => $config['model'],
            'DummyRoute' => $this->escapeString($routeSegment),
            'DummyEntityNamePlural' => $this->escapeString($config['entity_name_plural'] ?? ''),
            'DummyEntityName' => $this->escapeString($config['entity_name'] ?? ''),
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);

        File::ensureDirectoryExists(dirname($filePath));
        File::put($filePath, $content);
        $this->generatedBaseClasses[] = $filePath;
        $this->line("  ✅ Generated Base Test: {$filePath}");
    }

    /**
     * Render the test class contents.
     */
    protected function renderTestClass(array $data): string
    {
        $type = $this->option('type');
        $stubName = $type === 'feature' ? 'feature-test.stub' : 'browser-test.stub';
        $stubPath = $this->getStubPath($stubName);
        $stub = File::get($stubPath);

        $operationConfigProperty = $this->renderOperationConfigProperty($data['operation_config']);
        
        $methodsBlock = $data['methods'];

        $replacements = [
            'DummyNamespace' => $data['namespace'],
            'DummyClass' => $data['class'],
            'DummyBaseClass' => $data['base_class'] ?? ($type === 'feature' ? 'CrudFeatureTestCase' : 'CrudBrowserTestCase'),
            'DummyControllerClass' => class_basename($data['controller']),
            'DummyController' => $data['controller'],
            'DummyModelClass' => class_basename($data['model']),
            'DummyModel' => $data['model'],
            'DummyRoute' => $this->escapeString($data['route']),
            'DummyOperationConfigProperty' => $operationConfigProperty,
            'DummyOperation' => $data['operation'],
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
        if ($value instanceof \Closure) {
            return 'function() { return "Closure"; }';
        }

        if (is_object($value)) {
            return "'Object: ".get_class($value)."'";
        }

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
        return Str::studly($operation).'Test';
    }

    /**
     * Decide where the generated file should be stored.
     */
    protected function determineOutputPath(string $className, string $controllerName = null, string $type = null): string
    {
        // Use provided type or fallback to option (for backward compatibility if method called elsewhere)
        $testType = $type ?? $this->option('type');
        
        $baseDir = $testType === 'feature'
            ? base_path('tests/Feature')
            : base_path('tests/Browser');

        if ($controllerName) {
            $pathFn = fn($path) => str_replace('\\', DIRECTORY_SEPARATOR, $path);
            $baseDir .= DIRECTORY_SEPARATOR.$pathFn($controllerName);
        }

        if (! File::isDirectory($baseDir)) {
            File::makeDirectory($baseDir, 0755, true);
        }

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

    /**
     * Get the path to a stub file, respecting the chosen framework and published stubs.
     */
    protected function getStubPath(string $name): string
    {
        $framework = $this->option('framework');
        
        $searchPaths = [
            resource_path('views/vendor/backpack/crud/stubs/crud-testing/'),
            __DIR__.'/../../../resources/stubs/crud-testing/',
        ];

        foreach ($searchPaths as $basePath) {
            // If framework is defined and not default, try to find framework-specific stub
            if ($framework && $framework !== 'phpunit') {
                // First try nested directory: frameworks/{framework}/{stub}
                $namespaced = $basePath . $framework . '/' . $name;
                if (File::exists($namespaced)) {
                    return $namespaced;
                }
                
                // Then try prefixed: {framework}-{stub}
                $prefixed = $basePath . $framework . '-' . $name;
                if (File::exists($prefixed)) {
                    return $prefixed;
                }
            }

            if (File::exists($basePath . $name)) {
                return $basePath . $name;
            }
        }
        
        return __DIR__.'/../../../resources/stubs/crud-testing/' . $name;
    }

    /**
     * Get the relative namespace for the test class based on controller structure.
     */
    protected function getRelativeNamespace(string $controllerClass): string
    {
        $rootNamespace = 'App\\Http\\Controllers\\';
        
        if (Str::startsWith($controllerClass, $rootNamespace)) {
            $relative = Str::after($controllerClass, $rootNamespace);
        } else {
            $relative = class_basename($controllerClass);
        }

        return Str::replaceLast('Controller', '', $relative);
    }
}
