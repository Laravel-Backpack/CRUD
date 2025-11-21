<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\Support;

use Backpack\CRUD\app\Console\Commands\Upgrade\UpgradeContext;

class ConfigFilesHelper
{
    protected UpgradeContext $context;
    protected string $publishedRoot;
    protected string $packageRoot;
    protected bool $publishedIsFile = false;
    protected bool $packageIsFile = false;
    protected ?string $defaultConfigFile = null;
    private array $configFiles = [];

    public function __construct(
        UpgradeContext $context,
        string $publishedDirectory,
        string $packageDirectory
    ) {
        $this->context = $context;
        $this->setPublishedDirectory($publishedDirectory);
        $this->setPackageDirectory($packageDirectory);
        $this->initializeConfigFiles();
    }

    public function setDefaultConfigFile(string $configFile): void
    {
        $this->defaultConfigFile = $configFile;
    }

    protected function initializeConfigFiles(): void
    {
        if ($this->configFiles !== []) {
            return;
        }

        if (! isset($this->publishedRoot)) {
            return;
        }

        $this->configFiles = $this->inspectConfigFiles([$this->publishedRoot]);
    }

    public function publishedRelativePath(string $path): string
    {
        $absolutePath = $this->resolvePublishedPath($path);
        $relativePath = $this->context->relativePath($absolutePath);

        if ($relativePath !== $absolutePath) {
            return $relativePath;
        }

        return $this->relativePublishedPath($absolutePath);
    }

    public function readPublishedFile(string $path): ?string
    {
        $absolutePath = $this->resolvePublishedPath($path);

        if (! is_file($absolutePath)) {
            return null;
        }

        return $this->context->readFile($this->context->relativePath($absolutePath));
    }

    public function configKeyHasValue(string $key, mixed $expectedValue, ?string $path = null): bool
    {
        $target = $this->resolveConfigFileArgument($path);

        if ($target === null) {
            return false;
        }

        $config = $this->loadPublishedConfig($target);

        if ($config === null) {
            return false;
        }

        $current = $this->getConfigValueByKey($config, $key);

        if ($current === null) {
            return false;
        }

        if (is_array($current)) {
            if (is_array($expectedValue)) {
                foreach ($expectedValue as $value) {
                    if (! in_array($value, $current, true)) {
                        return false;
                    }
                }

                return true;
            }

            return in_array($expectedValue, $current, true);
        }

        return $current === $expectedValue;
    }

    public function writePublishedFile(string $path, string $contents): bool
    {
        $absolutePath = $this->resolvePublishedPath($path);

        return $this->context->writeFile($this->context->relativePath($absolutePath), $contents);
    }

    public function updateConfigKeyValue(string $key, mixed $newValue, ?string $path = null): bool
    {
        $target = $this->resolveConfigFileArgument($path);

        if ($target === null) {
            return false;
        }

        if (is_string($newValue) && $this->configKeyHasValue($key, $newValue, $target)) {
            return false;
        }

        $contents = $this->readPublishedFile($target);

        if ($contents === null) {
            return false;
        }

        $changed = false;
        $pattern = '/(?P<prefix>(["\"])'.preg_quote($key, '/').'\2\s*=>\s*)(?P<value>(?:[^,\r\n\/]|\/(?!\/))+)(?P<suffix>,?[ \t]*(?:\/\/[^\r\n]*)?)/';

        $updated = preg_replace_callback(
            $pattern,
            function (array $matches) use ($newValue, &$changed) {
                $existing = trim($matches['value']);
                $quote = $existing[0] ?? null;

                if (is_string($newValue)) {
                    $preferredQuote = ($quote === "'" || $quote === '"') ? $quote : "'";
                    $replacement = $this->exportStringValue($newValue, $preferredQuote);
                } else {
                    $replacement = $this->exportValue($newValue);
                }

                if ($replacement === $existing) {
                    return $matches[0];
                }

                $changed = true;

                return $matches['prefix'].$replacement.$matches['suffix'];
            },
            $contents,
            1
        );

        if ($updated === null || ! $changed) {
            return false;
        }

        return $this->writePublishedFile($target, $updated);
    }

