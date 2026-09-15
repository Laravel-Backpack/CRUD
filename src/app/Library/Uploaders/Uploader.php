<?php

namespace Backpack\CRUD\app\Library\Uploaders;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Uploaders\Support\Interfaces\UploaderInterface;
use Backpack\CRUD\app\Library\Uploaders\Support\Traits\HandleFileNaming;
use Backpack\CRUD\app\Library\Uploaders\Support\Traits\HandleRepeatableUploads;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

abstract class Uploader implements UploaderInterface
{
    use HandleFileNaming;
    use HandleRepeatableUploads;

    private string $name;

    private string $disk = 'public';

    private string $path = '';

    private bool $handleMultipleFiles = false;

    private bool $deleteWhenEntryIsDeleted = true;

    private bool|string $attachedToFakeField = false;

    /**
     * Cloud disks have the ability to generate temporary URLs to files, should we do it?
     */
    private bool $useTemporaryUrl = false;

    /**
     * When using temporary urls, define the time that the url will be valid.
     */
    private int $temporaryUrlExpirationTimeInMinutes = 1;

    /**
     * Indicates if the upload is relative to a relationship field/column.
     */
    private bool $isRelationship = false;

    /**
     * When previous files are updated, we need to keep track of them so that we don't add deleted files to the new list.
     */
    private $updatedPreviousFiles = null;

    public function __construct(array $crudObject, array $configuration)
    {
        $this->name = $crudObject['name'];
        $this->disk = $configuration['disk'] ?? $crudObject['disk'] ?? $this->disk;
        $this->path = $this->getPathFromConfiguration($crudObject, $configuration);
        $this->attachedToFakeField = isset($crudObject['fake']) && $crudObject['fake'] ? ($crudObject['store_in'] ?? 'extras') : ($crudObject['store_in'] ?? false);
        $this->useTemporaryUrl = $configuration['temporaryUrl'] ?? $this->useTemporaryUrl;
        $this->temporaryUrlExpirationTimeInMinutes = $configuration['temporaryUrlExpirationTime'] ?? $this->temporaryUrlExpirationTimeInMinutes;
        $this->deleteWhenEntryIsDeleted = $configuration['deleteWhenEntryIsDeleted'] ?? $this->deleteWhenEntryIsDeleted;
        $this->fileNamer = is_callable($configuration['fileNamer'] ?? null) ? $configuration['fileNamer'] : $this->getFileNameGeneratorInstance($configuration['fileNamer'] ?? null);
        $this->allowedExtensions = isset($configuration['allowedExtensions']) ? (array) $configuration['allowedExtensions'] : null;
    }

    /*******************************
     * Static methods
     *******************************/
    public static function for(array $crudObject, array $definition): UploaderInterface
    {
        return new static($crudObject, $definition);
    }

    /*******************************
     * public methods - event handler methods
     *******************************/
    public function storeUploadedFiles(Model $entry): Model
    {
        if ($this->handleRepeatableFiles) {
            return $this->handleRepeatableFiles($entry);
        }

        if ($this->attachedToFakeField) {
            $fakeFieldValue = $entry->{$this->attachedToFakeField};
            $fakeFieldValue = is_string($fakeFieldValue) ? json_decode($fakeFieldValue, true) : (array) $fakeFieldValue;
            $fakeFieldValue[$this->getAttributeName()] = $this->uploadFiles($entry);

            $entry->{$this->attachedToFakeField} = isset($entry->getCasts()[$this->attachedToFakeField]) ? $fakeFieldValue : json_encode($fakeFieldValue);

            return $entry;
        }

        $entry->{$this->getAttributeName()} = $this->uploadFiles($entry);

        return $entry;
    }

    public function retrieveUploadedFiles(Model $entry): Model
    {
        if ($this->handleRepeatableFiles) {
            return $this->retrieveRepeatableFiles($entry);
        }

        return $this->retrieveFiles($entry);
    }

    public function deleteUploadedFiles(Model $entry): void
    {
        if ($this->deleteWhenEntryIsDeleted) {
            if (! in_array(SoftDeletes::class, class_uses_recursive($entry), true)) {
                $this->performFileDeletion($entry);

                return;
            }

            if ($entry->isForceDeleting() === true) {
                $this->performFileDeletion($entry);
            }
        }
    }

