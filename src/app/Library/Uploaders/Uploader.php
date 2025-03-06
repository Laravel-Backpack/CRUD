<?php

namespace Backpack\CRUD\app\Library\Uploaders;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Uploaders\Support\Interfaces\UploaderInterface;
use Backpack\CRUD\app\Library\Uploaders\Support\Traits\HandleFileNaming;
use Backpack\CRUD\app\Library\Uploaders\Support\Traits\HandleRepeatableUploads;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class Uploader implements UploaderInterface
{
    use HandleFileNaming;
    use HandleRepeatableUploads;

    private string $name;

    private string $disk = 'public';

    private string $path = '';

    public bool $handleMultipleFiles = false;

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

        $values = $this->getUploadedFilesFromRequest();

        if ($this->attachedToFakeField) {
            $fakeFieldValue = $entry->{$this->attachedToFakeField};
            $fakeFieldValue = is_string($fakeFieldValue) ? json_decode($fakeFieldValue, true) : (array) $fakeFieldValue;
            $fakeFieldValue[$this->getAttributeName()] = $this->uploadFiles($entry, $values);

            $entry->{$this->attachedToFakeField} = isset($entry->getCasts()[$this->attachedToFakeField]) ? $fakeFieldValue : json_encode($fakeFieldValue);

            return $entry;
        }

        $entry->{$this->getAttributeName()} = $this->uploadFiles($entry, $values);

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
        if (! in_array(SoftDeletes::class, class_uses_recursive($entry), true)) {
            $this->performFileDeletion($entry);

            return;
        }

        if ($entry->isForceDeleting() === true) {
            $this->performFileDeletion($entry);
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

    public function shouldUploadFiles($entryValue): bool
    {
        return true;
    }

    public function shouldKeepPreviousValueUnchanged(Model $entry, $entryValue): bool
    {
        return $entry->exists && ($entryValue === null || $entryValue === [null]);
    }

    public function hasDeletedFiles($entryValue): bool
    {
        return $entryValue === false || $entryValue === null || $entryValue === [null];
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

    public function getValueWithoutPath(?string $value = null): ?string
    {
        return $value ? Str::after($value, $this->path) : null;
    }

    public function getUploadedFilesFromRequest()
    {
        return CRUD::getRequest()->file($this->getNameForRequest());
    }

    public function isFake(): bool
    {
        return $this->attachedToFakeField !== false;
    }

    public function getFakeAttribute(): bool|string
    {
        return $this->attachedToFakeField;
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

    public function fake(bool|string $isFake): self
    {
        $this->attachedToFakeField = $isFake;

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

        if ($this->attachedToFakeField) {
            $values = $entry->{$this->attachedToFakeField};

            $values = is_string($values) ? json_decode($values, true) : $values;
            $attributeValue = $values[$this->getAttributeName()] ?? null;
            $attributeValue = is_array($attributeValue) ? array_map(fn ($value) => $this->getValueWithoutPath($value), $attributeValue) : $this->getValueWithoutPath($attributeValue);
            $values[$this->getAttributeName()] = $attributeValue;
            $entry->{$this->attachedToFakeField} = isset($entry->getCasts()[$this->attachedToFakeField]) ? $values : json_encode($values);

            return $entry;
        }

        if ($this->handleMultipleFiles) {
            if (! isset($entry->getCasts()[$this->getName()]) && is_string($value)) {
                $entry->{$this->getAttributeName()} = json_decode($value, true);
            }

            return $entry;
        }

        $entry->{$this->getAttributeName()} = $this->getValueWithoutPath($value);

        return $entry;
    }

    protected function deleteFiles(Model $entry)
    {
        if (! $this->shouldDeleteFiles()) {
            return;
        }

        if ($this->attachedToFakeField) {
            $values = $entry->{$this->attachedToFakeField};
            $values = is_string($values) ? json_decode($values, true) : $values;
            $values = $values[$this->getAttributeName()] ?? null;
        }

        $values ??= $entry->{$this->getAttributeName()};

        if ($values === null) {
            return;
        }

        if ($this->handleMultipleFiles) {
            // ensure we have an array of values when field is not casted in model.
            if (is_string($values)) {
                $values = json_decode($values, true);
            }
            foreach ($values ?? [] as $value) {
                $value = Str::start($value, $this->path);
                Storage::disk($this->disk)->delete($value);
            }

            return;
        }

        $values = Str::start($values, $this->path);
        Storage::disk($this->disk)->delete($values);
    }

    private function performFileDeletion(Model $entry)
    {
        if (! $this->handleRepeatableFiles && $this->deleteWhenEntryIsDeleted) {
            $this->deleteFiles($entry);

            return;
        }

        $this->deleteRepeatableFiles($entry);
    }

    /*******************************
     * helper methods
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