    public function commentOutConfigValue(string $valueExpression, ?string $path = null): bool
    {
        $target = $this->resolveConfigFileArgument($path);

        if ($target === null) {
            return false;
        }

        $contents = $this->readPublishedFile($target);

        if ($contents === null) {
            return false;
        }

        $pattern = '~^[\t ]*'.preg_quote($valueExpression, '~').'([\t ]*,?[\t ]*)\r?$~m';

        $updated = preg_replace_callback(
            $pattern,
            function (array $matches) use ($valueExpression) {
                $position = strpos($matches[0], $valueExpression);
                $indentation = $position === false ? '' : substr($matches[0], 0, $position);

                return $indentation.'// '.$valueExpression.$matches[1];
            },
            $contents,
            1,
            $count
        );

        if ($updated === null || $count === 0) {
            return false;
        }

        return $this->writePublishedFile($target, $updated);
    }

    public function loadPublishedConfig(string $path): ?array
    {
        return $this->loadConfigArray($this->resolvePublishedPath($path));
    }

    public function loadPackageConfig(string $path): ?array
    {
        return $this->loadConfigArray($this->packageConfigPath($path));
    }

    public function analyzeConfigFile(string $path): ?array
    {
        if (! is_file($path)) {
            return null;
        }

        $displayPath = $this->context->relativePath($path);
        $relativeWithinPublished = $this->relativePublishedPath($path);

        $publishedConfig = $this->loadConfigArray($path);
        $packageConfig = $this->loadPackageConfigFor($path);

        if ($publishedConfig === null || $packageConfig === null) {
            return [
                'filename' => $relativeWithinPublished,
                'relative_path' => $displayPath,
                'absolute_path' => $path,
                'published_config' => $publishedConfig,
                'package_config' => $packageConfig,
                'missing_keys' => [],
                'top_level_missing_keys' => [],
            ];
        }

        $packageKeys = $this->flattenKeys($packageConfig);
        $publishedKeys = $this->flattenKeys($publishedConfig);

        return [
            'filename' => $relativeWithinPublished,
            'relative_path' => $displayPath,
            'absolute_path' => $path,
            'published_config' => $publishedConfig,
            'package_config' => $packageConfig,
            'missing_keys' => $this->calculateMissingKeys($packageKeys, $publishedKeys),
            'top_level_missing_keys' => $this->calculateTopLevelMissingKeys($packageConfig, $publishedConfig),
        ];
    }

    public function getMissingEntries(string $path, array $keys): array
    {
        if (empty($keys)) {
            return [];
        }

        $entries = $this->extractPackageEntries($path);

        if (empty($entries)) {
            return [];
        }

        $result = [];

        foreach ($keys as $key) {
            if (isset($entries[$key])) {
                $result[] = $entries[$key];
            }
        }

        return $result;
    }

    public function inspectConfigFiles(array $paths): array
    {
        $files = $this->collectFiles($paths);

        $checkedAny = false;
        $missingKeysPerFile = [];
        $topLevelEntriesPerFile = [];
        $topLevelMissingKeysPerFile = [];
        $collectedEntries = [];
        $absoluteMap = [];

        foreach ($files as $absolutePath) {
            $analysis = $this->analyzeConfigFile($absolutePath);

            if ($analysis === null) {
                continue;
            }

            $checkedAny = true;

            $displayPath = $analysis['relative_path'];
            $publishedConfig = $analysis['published_config'];
            $packageConfig = $analysis['package_config'];

            $absoluteMap[$displayPath] = $absolutePath;

            if ($publishedConfig === null || $packageConfig === null) {
                continue;
            }

            $missingKeys = $analysis['missing_keys'] ?? [];

            if (! empty($missingKeys)) {
                $missingKeysPerFile[$displayPath] = $missingKeys;
            }

            $topLevelKeys = $analysis['top_level_missing_keys'] ?? [];

            if (empty($topLevelKeys)) {
                continue;
            }

            $entriesByKey = [];

            foreach ($this->getMissingEntries($absolutePath, $topLevelKeys) as $entry) {
                $key = $entry['key'] ?? null;

                if ($key === null) {
                    continue;
                }

                if (! isset($entriesByKey[$key])) {
                    $entriesByKey[$key] = $entry;
                }

                if (! isset($collectedEntries[$key])) {
                    $collectedEntries[$key] = $entry;
                }
            }

            if (! empty($entriesByKey)) {
                $topLevelEntriesPerFile[$displayPath] = array_values($entriesByKey);
                $topLevelMissingKeysPerFile[$displayPath] = array_keys($entriesByKey);
            }
        }

        return [
            'checked_any' => $checkedAny,
            'missing_keys_per_file' => $missingKeysPerFile,
            'top_level_entries_per_file' => $topLevelEntriesPerFile,
            'top_level_missing_keys_per_file' => $topLevelMissingKeysPerFile,
            'collected_entries' => $collectedEntries,
            'absolute_paths' => $absoluteMap,
        ];
    }

