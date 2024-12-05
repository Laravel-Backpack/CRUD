<?php

namespace Backpack\CRUD\app\Library\Uploaders\Support\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

interface UploaderInterface
{
    /**
     * Static constructor function.
     */
    public static function for(array $field, array $configuration): UploaderInterface;

    /**
     * Default implementation functions.
     */

    // method called on `saving` event to store and update the entry with the uploaded files
    public function storeUploadedFiles(Model $entry);

    // method called on `retrieved` event to populated the uploaded files in the entry
    public function retrieveUploadedFiles(Model $entry);

    // method called on `deleting` event to delete the uploaded files
    public function deleteUploadedFiles(Model $entry);

    /**
     * Setters - configuration methods.
     */
    public function multiple(): self;

    public function repeats(string $repeatableContainerName): self;

    public function relationship(bool $isRelation): self;

    public function fake(bool|string $isFake): self;

    /**
     * Getters.
     */
    public function getName(): string;

    public function getAttributeName(): string;

    public function getDisk(): string;

    public function getPath(): string;

    public function useTemporaryUrl(): bool;

    public function getExpirationTimeInMinutes(): int;

    public function getFileName(string|UploadedFile $file): string;

    public function getRepeatableContainerName(): ?string;

    public function getIdentifier(): string;

    public function getNameForRequest(): string;

    public function canHandleMultipleFiles(): bool;

    public function isRelationship(): bool;

    public function getPreviousFiles(Model $entry): mixed;

    /**
     * Strategy methods.
     */
    public function shouldDeleteFiles(): bool;

    public function hasDeletedFiles($entryValue): bool;

    public function shouldUploadFiles(mixed $value): bool;

    public function shouldKeepPreviousValueUnchanged(Model $entry, mixed $entryValue): bool;
}