    /**
     * Check the type of the files sent in the request for this uploader (and for the other uploaders in the same
     * repeatable), so a file that is not allowed is rejected before any uploader stores or deletes files.
     *
     * @throws ValidationException when a file type is not allowed
     */
    public function validateUploadedFiles(): void
    {
        if (! $this->handleRepeatableFiles) {
            $this->validateUploadedFilesTypes(CRUD::getRequest()->file($this->getNameForRequest()), $this->getNameForRequest());

            return;
        }

        $containerName = $this->getRepeatableContainerName();
        $rows = CRUD::getRequest()->file($containerName);

        foreach (app('UploadersRepository')->getRepeatableUploadersFor($containerName) as $uploader) {
            if (! $uploader instanceof self) {
                continue;
            }

            foreach (is_array($rows) ? $rows : [] as $row => $rowFiles) {
                if (is_array($rowFiles) && isset($rowFiles[$uploader->getAttributeName()])) {
                    $uploader->validateUploadedFilesTypes($rowFiles[$uploader->getAttributeName()], $containerName.'.'.$row.'.'.$uploader->getAttributeName());
                }
            }
        }
    }

    /**
     * @throws ValidationException when a file type is not allowed
     */
    protected function validateUploadedFilesTypes(mixed $files, string $validationKey): void
    {
        foreach (Arr::flatten(Arr::wrap($files)) as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            try {
                $this->getFileName($file);
            } catch (ValidationException $e) {
                throw ValidationException::withMessages([$validationKey => Arr::flatten($e->errors())]);
            }
        }
    }

    /*******************************
     * Getters
     *******************************/
    public function getName(): string
    {
        return $this->name;
    }