    public function configFilesPublished(): bool
    {
        return (bool) ($this->configFiles['checked_any'] ?? false);
    }

    public function missingKeysPerFile(): array
    {
        return $this->configFiles['missing_keys_per_file'] ?? [];
    }

    public function topLevelEntriesPerFile(): array
    {
        return $this->configFiles['top_level_entries_per_file'] ?? [];
    }

    public function topLevelMissingKeysPerFile(): array
    {
        return $this->configFiles['top_level_missing_keys_per_file'] ?? [];
    }

    public function collectedEntries(): array
    {
        return $this->configFiles['collected_entries'] ?? [];
    }

    public function absolutePaths(): array
    {
        return $this->configFiles['absolute_paths'] ?? [];
    }

    public function loadConfigArray(string $path): ?array
    {
        if (! is_file($path)) {
            return null;
        }

        $data = include $path;

        return is_array($data) ? $data : null;
    }

    public function flattenKeys(array $config, string $prefix = ''): array
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

    public function publishedFileContainsKey(string $path, string $key): bool
    {
        $config = $this->loadPublishedConfig($path);

        if ($config === null) {
            return false;
        }

        return in_array($key, $this->flattenKeys($config), true);
    }

    public function packageConfigPath(string $path): string
    {
        return $this->packagePathFor($path);
    }

    public function setPublishedDirectory(string $publishedDirectory): void
    {
        $this->publishedRoot = $this->trimTrailingSeparators(trim($publishedDirectory));

        if ($this->publishedRoot === '') {
            throw new \InvalidArgumentException('Published directory path must not be empty.');
        }

        $this->publishedIsFile = ! is_dir($this->publishedRoot);
        $this->configFiles = [];
    }

    public function setPackageDirectory(string $packageDirectory): void
    {
        $this->packageRoot = $this->trimTrailingSeparators(trim($packageDirectory));

        if ($this->packageRoot === '') {
            throw new \InvalidArgumentException('Package directory path must not be empty.');
        }

        $this->packageIsFile = ! is_dir($this->packageRoot);
        $this->configFiles = [];
    }

    public function getPackageEntries(string $path, ?array $onlyKeys = null): array
    {
        $entries = $this->extractPackageEntries($path);

        if ($onlyKeys === null) {
            return $entries;
        }

        if ($onlyKeys === []) {
            return [];
        }

        $lookup = array_fill_keys($onlyKeys, true);

        return array_intersect_key($entries, $lookup);
    }

    public function getPackageEntry(string $path, string $key): ?array
    {
        $entries = $this->extractPackageEntries($path);

        return $entries[$key] ?? null;
    }

    public function buildSnippet(array $entries): string
    {
        $blocks = [];

        foreach ($entries as $entry) {
            $commentLines = $entry['commentLines'] ?? [];
            $entryLines = $entry['entryLines'] ?? [];

            if (empty($entryLines)) {
                continue;
            }

            $normalized = [];

            foreach ($commentLines as $line) {
                $normalized[] = rtrim($line, "\r");
            }

            foreach ($entryLines as $line) {
                $normalized[] = rtrim($line, "\r");
            }

            if (! empty($normalized)) {
                $blocks[] = implode(PHP_EOL, $normalized);
            }
        }

        if (empty($blocks)) {
            return '';
        }

        return implode(PHP_EOL.PHP_EOL, $blocks).PHP_EOL;
    }

