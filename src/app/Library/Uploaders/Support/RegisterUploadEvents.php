<?php

namespace Backpack\CRUD\app\Library\Uploaders\Support;

use Backpack\CRUD\app\Library\CrudPanel\CrudColumn;
use Backpack\CRUD\app\Library\CrudPanel\CrudField;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Uploaders\Support\Interfaces\UploaderInterface;
use Exception;

final class RegisterUploadEvents
{
    private readonly string $crudObjectType;

    public function __construct(
        private readonly CrudField|CrudColumn $crudObject,
        private readonly array $uploaderConfiguration,
        private readonly string $macro
        ) {
        $this->crudObjectType = is_a($crudObject, CrudField::class) ? 'field' : (is_a($crudObject, CrudColumn::class) ? 'column' : null);

        if (! $this->crudObjectType) {
            abort(500, 'Upload handlers only work for CrudField and CrudColumn classes.');
        }
    }

    public static function handle(CrudField|CrudColumn $crudObject, array $uploaderConfiguration, string $macro, ?array $subfield = null): void
    {
        $instance = new self($crudObject, $uploaderConfiguration, $macro);

        $instance->registerEvents($subfield);
    }

    /*******************************
     * Private methods - implementation
     *******************************/
    private function registerEvents(array|null $subfield = []): void
    {
        if (! empty($subfield)) {
            $this->registerSubfieldEvent($subfield);

            return;
        }

        $attributes = $this->crudObject->getAttributes();
        $model = $attributes['model'] ?? $this->crudObject->crud()->getModel()::class;
        $uploader = $this->getUploader($attributes, $this->uploaderConfiguration);

        $this->setupModelEvents($model, $uploader);
        $this->setupUploadConfigsInCrudObject($uploader);
    }

    private function registerSubfieldEvent(array $subfield): void
    {
        $uploader = $this->getUploader($subfield, $this->uploaderConfiguration);
        $crudObject = $this->crudObject->getAttributes();
        $uploader = $uploader->repeats($crudObject['name']);

        // If this uploader is already registered bail out. We may endup here multiple times when doing modifications to the crud object.
        // Changing `subfields` properties will call the macros again. We prevent duplicate entries by checking
        // if the uploader is already registered.
        if (app('UploadersRepository')->isUploadRegistered($uploader->getRepeatableContainerName(), $uploader)) {
            return;
        }

        $model = $subfield['baseModel'] ?? $this->crudObject->crud()->getModel()::class;

        if (isset($crudObject['relation_type']) && $crudObject['entity'] !== false) {
            $uploader = $uploader->relationship(true);
        }

        // for subfields, we only register one event so that we have access to the repeatable container name.
        // all the uploaders for a given container are stored in the UploadersRepository.
        if (! app('UploadersRepository')->hasRepeatableUploadersFor($uploader->getRepeatableContainerName())) {
            $this->setupModelEvents($model, $uploader);
        }

        $subfields = collect($this->crudObject->getAttributes()['subfields']);
        $subfields = $subfields->map(function ($item) use ($subfield, $uploader) {
            if ($item['name'] === $subfield['name']) {
                $item['upload'] = true;
                $item['disk'] = $uploader->getDisk();
                $item['prefix'] = $uploader->getPath();
                if ($uploader->useTemporaryUrl()) {
                    $item['temporary'] = $uploader->useTemporaryUrl();
                    $item['expiration'] = $uploader->getExpirationTimeInMinutes();
                }
            }

            return $item;
        })->toArray();

        app('UploadersRepository')->registerRepeatableUploader($uploader->getRepeatableContainerName(), $uploader);

        $this->crudObject->subfields($subfields);
    }

    /**
     * Register the saving, retrieved and deleting events on model to handle the various upload stages.
     * In case of CrudColumn we don't register the saving event.
     */
    private function setupModelEvents(string $model, UploaderInterface $uploader): void
    {
        if (app('UploadersRepository')->isUploadHandled($uploader->getIdentifier())) {
            return;
        }

        if ($this->crudObjectType === 'field') {
            $model::saving(function ($entry) use ($uploader) {
                $updatedCountKey = 'uploaded_'.($uploader->getRepeatableContainerName() ?? $uploader->getName()).'_count';

                CRUD::set($updatedCountKey, CRUD::get($updatedCountKey) ?? 0);

                $entry = $uploader->storeUploadedFiles($entry);

                CRUD::set($updatedCountKey, CRUD::get($updatedCountKey) + 1);
            });
        }

        $model::retrieved(function ($entry) use ($uploader) {
            $entry = $uploader->retrieveUploadedFiles($entry);
        });

        $model::deleting(function ($entry) use ($uploader) {
            $uploader->deleteUploadedFiles($entry);
        });

        app('UploadersRepository')->markAsHandled($uploader->getIdentifier());
    }

    /**
     * Return the uploader for the object beeing configured.
     * We will give priority to any uploader provided by `uploader => App\SomeUploaderClass` on upload definition.
     *
     * If none provided, we will use the Backpack defaults for the given object type.
     *
     * Throws an exception in case no uploader for the given object type is found.
     *
     * @throws Exception
     */
    private function getUploader(array $crudObject, array $uploaderConfiguration): UploaderInterface
    {
        $customUploader = isset($uploaderConfiguration['uploader']) && class_exists($uploaderConfiguration['uploader']);

        if ($customUploader) {
            return $uploaderConfiguration['uploader']::for($crudObject, $uploaderConfiguration);
        }

        $uploader = app('UploadersRepository')->hasUploadFor($crudObject['type'], $this->macro);

        if ($uploader) {
            return app('UploadersRepository')->getUploadFor($crudObject['type'], $this->macro)::for($crudObject, $uploaderConfiguration);
        }

        throw new Exception('Undefined upload type for '.$this->crudObjectType.' type: '.$crudObject['type']);
    }

    /**
     * Set up the upload attributes in the CrudObject.
     */
    private function setupUploadConfigsInCrudObject(UploaderInterface $uploader): void
    {
        $this->crudObject->upload(true)->disk($uploader->getDisk())->prefix($uploader->getPath());
    }
}