    public function getAttributeName(): string
    {
        return Str::afterLast($this->name, '.');
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function useTemporaryUrl(): bool
    {
        return $this->useTemporaryUrl;
    }

    public function getExpirationTimeInMinutes(): int
    {
        return $this->temporaryUrlExpirationTimeInMinutes;
    }

    public function shouldDeleteFiles(): bool
    {
        return $this->deleteWhenEntryIsDeleted;
    }

    public function getIdentifier(): string
    {
        if ($this->handleRepeatableFiles) {
            return $this->repeatableContainerName.'_'.$this->name;
        }

        return $this->name;
    }

    public function getNameForRequest(): string
    {
        return $this->repeatableContainerName ?? $this->name;
    }

    public function canHandleMultipleFiles(): bool
    {
        return $this->handleMultipleFiles;
    }

    public function isRelationship(): bool
    {
        return $this->isRelationship;
    }

    public function getPreviousFiles(Model $entry): mixed
    {
        if (! $this->attachedToFakeField) {
            return $this->getOriginalValue($entry);
        }

        $value = $this->getOriginalValue($entry, $this->attachedToFakeField);
        $value = is_string($value) ? json_decode($value, true) : (array) $value;

        return $value[$this->getAttributeName()] ?? null;
    }

    /*******************************
     * Setters - fluently configure the uploader
     *******************************/
    public function multiple(): self
    {
        $this->handleMultipleFiles = true;

        return $this;
    }

    public function relationship(bool $isRelationship): self
    {
        $this->isRelationship = $isRelationship;

        return $this;
    }

    /*******************************
     * Default implementation functions
     *******************************/
    public function uploadFiles(Model $entry, $values = null)
    {
    }

    private function retrieveFiles(Model $entry): Model
    {
        $value = $entry->{$this->getAttributeName()};

        if ($this->handleMultipleFiles) {
            if (! isset($entry->getCasts()[$this->getName()]) && is_string($value)) {
                $entry->{$this->getAttributeName()} = json_decode($value, true);
            }

            return $entry;
        }

        if ($this->attachedToFakeField) {
            $values = $entry->{$this->attachedToFakeField};
            $values = is_string($values) ? json_decode($values, true) : (array) $values;

            $values[$this->getAttributeName()] = isset($values[$this->getAttributeName()]) ? Str::after($values[$this->getAttributeName()], $this->path) : null;
            $entry->{$this->attachedToFakeField} = json_encode($values);

            return $entry;
        }

        $entry->{$this->getAttributeName()} = Str::after($value, $this->path);

        return $entry;
    }

    private function deleteFiles(Model $entry)
    {
        $values = $entry->{$this->getAttributeName()};

        if ($values === null) {
            return;
        }

        if ($this->handleMultipleFiles) {
            // ensure we have an array of values when field is not casted in model.
            if (! isset($entry->getCasts()[$this->name]) && is_string($values)) {
                $values = json_decode($values, true);
            }
            foreach ($values ?? [] as $value) {
                if (! is_string($value)) {
                    continue;
                }

                $this->deleteStoredFile(Str::start($value, $this->path));
            }

            return;
        }

        $values = Str::start($values, $this->path);
        $this->deleteStoredFile($values);
    }

    /**
     * Delete a file from the uploader disk, but only when it is safe to do so:
     * no parent directory segments, and inside the uploader path (when one is configured).
     */
    protected function deleteStoredFile(mixed $file): bool
    {
        if (! $this->canDeleteStoredFile($file)) {
            return false;
        }

        return Storage::disk($this->getDisk())->delete($file);
    }

    protected function canDeleteStoredFile(mixed $file): bool
    {
        if (! is_string($file) || trim($file) === '') {
            return false;
        }

        $file = ltrim(str_replace('\\', '/', $file), '/');

        if (preg_match('#(^|/)\.\.(/|$)#', $file)) {
            return false;
        }

        $path = ltrim(str_replace('\\', '/', $this->getPath()), '/');

        return $path === '' || Str::startsWith($file, $path);
    }

    /**
     * Given a file reference sent in the request, return the stored file it refers to,
     * but only if that file is one of the files the entry owns. The matched file is
     * removed from $ownedFiles, so each owned file can only be claimed once.
     */
    protected function pullOwnedFile(mixed $file, array &$ownedFiles): ?string
    {
        if (! is_string($file) || $file === '') {
            return null;
        }

        $key = array_search($file, $ownedFiles, true);

        if ($key === false) {
            foreach ($ownedFiles as $ownedKey => $ownedFile) {
                if ($this->getValueWithoutPath($ownedFile) === $file) {
                    $key = $ownedKey;
                    break;
                }
            }
        }

        if ($key === false) {
            return null;
        }

        $ownedFile = $ownedFiles[$key];
        unset($ownedFiles[$key]);

        return $ownedFile;
    }

    /**
     * Flatten stored values (eg. the values of all repeatable rows) into a list of file paths.
     */
    protected function getStoredFilesList(mixed $values): array
    {
        return array_values(array_filter(
            Arr::flatten(Arr::wrap($values)),
            fn ($file) => is_string($file) && $file !== ''
        ));
    }

    protected function getValueWithoutPath(?string $value = null): ?string
    {
        return $value ? Str::after($value, $this->path) : null;
    }

    private function performFileDeletion(Model $entry)
    {
        if (! $this->handleRepeatableFiles) {
            $this->deleteFiles($entry);

            return;
        }

        $this->deleteRepeatableFiles($entry);
    }

    /*******************************
     * Private helper methods
     *******************************/
    private function getPathFromConfiguration(array $crudObject, array $configuration): string
    {
        $this->path = $configuration['path'] ?? $crudObject['prefix'] ?? $this->path;

        return empty($this->path) ? $this->path : Str::of($this->path)->finish('/')->value();
    }

    private function getOriginalValue(Model $entry, $field = null)
    {
        $field ??= $this->getAttributeName();

        if ($this->updatedPreviousFiles !== null) {
            return $this->updatedPreviousFiles;
        }

        $previousValue = $entry->getOriginal($field);

        if (! $previousValue) {
            return $previousValue;
        }

        if (
            method_exists($entry, 'translationEnabled') &&
            $entry->translationEnabled() &&
            $entry->isTranslatableAttribute($field)
        ) {
            return $previousValue[$entry->getLocale()] ?? null;
        }

        return $previousValue;
    }
}