    public function addEntriesToPublishedFile(string $path, array $entries, ?string &$error = null): bool
    {
        if (empty($entries)) {
            return true;
        }

        $contents = $this->readPublishedFile($path);

        if ($contents === null) {
            $error = sprintf('Could not read %s to update missing configuration keys.', $this->publishedRelativePath($path));

            return false;
        }

        $snippet = $this->buildSnippet($entries);

        if ($snippet === '') {
            return true;
        }

        $closingPosition = $this->findConfigArrayClosurePosition($contents);

        if ($closingPosition === null) {
            $error = sprintf('Could not locate the end of the configuration array in %s.', $this->publishedRelativePath($path));

            return false;
        }

        $before = substr($contents, 0, $closingPosition);
        $after = substr($contents, $closingPosition);

        $configArray = $this->loadPublishedConfig($path);
        $shouldEnsureTrailingComma = $configArray === null ? true : ! empty($configArray);

        if ($shouldEnsureTrailingComma) {
            $before = $this->ensureTrailingComma($before);
        }

        $newline = str_contains($contents, "\r\n") ? "\r\n" : "\n";
        $before = rtrim($before).$newline.$newline;

        if ($newline !== PHP_EOL) {
            $snippet = $this->normalizeNewlines($snippet, $newline);
        }

        $updated = $before.$snippet.$after;

        if (! $this->writePublishedFile($path, $updated)) {
            $error = sprintf('Could not update %s automatically.', $this->publishedRelativePath($path));

            return false;
        }

        return true;
    }

    public function addKeyToConfigFile(string $path, string $snippet, ?string &$error = null): bool
    {
        $contents = $this->readPublishedFile($path);

        if ($contents === null) {
            $error = sprintf('Could not read %s to update the configuration keys.', $this->publishedRelativePath($path));

            return false;
        }

        $newline = str_contains($contents, "\r\n") ? "\r\n" : "\n";
        $normalizedSnippet = rtrim($this->normalizeNewlines($snippet, $newline));

        if ($normalizedSnippet === '') {
            return true;
        }

        $normalizedSnippet .= $newline.$newline;
        $pattern = '/(return\s*\[\s*(?:\r?\n))/';
        $replacement = '$1'.$normalizedSnippet;

        $updatedContents = preg_replace($pattern, $replacement, $contents, 1, $replacements);

        if ($updatedContents === null || $replacements === 0) {
            $error = sprintf('Could not locate the start of the configuration array in %s.', $this->publishedRelativePath($path));

            return false;
        }

        if (! $this->writePublishedFile($path, $updatedContents)) {
            $error = sprintf('Could not save the updated %s configuration.', $this->publishedRelativePath($path));

            return false;
        }

        return true;
    }

