<?php

namespace Backpack\CRUD\app\Console\Commands\Upgrade\v7\Steps;

use Backpack\CRUD\app\Console\Commands\Upgrade\Step;
use Backpack\CRUD\app\Console\Commands\Upgrade\StepResult;
use Illuminate\Filesystem\Filesystem;

class CheckFileManagerPublishedViewsStep extends Step
{
    private const LEGACY_VIEWS_DIRECTORY = 'resources/views/vendor/elfinder';

    /**
     * @var array<int, string>
     */
    protected array $legacyFiles = [];

    protected bool $legacyDirectoryDetected = false;

    public function title(): string
    {
        return 'Check if File Manager has published views';
    }

    public function run(): StepResult
    {
        $this->legacyFiles = [];
        $this->legacyDirectoryDetected = false;

        if (! $this->context()->hasComposerPackage('backpack/filemanager')) {
            return StepResult::success('File Manager add-on not detected; no published views to review.');
        }

        if (! $this->context()->fileExists(self::LEGACY_VIEWS_DIRECTORY)) {
            return StepResult::success('No File Manager views found in resources/views/vendor/elfinder.');
        }

        $filesystem = new Filesystem();
        $absoluteDirectory = $this->context()->basePath(self::LEGACY_VIEWS_DIRECTORY);

        if (! $filesystem->isDirectory($absoluteDirectory)) {
            return StepResult::success('No File Manager views found in resources/views/vendor/elfinder.');
        }

        $this->legacyDirectoryDetected = true;

        $this->legacyFiles = $this->collectRelativeFiles($filesystem, $absoluteDirectory);

        if (empty($this->legacyFiles)) {
            return StepResult::warning(
                'File Manager directory detected. Delete resources/views/vendor/elfinder if you have not customized those views.',
            );
        }

        return StepResult::warning(
            'File Manager views detected. Delete resources/views/vendor/elfinder if you have not customized those views.',
        );
    }

    public function canFix(StepResult $result): bool
    {
        if (! $result->status->isWarning()) {
            return false;
        }

        return $this->legacyDirectoryDetected;
    }

    public function fixMessage(StepResult $result): string
    {
        return 'We will delete resources/views/vendor/elfinder views. Proceed?';
    }

    public function fix(StepResult $result): StepResult
    {
        if (! $this->legacyDirectoryDetected) {
            return StepResult::skipped('Legacy File Manager directory no longer present.');
        }

        foreach ($this->legacyFiles as $path) {
            if (! $this->context()->deleteFile($path)) {
                return StepResult::failure("Could not delete {$path} automatically.");
            }
        }

        $filesystem = new Filesystem();
        $absoluteDirectory = $this->context()->basePath(self::LEGACY_VIEWS_DIRECTORY);

        if ($filesystem->isDirectory($absoluteDirectory) && ! $filesystem->deleteDirectory($absoluteDirectory)) {
            return StepResult::failure('Could not delete resources/views/vendor/elfinder automatically.');
        }

        $this->legacyFiles = [];
        $this->legacyDirectoryDetected = false;

        return StepResult::success('Removed resources/views/vendor/elfinder so the default package views are used.');
    }

    protected function collectRelativeFiles(Filesystem $filesystem, string $absoluteDirectory): array
    {
        $files = [];

        foreach ($filesystem->allFiles($absoluteDirectory) as $file) {
            $realPath = $file->getRealPath();

            if ($realPath === false) {
                continue;
            }

            $files[] = $this->context()->relativePath($realPath);
        }

        sort($files);

        return $files;
    }
}
