<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade;

use Composer\InstalledVersions;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;

class UpgradeContext
{
    protected Filesystem $files;

    protected string $basePath;

    protected array $composerJson = [];

    protected array $searchCache = [];

    protected array $fileCache = [];

    protected array $defaultScanDirectories = [
        'app',
        'config',
        'resources/views',
        'resources/lang',
        'routes',
        'database/factories',
        'database/seeders',
    ];

    protected array $searchableExtensions = ['php'];

    protected array $addons = [];

    public function __construct(
        protected readonly string $targetVersion,
        ?Filesystem $filesystem = null,
        ?string $basePath = null,
        array $addons = []
    ) {
        $this->files = $filesystem ?? new Filesystem();
        $this->basePath = $this->normalizePath($basePath ?? $this->defaultBasePath());
        $this->addons = $addons;
    }

    public function targetVersion(): string
    {
        return $this->targetVersion;
    }

    public function addons(): array
    {
        return $this->addons;
    }

    public function basePath(string $path = ''): string
    {
        $path = ltrim($path, '/\\');

        return $this->normalizePath($this->basePath.($path !== '' ? '/'.$path : ''));
    }

    public function fileExists(string $relativePath): bool
    {
        return $this->files->exists($this->basePath($relativePath));
    }

    public function readFile(string $relativePath): ?string
    {
        if (isset($this->fileCache[$relativePath])) {
            return $this->fileCache[$relativePath];
        }

        $fullPath = $this->basePath($relativePath);

        if (! $this->files->exists($fullPath)) {
            return null;
        }

        try {
            return $this->fileCache[$relativePath] = $this->files->get($fullPath);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    public function composerJson(): array
    {
        if (! empty($this->composerJson)) {
            return $this->composerJson;
        }

        $content = $this->readFile('composer.json');

        if (! $content) {
            return $this->composerJson = [];
        }

        return $this->composerJson = json_decode($content, true) ?? [];
    }

    public function composerRequirement(string $package): ?string
    {
        $composer = $this->composerJson();

        return Arr::get($composer, "require.$package") ?? Arr::get($composer, "require-dev.$package");
    }

    public function composerRequirementSection(string $package): ?string
    {
        $composer = $this->composerJson();

        if (array_key_exists($package, $composer['require'] ?? [])) {
            return 'require';
        }

        if (array_key_exists($package, $composer['require-dev'] ?? [])) {
            return 'require-dev';
        }

        return null;
    }

    public function hasComposerPackage(string $package): bool
    {
        return $this->composerRequirement($package) !== null;
    }

    public function composerMinimumStability(): ?string
    {
        $composer = $this->composerJson();

        return $composer['minimum-stability'] ?? null;
    }

    public function installedPackageVersion(string $package): ?string
    {
        if (! InstalledVersions::isInstalled($package)) {
            return null;
        }

        return InstalledVersions::getVersion($package) ?? InstalledVersions::getPrettyVersion($package);
    }

    public function installedPackagePrettyVersion(string $package): ?string
    {
        if (! InstalledVersions::isInstalled($package)) {
            return null;
        }

        return InstalledVersions::getPrettyVersion($package);
    }

    public function packageMajorVersion(string $package): ?int
    {
        $pretty = $this->installedPackagePrettyVersion($package);

        if (! $pretty) {
            return null;
        }

        if (preg_match('/(\d+)/', $pretty, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    public function searchTokens(array $tokens, ?array $directories = null): array
    {
        sort($tokens);

        $directories = $directories ?? $this->defaultScanDirectories;

        $cacheKey = md5(json_encode([$tokens, $directories]));

        if (isset($this->searchCache[$cacheKey])) {
            return $this->searchCache[$cacheKey];
        }

        $results = array_fill_keys($tokens, []);

        foreach ($directories as $directory) {
            $absoluteDirectory = $this->resolvePath($directory);

            if (! $this->files->isDirectory($absoluteDirectory)) {
                continue;
            }

            foreach ($this->files->allFiles($absoluteDirectory) as $file) {
                $path = $file->getRealPath();

                if ($path === false) {
                    continue;
                }

                if ($this->shouldSkipFile($file->getFilename())) {
                    continue;
                }

                try {
                    $contents = $this->files->get($path);
                } catch (\Throwable $exception) {
                    continue;
                }

                foreach ($tokens as $token) {
                    if (str_contains($contents, $token)) {
                        $results[$token][] = $this->relativePath($path);
                    }
                }
            }
        }

        foreach ($results as $token => $paths) {
            $results[$token] = array_values(array_unique($paths));
        }

        return $this->searchCache[$cacheKey] = $results;
    }

    public function defaultScanDirectories(): array
    {
        return $this->defaultScanDirectories;
    }

    public function writeFile(string $relativePath, string $contents): bool
    {
        $fullPath = $this->basePath($relativePath);

        try {
            $this->files->ensureDirectoryExists(dirname($fullPath));
            $this->files->put($fullPath, $contents);
            $this->fileCache[$relativePath] = $contents;

            if ($relativePath === 'composer.json') {
                $this->composerJson = json_decode($contents, true) ?? [];
            }

            return true;
        } catch (\Throwable $exception) {
            return false;
        }
    }

    public function deleteFile(string $relativePath): bool
    {
        $fullPath = $this->basePath($relativePath);

        try {
            if (! $this->files->exists($fullPath)) {
                return true;
            }

            $this->files->delete($fullPath);
            unset($this->fileCache[$relativePath]);

            if ($relativePath === 'composer.json') {
                $this->composerJson = [];
            }

            return true;
        } catch (\Throwable $exception) {
            return false;
        }
    }

    public function updateComposerJson(callable $callback): bool
    {
        $composer = $this->composerJson();

        $updatedComposer = $composer;

        $callback($updatedComposer);

        if ($updatedComposer === $composer) {
            return true;
        }

        $encoded = json_encode($updatedComposer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($encoded === false) {
            return false;
        }

        return $this->writeFile('composer.json', $encoded.PHP_EOL);
    }

    public function relativePath(string $absolutePath): string
    {
        $normalized = $this->normalizePath($absolutePath);

        if (str_starts_with($normalized, $this->basePath.'/')) {
            return substr($normalized, strlen($this->basePath) + 1);
        }

        return $normalized;
    }

    protected function resolvePath(string $path): string
    {
        if ($this->files->isDirectory($path) || $this->files->exists($path)) {
            return $this->normalizePath($path);
        }

        return $this->basePath($path);
    }

    protected function shouldSkipFile(string $filename): bool
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (! in_array($extension, $this->searchableExtensions, true)) {
            return true;
        }

        return false;
    }

    protected function normalizePath(string $path): string
    {
        $path = str_replace(['\\', '//'], '/', $path);

        return rtrim($path, '/');
    }

    protected function defaultBasePath(): string
    {
        return $this->normalizePath(base_path());
    }
}