    public function ensureTrailingComma(string $before): string
    {
        $trimmed = rtrim($before);

        if ($trimmed === '') {
            return $before;
        }

        $trailingLength = strlen($before) - strlen($trimmed);
        $trailingWhitespace = $trailingLength > 0 ? substr($before, -$trailingLength) : '';
        $core = $trailingLength > 0 ? substr($before, 0, -$trailingLength) : $before;

        $parts = preg_split('/(\r\n|\n|\r)/', $core, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($parts === false) {
            return $before;
        }

        $segments = [];

        for ($i = 0, $partCount = count($parts); $i < $partCount; $i += 2) {
            $segments[] = [
                'line' => $parts[$i],
                'newline' => $parts[$i + 1] ?? '',
            ];
        }

        $modified = false;

        for ($i = count($segments) - 1; $i >= 0; $i--) {
            $line = $segments[$i]['line'];
            $trimmedLine = trim($line);

            if ($trimmedLine === '') {
                continue;
            }

            if ($this->isCommentLine($trimmedLine) || $this->isBlockCommentStart($trimmedLine) || str_starts_with($trimmedLine, '*')) {
                continue;
            }

            $commentPos = $this->findInlineCommentPosition($line);
            $lineBeforeComment = $commentPos !== null ? substr($line, 0, $commentPos) : $line;
            $lineBeforeCommentTrimmed = rtrim($lineBeforeComment);

            if ($lineBeforeCommentTrimmed === '' || $this->looksLikeArrayOpening($lineBeforeCommentTrimmed)) {
                continue;
            }

            if (str_ends_with($lineBeforeCommentTrimmed, ',')) {
                break;
            }

            if ($commentPos !== null) {
                $codeSegment = substr($line, 0, $commentPos);
                $commentPart = substr($line, $commentPos);
                $codePart = rtrim($codeSegment);
                $spaceLength = strlen($codeSegment) - strlen($codePart);
                $spaceSegment = $spaceLength > 0 ? substr($codeSegment, -$spaceLength) : '';
                $segments[$i]['line'] = $codePart.','.$spaceSegment.$commentPart;
            } else {
                $segments[$i]['line'] = rtrim($line).',';
            }

            $modified = true;
            break;
        }

        if (! $modified) {
            return $before;
        }

        $reconstructed = '';

        foreach ($segments as $segment) {
            $reconstructed .= $segment['line'].$segment['newline'];
        }

        return $reconstructed.$trailingWhitespace;
    }

    protected function extractPackageEntries(string $path): array
    {
        $packagePath = $this->packagePathFor($path);

        if (! is_file($packagePath)) {
            return [];
        }

        $lines = file($packagePath, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            return [];
        }

        $entries = [];
        $pendingComments = [];
        $lineCount = count($lines);
        $index = 0;
        $withinConfigArray = false;

        while ($index < $lineCount) {
            $line = rtrim($lines[$index], "\r");
            $trimmed = ltrim($line);

            if (! $withinConfigArray) {
                if (preg_match('/return\s*\[/', $trimmed)) {
                    $withinConfigArray = true;
                }

                $index++;
                continue;
            }

            if ($trimmed === '];') {
                break;
            }

            if ($this->isBlockCommentStart($trimmed)) {
                $pendingComments[] = $line;

                while (! str_contains($trimmed, '*/') && $index + 1 < $lineCount) {
                    $index++;
                    $line = rtrim($lines[$index], "\r");
                    $trimmed = ltrim($line);
                    $pendingComments[] = $line;
                }

                $index++;
                continue;
            }

            if ($this->isCommentLine($trimmed)) {
                $pendingComments[] = $line;
                $index++;
                continue;
            }

            if ($trimmed === '' && ! empty($pendingComments)) {
                $pendingComments[] = $line;
                $index++;
                continue;
            }

            if (! preg_match('/^([\'"])\s*(.+?)\1\s*=>/', $trimmed, $matches)) {
                $pendingComments = [];
                $index++;
                continue;
            }

            $key = $matches[2];
            $entryLines = [$line];

            $valueDepth = $this->calculateBracketDelta($line);
            $hasTerminatingComma = $this->lineHasTerminatingComma($line);

            while (($valueDepth > 0 || ! $hasTerminatingComma) && $index + 1 < $lineCount) {
                $index++;
                $line = rtrim($lines[$index], "\r");
                $entryLines[] = $line;

                $valueDepth += $this->calculateBracketDelta($line);
                $hasTerminatingComma = $this->lineHasTerminatingComma($line);
            }

            $entries[$key] = [
                'key' => $key,
                'commentLines' => $pendingComments,
                'entryLines' => $entryLines,
            ];

            $pendingComments = [];
            $index++;
        }

        return $entries;
    }

    protected function collectFiles(array $definitions): array
    {
        $files = [];

        foreach ($definitions as $definition) {
            if (! is_string($definition)) {
                continue;
            }

            $path = trim($definition);

            if ($path === '') {
                continue;
            }

            if (is_dir($path)) {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
                );

                foreach ($iterator as $fileInfo) {
                    if ($fileInfo->isFile() && strtolower($fileInfo->getExtension()) === 'php') {
                        $absolute = realpath($fileInfo->getPathname()) ?: $fileInfo->getPathname();
                        $files[$absolute] = true;
                    }
                }

                continue;
            }

            if (is_file($path)) {
                $absolute = realpath($path) ?: $path;
                $files[$absolute] = true;
            }
        }

        $paths = array_keys($files);
        sort($paths);

        return $paths;
    }

