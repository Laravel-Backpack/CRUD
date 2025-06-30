<?php

namespace Backpack\CRUD\app\Library\Uploaders\Support;

use Backpack\CRUD\app\Library\CrudPanel\CrudColumn;
use Backpack\CRUD\app\Library\CrudPanel\CrudField;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Uploaders\Support\Interfaces\UploaderInterface;
use Exception;
use Illuminate\Database\Eloquent\Relations\Pivot;

final class RegisterUploadEvents
{
    private string $crudObjectType;

    public function __construct(
        private readonly CrudField|CrudColumn $crudObject,
        private readonly array $uploaderConfiguration,
        private readonly string $macro
        ) {
        $this->crudObjectType = is_a($crudObject, CrudField::class) ? 'field' : (is_a($crudObject, CrudColumn::class) ? 'column' : null);

        if (! $this->crudObjectType) {
            abort(500, 'Upload handlers only work for CrudField and CrudColumn classes.', ['developer-error-exception']);
        }
    }

    public static function handle(CrudField|CrudColumn $crudObject, array $uploaderConfiguration, string $macro, ?array $subfield = null, bool $registerModelEvents = true): void
    {
        $instance = new self($crudObject, $uploaderConfiguration, $macro);

        $instance->registerEvents($subfield, $registerModelEvents);
    }

    /*******************************
     * Private methods - implementation
     *******************************/
    private function registerEvents(?array $subfield = [], bool $registerModelEvents = true): void
    {
        if (! empty($subfield)) {
            $this->registerSubfieldEvent($subfield, $registerModelEvents);

            return;
        }

        $attributes = $this->crudObject->getAttributes();
        $model = $attributes['model'] ?? get_class($this->crudObject->crud()->getModel());
        $uploader = $this->getUploader($attributes, $this->uploaderConfiguration);

        if (isset($attributes['relation_type']) && $attributes['entity'] !== false) {
            $uploader = $uploader->relationship(true);
        }

        $this->setupModelEvents($model, $uploader);
        $this->setupUploadConfigsInCrudObject($uploader);
    }

    private function registerSubfieldEvent(array $subfield, bool $registerModelEvents = true): void
    {
        $uploader = $this->getUploader($subfield, $this->uploaderConfiguration);
        $crudObject = $this->crudObject->getAttributes();
        $uploader = $uploader->repeats($crudObject['name']);
        $uploader = $uploader->fake((isset($crudObject['fake']) && $crudObject['fake']) ? ($crudObject['store_in'] ?? 'extras') : false);

        // If this uploader is already registered bail out. We may endup here multiple times when doing modifications to the crud object.
        // Changing `subfields` properties will call the macros again. We prevent duplicate entries by checking
        // if the uploader is already registered.
        if (app('UploadersRepository')->isUploadRegistered($uploader->getRepeatableContainerName(), $uploader)) {
            return;
        }

        if (isset($crudObject['relation_type']) && $crudObject['entity'] !== false) {
            $uploader = $uploader->relationship(true);
            $subfield['relation_type'] = $crudObject['relation_type'];
        }

        $model = $this->getSubfieldModel($subfield, $uploader);

        // only the last subfield uploader will setup the model events for the whole group
        if ($registerModelEvents) {
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
                $entry = $uploader->storeUploadedFiles($entry);
            });
        }
        // if the entry is already retrieved from database, don't register the event
        // just process the uploader on the crud entry we already got.
        if (app('crud')->entry) {
            app('crud')->entry = $uploader->retrieveUploadedFiles(app('crud')->entry);
        } else {
            // the retrieve model may differ from the deleting and saving models because retrieved event
            // is not called in pivot models when loading the relations.
            $retrieveModel = $this->getModelForRetrieveEvent($model, $uploader);

            $retrieveModel::retrieved(function ($entry) use ($uploader) {
                if ($entry->translationEnabled()) {
                    $locale = request('_locale', app()->getLocale());
                    if (in_array($locale, array_keys($entry->getAvailableLocales()))) {
                        $entry->setLocale($locale);
                    }
                }
                $entry = $uploader->retrieveUploadedFiles($entry);
            });
        }

        $model::deleting(function ($entry) use ($uploader) {
            $uploader->deleteUploadedFiles($entry);
        });

        // if the uploader is a relationship and handles repeatable files, we will also register the deleting event on the
        // parent model. that way we can control the deletion of the files when the parent model is deleted.
        if ($uploader->isRelationship() && $uploader->handleRepeatableFiles) {
            app('crud')->model::deleting(function ($entry) use ($uploader) {
                $uploader->deleteUploadedFiles($entry);
            });
        }

        app('UploadersRepository')->markAsHandled($uploader->getIdentifier());
    }

    /**
     * Return the uploader for the object being configured.
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
        $hasCustomUploader = isset($uploaderConfiguration['uploader']);

        if ($hasCustomUploader && ! is_a($uploaderConfiguration['uploader'], UploaderInterface::class, true)) {
            throw new Exception('Invalid uploader class provided for '.$this->crudObjectType.' type: '.$crudObject['type']);
        }

        if ($hasCustomUploader) {
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

        if ($uploader->useTemporaryUrl()) {
            $this->crudObject->temporary($uploader->useTemporaryUrl());
            $this->crudObject->expiration($uploader->getExpirationTimeInMinutes());
        }
    }

    private function getSubfieldModel(array $subfield, UploaderInterface $uploader)
    {
        if (! $uploader->isRelationship()) {
            return $subfield['baseModel'] ?? get_class(app('crud')->getModel());
        }

        if (in_array($subfield['relation_type'], ['BelongsToMany', 'MorphToMany'])) {
            return app('crud')->getModel()->{$subfield['baseEntity']}()->getPivotClass();
        }

        return $subfield['baseModel'];
    }

    private function getModelForRetrieveEvent(string $model, UploaderInterface $uploader)
    {
        if (! $uploader->isRelationship()) {
            return $model;
        }

        return is_a($model, Pivot::class, true) ? $this->crudObject->getAttributes()['model'] : $model;
    }
}
