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
    public function storeUploadedFiles(Model $entry);

    public function retrieveUploadedFiles(Model $entry);

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

    public function shouldDeleteFiles(): bool;

    public function canHandleMultipleFiles(): bool;

    public function isRelationship(): bool;

    public function getPreviousFiles(Model $entry): mixed;

    public function getValueWithoutPath(?string $value = null): ?string;

    public function isFake(): bool;

    public function getFakeAttribute(): bool|string;
}
