<?php

namespace Backpack\CRUD\app\Library\Uploaders\Support;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Uploaders\Support\Interfaces\UploaderInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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

    public function __construct()
    {
        $this->uploaderClasses = config('backpack.crud.uploaders', []);
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
        return array_key_exists($objectType, $this->uploaderClasses[$group] ?? []);
    }

    /**
     * Return the uploader for the given object type.
     */
    public function getUploadFor(string $objectType, string $group): string
    {
        if (! $this->hasUploadFor($objectType, $group)) {
            throw new \Exception('There is no uploader defined for the given field type.');
        }

        return $this->uploaderClasses[$group][$objectType];
    }

    /**
     * return the registered groups names AKA macros. eg: withFiles, withMedia.
     */
    public function getUploadersGroupsNames(): array
    {
        return array_keys($this->uploaderClasses);
    }

    /**
     * Register new uploaders or override existing ones.
     */
    public function addUploaderClasses(array $uploaders, string $group): void
    {
        // ensure all uploaders implement the UploaderInterface
        foreach ($uploaders as $uploader) {
            if (! is_a($uploader, UploaderInterface::class, true)) {
                throw new \Exception('The uploader class must implement the UploaderInterface.');
            }
        }
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

    /**
     * Get the uploaders classes for the given group of uploaders.
     */
    public function getAjaxUploadTypes(string $uploaderMacro = 'withFiles'): array
    {
        $ajaxFieldTypes = [];
        foreach ($this->uploaderClasses[$uploaderMacro] as $fieldType => $uploader) {
            if (is_a($uploader, 'Backpack\Pro\Uploads\BackpackAjaxUploader', true)) {
                $ajaxFieldTypes[] = $fieldType;
            }
        }

        return $ajaxFieldTypes;
    }

    /**
     * Get an ajax uploader instance for a given input name.
     */
    public function getFieldUploaderInstance(string $requestInputName): UploaderInterface
    {
        if (strpos($requestInputName, '#') !== false) {
            $repeatableContainerName = Str::before($requestInputName, '#');
            $requestInputName = Str::after($requestInputName, '#');

            $uploaders = $this->getRepeatableUploadersFor($repeatableContainerName);

            $uploader = Arr::first($uploaders, function ($uploader) use ($requestInputName) {
                return $uploader->getName() === $requestInputName;
            });

            if (! $uploader) {
                abort(500, 'Could not find the field in the repeatable uploaders.');
            }

            return $uploader;
        }

        if (empty($crudObject = CRUD::fields()[$requestInputName] ?? [])) {
            abort(500, 'Could not find the field in the CRUD fields.');
        }

        if (! $uploaderMacro = $this->getUploadCrudObjectMacroType($crudObject)) {
            abort(500, 'There is no uploader defined for the given field type.');
        }

        if (! $this->isValidUploadField($crudObject, $uploaderMacro)) {
            abort(500, 'Invalid field for upload.');
        }

        $uploaderConfiguration = $crudObject[$uploaderMacro] ?? [];
        $uploaderConfiguration = ! is_array($uploaderConfiguration) ? [] : $uploaderConfiguration;
        $uploaderClass = $this->getUploadFor($crudObject['type'], $uploaderMacro);

        return new $uploaderClass(['name' => $requestInputName], $uploaderConfiguration);
    }

    /**
     * Get the upload field macro type for the given object.
     */
    private function getUploadCrudObjectMacroType(array $crudObject): string|null
    {
        $uploadersGroups = $this->getUploadersGroupsNames();

        foreach ($uploadersGroups as $uploaderMacro) {
            if (isset($crudObject[$uploaderMacro])) {
                return $uploaderMacro;
            }
        }

        return null;
    }

    private function isValidUploadField($crudObject, $uploaderMacro)
    {
        if (Str::contains($crudObject['name'], '#')) {
            $container = Str::before($crudObject['name'], '#');
            $field = array_filter(CRUD::fields()[$container]['subfields'] ?? [], function ($item) use ($crudObject, $uploaderMacro) {
                return $item['name'] === $crudObject['name'] && in_array($item['type'], $this->getAjaxUploadTypes($uploaderMacro));
            });

            return ! empty($field);
        }

        return in_array($crudObject['type'], $this->getAjaxUploadTypes($uploaderMacro));
    }
}