    protected function loadPackageConfigFor(string $absolutePublishedPath): ?array
    {
        return $this->loadConfigArray($this->packagePathFor($absolutePublishedPath));
    }

    protected function packagePathFor(string $publishedPath): string
    {
        $publishedAbsolute = $this->resolvePublishedPath($publishedPath);

        if ($this->packageRoot === '') {
            return $publishedAbsolute;
        }

        if ($this->packageIsFile) {
            return $this->packageRoot;
        }

        $packageRoot = rtrim($this->packageRoot, '\/');

        if ($this->publishedIsFile) {
            return $packageRoot.DIRECTORY_SEPARATOR.basename($publishedAbsolute);
        }

        $publishedRoot = rtrim($this->publishedRoot, '\/');

        if ($publishedRoot !== '') {
            $normalizedRoot = $this->normalizePath($publishedRoot);
            $normalizedPath = $this->normalizePath($publishedAbsolute);

            if ($normalizedPath === $normalizedRoot) {
                return $packageRoot;
            }

            $prefix = $normalizedRoot.'/';

            if (str_starts_with($normalizedPath, $prefix)) {
                $relative = substr($normalizedPath, strlen($prefix));

                return $packageRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
            }
        }

        return $packageRoot.DIRECTORY_SEPARATOR.basename($publishedAbsolute);
    }

    protected function trimTrailingSeparators(string $path): string
    {
        if ($path === '') {
            return '';
        }

        if ($path === DIRECTORY_SEPARATOR) {
            return $path;
        }

        $trimmed = rtrim($path, '/\\');

        return $trimmed === '' ? $path : $trimmed;
    }

    protected function resolvePublishedPath(?string $path = null): string
    {
        $target = $path ?? '';

        if ($target !== '' && $this->isAbsolutePath($target)) {
            return $target;
        }

        if ($this->publishedRoot === '') {
            return $target;
        }

        if ($this->publishedIsFile) {
            return $this->publishedRoot;
        }

        $relative = $this->trimLeadingSeparators($target);

        if ($relative === '') {
            return $this->publishedRoot;
        }

        return $this->publishedRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }

    protected function trimLeadingSeparators(string $path): string
    {
        return ltrim($path, '\\/');
    }

    protected function isAbsolutePath(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if (str_starts_with($path, '/') || str_starts_with($path, '\\')) {
            return true;
        }

        return (bool) preg_match('/^[A-Za-z]:[\\\\\/]/', $path);
    }

    protected function relativePublishedPath(string $path): string
    {
        $absolutePath = $this->resolvePublishedPath($path);

        if ($this->publishedRoot === '' || $this->publishedIsFile) {
            return basename($absolutePath);
        }

        $normalizedRoot = $this->normalizePath($this->publishedRoot);
        $normalizedPath = $this->normalizePath($absolutePath);

        if ($normalizedPath === $normalizedRoot) {
            return basename($absolutePath);
        }

        $prefix = $normalizedRoot.'/';

        if (str_starts_with($normalizedPath, $prefix)) {
            return substr($normalizedPath, strlen($prefix));
        }

        return basename($absolutePath);
    }

    protected function normalizePath(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }

    protected function resolveConfigFileArgument(?string $path): ?string
    {
        if ($path !== null) {
            return $path;
        }

        return $this->defaultConfigFile;
    }

    protected function getConfigValueByKey(array $config, string $key): mixed
    {
        if ($key === '') {
            return null;
        }

        $segments = explode('.', $key);
        $current = $config;

        foreach ($segments as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                return null;
            }

            $current = $current[$segment];
        }

