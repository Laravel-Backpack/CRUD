<?php

namespace Backpack\CRUD\app\Library\Uploaders\Support;

use Backpack\CRUD\app\Library\Uploaders\Support\Interfaces\UploaderInterface;

final class UploadersRepository
{
    /**
     * The array of uploaders classes for field types.
     */
    private array $uploaderClasses;

    /**
     * Uploaders registered in a repeatable group.
     */
    private array $repeatableUploaders = [];

    /**
     * Uploaders that have already been handled (events registered) for each field/column instance.
     */
    private array $handledUploaders = [];

    /**
     * Uploaders that store files, whose request files are checked before any of them stores or deletes files.
     */
    private array $storingUploaders = [];

    /**
     * Models that already check the uploaded files before saving.
     */
    private array $modelsValidatingUploads = [];

    public function __construct()
    {
        $this->uploaderClasses = config('backpack.crud.uploaders');
    }

    /**
     * Register an uploader that stores files when the given models are saved. The first time a model is registered, a saving
     * event is added to check the files of all the uploaders, before any uploader saving event stores or deletes files.
     * Pass the crud model too, so relationship uploads are checked before the parent entry is saved.
     */
    public function registerStoringUploader(UploaderInterface $uploader, string ...$models): void
    {
        $this->storingUploaders[] = $uploader;

        foreach ($models as $model) {
            if (in_array($model, $this->modelsValidatingUploads, true)) {
                continue;
            }

            $model::saving(function () {
                $this->validateUploadedFiles();
            });

            $this->modelsValidatingUploads[] = $model;
        }
    }

    /**
     * Check the files sent in the request for all the uploaders that store files.
     *
     * @throws \Illuminate\Validation\ValidationException when a file type is not allowed
     */
    public function validateUploadedFiles(): void
    {
        foreach ($this->storingUploaders as $uploader) {
            if (method_exists($uploader, 'validateUploadedFiles')) {
                $uploader->validateUploadedFiles();
            }
        }
    }

    /**
     * Mark the given uploader as handled.
     */
    public function markAsHandled(string $objectName): void
    {
        if (! in_array($objectName, $this->handledUploaders)) {
            $this->handledUploaders[] = $objectName;
        }
    }

    /**
     * Check if the given uploader for field/column have been handled.
     */
    public function isUploadHandled(string $objectName): bool
    {
        return in_array($objectName, $this->handledUploaders);
    }

    /**
     * Check if there are uploads for the give object(field/column) type.
     */
    public function hasUploadFor(string $objectType, string $group): bool
    {
        return array_key_exists($objectType, $this->uploaderClasses[$group]);
    }

    /**
     * Return the uploader for the given object type.
     */
    public function getUploadFor(string $objectType, string $group): string
    {
        return $this->uploaderClasses[$group][$objectType];
    }

    /**
     * Register new uploaders or override existing ones.
     */
    public function addUploaderClasses(array $uploaders, string $group): void
    {
        $this->uploaderClasses[$group] = array_merge($this->getGroupUploadersClasses($group), $uploaders);
    }

    /**
     * Return the uploaders classes for the given group.
     */
    private function getGroupUploadersClasses(string $group): array
    {
        return $this->uploaderClasses[$group] ?? [];
    }

    /**
     * Register the specified uploader for the given upload name.
     */
    public function registerRepeatableUploader(string $uploadName, UploaderInterface $uploader): void
    {
        if (! array_key_exists($uploadName, $this->repeatableUploaders) || ! in_array($uploader, $this->repeatableUploaders[$uploadName])) {
            $this->repeatableUploaders[$uploadName][] = $uploader;
        }
    }

    /**
     * Check if there are uploaders registered for the given upload name.
     */
    public function hasRepeatableUploadersFor(string $uploadName): bool
    {
        return array_key_exists($uploadName, $this->repeatableUploaders);
    }

    /**
     * Get the repeatable uploaders for the given upload name.
     */
    public function getRepeatableUploadersFor(string $uploadName): array
    {
        return $this->repeatableUploaders[$uploadName] ?? [];
    }

    /**
     * Check if the specified upload is registered for the given repeatable uploads.
     */
    public function isUploadRegistered(string $uploadName, UploaderInterface $upload): bool
    {
        return $this->hasRepeatableUploadersFor($uploadName) && in_array($upload->getName(), $this->getRegisteredUploadNames($uploadName));
    }

    /**
     * Return the registered uploaders names for the given repeatable upload name.
     */
    public function getRegisteredUploadNames(string $uploadName): array
    {
        return array_map(function ($uploader) {
            return $uploader->getName();
        }, $this->getRepeatableUploadersFor($uploadName));
    }
}
