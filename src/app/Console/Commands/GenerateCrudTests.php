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
        $this->info('Discovering CRUD controllers and generating tests...');
        $this->line('This may take a moment depending on the number of controllers and operations.');

        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]]);

        $this->callSilent('migrate');

        $controllers = collect($this->discoverControllers());

        if ($controllers->isEmpty()) {
            $this->warn('No CRUD controllers were discovered.');

            return self::SUCCESS;
        }

        if ($filter = $this->option('controller')) {
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
     * Discover CRUD controllers using configured paths.
     */
    protected function discoverControllers(): array
    {
        $paths = (array) config('backpack.testing.controllers_path', [app_path('Http/Controllers')]);

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

        $types = $this->option('type') ? [$this->option('type')] : ['feature'];

        // Probe whether the controller can be initialized or we need conventions
        $probe = new CrudTestBuilder($controllerInfo, $operations->first());
        $usingConventions = $probe->usedConventions();

        foreach ($types as $type) {
            if ($usingConventions) {
                $this->warn("  Generating {$type} tests using conventions...");
            } else {
                $this->line("  Generating {$type} tests...");
            }

            $operations->each(function (string $operation) use ($controllerInfo, $type, $usingConventions) {
                $this->generateTestForOperation($controllerInfo, $operation, $type, $usingConventions);
            });
        }
    }

    /**
     * Get the namespace for default test artifacts.
     */
    protected function getDefaultsNamespace(string $type): string
    {
        return $type === 'feature'
            ? 'Tests\\Feature\\Backpack'
            : 'Tests\\Browser\\Backpack';
    }

    /**
     * Generate the test class for a controller operation.
     */
    protected function generateTestForOperation(array $controllerInfo, string $operation, string $type, bool $usingConventions = false): void
    {
        try {
            $builder = new CrudTestBuilder($controllerInfo, $operation);

            $config = $builder->getTestConfiguration();

            $className = $this->resolveClassName($controllerInfo, $operation);

            $controllerShortName = Str::replaceLast('Controller', '', $controllerInfo['short_name']);
            $controllerRelPath = $this->getRelativeNamespace($controllerInfo['class']);

            $namespace = $type === 'feature'
                ? 'Tests\\Feature\\'.$controllerRelPath
                : 'Tests\\Browser\\'.$controllerRelPath;

            // 1. Ensure Default Traits and Base exist (in consolidated folder)
            $defaultsNamespace = $this->getDefaultsNamespace($type);
            $this->ensureDefaultArtifactsExist($defaultsNamespace, $type);
            
            // Check if trait stub exists
            $traitStubName = strtolower($operation).'.stub';
            if (!$this->getStubContent($type.'/'.$traitStubName)) {
                $this->line("  ⏭️  Skipping {$operation} (no trait stub found)");
                return;
            }

            $traitName = 'Default'.Str::studly($operation).'Tests';

            // Ensure the specific trait for this operation exists (beyond the default 5) if needed
            // Actually ensureDefaultArtifactsExist handles standard ones. 
            // If it's a custom operation with a trait stub but not in standard 5, we should ensure it exists.
            $this->ensureTraitExists($defaultsNamespace, $operation, $type);

            // 2. Ensure Controller Specific Base exists
            $baseClassName = 'TestBase'; 
            $this->ensureControllerTestBaseExists($namespace, $baseClassName, $controllerInfo, $config, $type, $usingConventions);

            // 3. Generate the Operation Test Class
            $filePath = $this->determineOutputPath($className, $controllerRelPath, $type);

            if ($this->shouldSkipExisting($filePath)) {
                $this->line("  ⏭️  Skipping {$operation} (file exists, use --force to overwrite)");

                return;
            }

            $traitName = 'Default'.Str::studly($operation).'Tests';

            $stub = $this->getStubContent($type.'/test_class.stub');

            $replacements = [
                'DummyNamespace' => $namespace,
                'DummyClass' => $className,
                'DummyBaseClass' => $baseClassName, // Short name e.g. TestBase
                'DummyTrait' => '\\'.$defaultsNamespace.'\\'.$traitName, // FQCN with leading slash for direct use
            ];

            $content = str_replace(array_keys($replacements), array_values($replacements), $stub);

            File::ensureDirectoryExists(dirname($filePath));
            File::put($filePath, $content);

            $this->generatedFiles[$type][] = $filePath;

            $this->line("  ✅ Generated {$operation} test: {$filePath}");
        } catch (\Throwable $e) {
            $this->error("  ❌ Failed generating {$operation}: {$e->getMessage()}");
        }
    }

    protected function ensureDefaultArtifactsExist(string $namespace, string $type): void
    {
        $basePath = $type === 'feature' ? base_path('tests/Feature') : base_path('tests/Browser');
        $relativePath = str_replace(['Tests\\Feature\\', 'Tests\\Browser\\'], '', $namespace);
        $relativePath = trim($relativePath, '\\');
        
        $targetDir = $basePath . ($relativePath ? DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relativePath) : '');

        if (!File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        // 1. Generate DefaultTestBase
        $baseClassName = 'DefaultTestBase';
        $itemPath = $targetDir . DIRECTORY_SEPARATOR . $baseClassName . '.php';

        if (! in_array($itemPath, $this->generatedBaseClasses)) {
            if (!File::exists($itemPath) || $this->option('force')) {
                $stub = $this->getStubContent($type.'/default_base.stub');
                $content = str_replace('DummyNamespace', $namespace, $stub);
                File::put($itemPath, $content);
                $this->line("  ✅ Generated Default Base: {$itemPath}");
            }
            $this->generatedBaseClasses[] = $itemPath; // Mark as processed
        }

        // 2. Generate Default Traits
        $operations = ['List', 'Create', 'Update', 'Show', 'Delete'];
        foreach ($operations as $op) {
            $this->ensureTraitExists($namespace, $op, $type);
        }
    }
    
    protected function ensureTraitExists(string $namespace, string $operation, string $type): void
    {
        $basePath = $type === 'feature' ? base_path('tests/Feature') : base_path('tests/Browser');
        $relativePath = str_replace(['Tests\\Feature\\', 'Tests\\Browser\\'], '', $namespace);
        $relativePath = trim($relativePath, '\\');
        
        $targetDir = $basePath . ($relativePath ? DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relativePath) : '');

        $traitName = 'Default'.Str::studly($operation).'Tests';
        $traitPath = $targetDir . DIRECTORY_SEPARATOR . $traitName . '.php';

        if (in_array($traitPath, $this->generatedBaseClasses)) {
            return;
        }

        if (!File::exists($traitPath) || $this->option('force')) {
            $stubName = strtolower($operation).'.stub';
            $stub = $this->getStubContent($type.'/'.$stubName);
            if ($stub) {
                $content = str_replace(['DummyNamespace', 'DummyTrait'], [$namespace, $traitName], $stub);
                File::put($traitPath, $content);
                $this->line("  ✅ Generated Default Trait: {$traitPath}");
            }
        }
        
        $this->generatedBaseClasses[] = $traitPath;
    }

    protected function ensureControllerTestBaseExists(string $namespace, string $className, array $controllerInfo, array $config, string $type, bool $usingConventions = false): void
    {
        $controllerRelPath = $this->getRelativeNamespace($controllerInfo['class']);
        $filePath = $this->determineOutputPath($className, $controllerRelPath, $type);

        if (in_array($filePath, $this->generatedBaseClasses)) {
            return;
        }

        if (File::exists($filePath) && !$this->option('force')) {
            $this->generatedBaseClasses[] = $filePath;
            return;
        }

        $defaultsNamespace = $this->getDefaultsNamespace($type);
        $defaultBaseClass = '\\'.$defaultsNamespace.'\\DefaultTestBase';

        $stub = $this->getStubContent($type.'/controller_base.stub');
        
        $routeSegment = $this->normalizeRoute($config['route'] ?? '');

        $replacements = [
            'DummyNamespace' => $namespace,
            'DummyClass' => $className,
            'DummyBaseClass' => $defaultBaseClass,
            'DummyControllerClass' => class_basename($config['controller']),
            'DummyController' => $config['controller'],
            'DummyModelClass' => class_basename($config['model']),
            'DummyModel' => $config['model'],
            'DummyRoute' => $this->escapeString($routeSegment),
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);

        if ($usingConventions) {
            $comment = <<<'COMMENT'
/**
 * NOTE: This test configuration was generated using naming conventions because
 * the CrudController could not be initialized. Please verify that the model,
 * route, and controller values below are correct before running your tests.
 */
COMMENT;
            $content = str_replace('class '.$className, $comment."\n".'class '.$className, $content);
        }

        File::ensureDirectoryExists(dirname($filePath));
        File::put($filePath, $content);
        $this->line("  ✅ Generated Controller Base: {$filePath}");

        $this->generatedBaseClasses[] = $filePath;
    }

    protected function getStubContent(string $name): string
    {
        $path = $this->getStubPath($name);
        return File::exists($path) ? File::get($path) : '';
    }

    /**
     * Decide whether to skip writing a class if it already exists.
     */
    protected function shouldSkipExisting(string $filePath): bool
    {
        if (! File::exists($filePath) || $this->option('force')) {
            return false;
        }

        return true;
    }



    /**
     * Escape a string for inclusion inside single quotes.
     */
    protected function escapeString(string $value): string
    {
        return addslashes($value);
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
    protected function determineOutputPath(string $className, ?string $controllerName = null, ?string $type = null): string
    {
        // Use provided type or fallback to option (for backward compatibility if method called elsewhere)
        $testType = $type ?? $this->option('type');

        $baseDir = $testType === 'feature'
            ? base_path('tests/Feature')
            : base_path('tests/Browser');

        if ($controllerName) {
            $pathFn = fn ($path) => str_replace('\\', DIRECTORY_SEPARATOR, $path);
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
            resource_path('views/vendor/backpack/crud/stubs/testing/'),
            __DIR__.'/../../../resources/stubs/testing/',
        ];

        foreach ($searchPaths as $basePath) {
            // If framework is defined and not default, try to find framework-specific stub
            if ($framework && $framework !== 'phpunit') {
                // First try nested directory: frameworks/{framework}/{stub}
                $namespaced = $basePath.$framework.'/'.$name;
                if (File::exists($namespaced)) {
                    return $namespaced;
                }

                // Then try prefixed: {framework}-{stub}
                $prefixed = $basePath.$framework.'-'.$name;
                if (File::exists($prefixed)) {
                    return $prefixed;
                }
            }

            if (File::exists($basePath.$name)) {
                return $basePath.$name;
            }
        }

        return __DIR__.'/../../../resources/stubs/testing/'.$name;
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