        return $current;
    }

    protected function exportStringValue(string $value, string $quote): string
    {
        if ($quote === '"') {
            return '"'.addcslashes($value, '\\"$').'"';
        }

        return '\''.addcslashes($value, "\\'").'\'';
    }

    protected function exportValue(mixed $value): string
    {
        return var_export($value, true);
    }

    protected function calculateMissingKeys(array $packageKeys, array $publishedKeys): array
    {
        $missingKeys = array_values(array_diff($packageKeys, $publishedKeys));
        sort($missingKeys);

        return $missingKeys;
    }

    protected function calculateTopLevelMissingKeys(array $packageConfig, array $publishedConfig): array
    {
        $topLevelMissing = array_keys(array_diff_key($packageConfig, $publishedConfig));
        sort($topLevelMissing);

        return $topLevelMissing;
    }

    protected function findConfigArrayClosurePosition(string $contents): ?int
    {
        $position = strrpos($contents, '];');

        return $position === false ? null : $position;
    }

    protected function normalizeNewlines(string $text, string $newline): string
    {
        if ($newline === "\n") {
            return str_replace(["\r\n", "\r"], "\n", $text);
        }

        if ($newline === "\r\n") {
            $normalized = str_replace(["\r\n", "\r"], "\n", $text);

            return str_replace("\n", "\r\n", $normalized);
        }

        return $text;
    }

    protected function stripStrings(string $line): string
    {
        $result = '';
        $length = strlen($line);
        $inSingle = false;
        $inDouble = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $line[$i];

            if ($char === '\'' && ! $inDouble && ! $this->isCharacterEscaped($line, $i)) {
                $inSingle = ! $inSingle;
                continue;
            }

            if ($char === '"' && ! $inSingle && ! $this->isCharacterEscaped($line, $i)) {
                $inDouble = ! $inDouble;
                continue;
            }

            if ($inSingle || $inDouble) {
                if ($char === '\\' && $i + 1 < $length) {
                    $i++;
                }

                continue;
            }

            $result .= $char;
        }

        return $result;
    }

    protected function stripInlineComment(string $line): string
    {
        $clean = $this->stripStrings($line);
        $parts = explode('//', $clean, 2);

        return $parts[0];
    }

    protected function findInlineCommentPosition(string $line): ?int
    {
        $length = strlen($line);
        $inSingle = false;
        $inDouble = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $line[$i];

            if ($char === '\'' && ! $inDouble) {
                if (! $this->isCharacterEscaped($line, $i)) {
                    $inSingle = ! $inSingle;
                }

                continue;
            }

            if ($char === '"' && ! $inSingle) {
                if (! $this->isCharacterEscaped($line, $i)) {
                    $inDouble = ! $inDouble;
                }

                continue;
            }

            if ($inSingle || $inDouble) {
                continue;
            }

            if ($char === '/' && $i + 1 < $length && $line[$i + 1] === '/') {
                return $i;
            }
        }

        return null;
    }

    protected function isCharacterEscaped(string $line, int $position): bool
    {
        $escapeCount = 0;

        for ($i = $position - 1; $i >= 0 && $line[$i] === '\\'; $i--) {
            $escapeCount++;
        }

        return $escapeCount % 2 === 1;
    }

    protected function looksLikeArrayOpening(string $line): bool
    {
        if ($line === '[' || $line === '(') {
            return true;
        }

        if (preg_match('/^(return\s+)?\[\s*$/i', $line)) {
            return true;
        }

        if (preg_match('/^(return\s+)?array\s*\(\s*$/i', $line)) {
            return true;
        }

        return false;
    }

    protected function calculateBracketDelta(string $line): int
    {
        $clean = $this->stripStrings($line);

        $delta = substr_count($clean, '[') - substr_count($clean, ']');
        $delta += substr_count($clean, '(') - substr_count($clean, ')');
        $delta += substr_count($clean, '{') - substr_count($clean, '}');

        return $delta;
    }

    protected function lineHasTerminatingComma(string $line): bool
    {
        $clean = $this->stripInlineComment($line);

        return str_contains($clean, ',');
    }

    protected function isCommentLine(string $trimmedLine): bool
    {
        return str_starts_with($trimmedLine, '//');
    }

    protected function isBlockCommentStart(string $trimmedLine): bool
    {
        return str_starts_with($trimmedLine, '/*');
    }
}
