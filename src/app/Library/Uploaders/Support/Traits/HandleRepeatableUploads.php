<?php

namespace Backpack\CRUD\app\Library\Uploaders\Support\Traits;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Uploaders\Support\Interfaces\UploaderInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @codeCoverageIgnore
 */
trait HandleRepeatableUploads
{
    public bool $handleRepeatableFiles = false;

    public null|string $repeatableContainerName = null;

    /*******************************
     * Setters - fluently configure the uploader
     *******************************/
    public function repeats(string $repeatableContainerName): self
    {
        $this->handleRepeatableFiles = true;

        $this->repeatableContainerName = $repeatableContainerName;

        return $this;
    }

    /*******************************
     * Getters
     *******************************/
    public function getRepeatableContainerName(): null|string
    {
        return $this->repeatableContainerName;
    }

    /*******************************
     * Default implementation methods
     *******************************/
    protected function uploadRepeatableFiles($values, $previousValues, $entry = null)
    {
    }

    protected function handleRepeatableFiles(Model $entry): Model
    {
        $values = collect(CRUD::getRequest()->get($this->getRepeatableContainerName()));
        $files = collect(CRUD::getRequest()->file($this->getRepeatableContainerName()));

        $value = $this->mergeValuesRecursive($values, $files);

        if ($this->isRelationship()) {
            if ($value->isEmpty()) {
                return $entry;
            }

            return $this->processRelationshipRepeatableUploaders($entry);
        }

        $processedEntryValues = $this->processRepeatableUploads($entry, $value)->toArray();

        if ($this->isFake()) {
            $fakeValues = $entry->{$this->getFakeAttribute()} ?? [];

            if (is_string($fakeValues)) {
                $fakeValues = json_decode($fakeValues, true);
            }

            $fakeValues[$this->getRepeatableContainerName()] = empty($processedEntryValues)
                                                        ? null
                                                        : (isset($entry->getCasts()[$this->getFakeAttribute()])
                                                            ? $processedEntryValues
                                                            : json_encode($processedEntryValues));

            $entry->{$this->getFakeAttribute()} = isset($entry->getCasts()[$this->getFakeAttribute()])
                                                            ? $fakeValues
                                                            : json_encode($fakeValues);

            return $entry;
        }

        $entry->{$this->getRepeatableContainerName()} = empty($processedEntryValues)
                                                        ? null
                                                        : (isset($entry->getCasts()[$this->getRepeatableContainerName()])
                                                            ? $processedEntryValues
                                                            : json_encode($processedEntryValues));

        return $entry;
    }

    private function processRelationshipRepeatableUploaders(Model $entry)
    {
        foreach (app('UploadersRepository')->getRepeatableUploadersFor($this->getRepeatableContainerName()) as $uploader) {
            $entry = $uploader->uploadRelationshipFiles($entry);
        }

        return $entry;
    }

    protected function uploadRelationshipFiles(Model $entry): Model
    {
        $entryValue = $this->getFilesFromEntry($entry);

        if ($this->handleMultipleFiles && is_string($entryValue)) {
            try {
                $entryValue = json_decode($entryValue, true);
            } catch (\Exception) {
                return $entry;
            }
        }

        if ($this->hasDeletedFiles($entryValue)) {
            $entry->{$this->getAttributeName()} = $this->uploadFiles($entry, false);
            $this->updatedPreviousFiles = $this->getEntryAttributeValue($entry);
        }

        if ($this->shouldKeepPreviousValueUnchanged($entry, $entryValue)) {
            $entry->{$this->getAttributeName()} = $this->updatedPreviousFiles ?? $this->getEntryOriginalValue($entry);

            return $entry;
        }

        if ($this->shouldUploadFiles($entryValue)) {
            $entry->{$this->getAttributeName()} = $this->uploadFiles($entry, $entryValue);
        }

        return $entry;
    }

    protected function getFilesFromEntry(Model $entry)
    {
        return $entry->getAttribute($this->getAttributeName());
    }

    protected function getEntryAttributeValue(Model $entry)
    {
        return $entry->{$this->getAttributeName()};
    }

    protected function getEntryOriginalValue(Model $entry)
    {
        return $entry->getOriginal($this->getAttributeName());
    }

    protected function processRepeatableUploads(Model $entry, Collection $values): Collection
    {
        foreach (app('UploadersRepository')->getRepeatableUploadersFor($this->getRepeatableContainerName()) as $uploader) {
            $uploadedValues = $uploader->uploadRepeatableFiles($values->pluck($uploader->getAttributeName())->toArray(), $this->getPreviousRepeatableValues($entry, $uploader));

            $values = $values->map(function ($item, $key) use ($uploadedValues, $uploader) {
                $item[$uploader->getAttributeName()] = $uploadedValues[$key] ?? null;

                return $item;
            });
        }

        return $values;
    }

    private function retrieveRepeatableFiles(Model $entry): Model
    {
        if ($this->isRelationship) {
            return $this->retrieveRepeatableRelationFiles($entry);
        }

        $repeatableUploaders = app('UploadersRepository')->getRepeatableUploadersFor($this->getRepeatableContainerName());

        if ($this->attachedToFakeField) {
            $values = $entry->{$this->attachedToFakeField};

            $values = is_string($values) ? json_decode($values, true) : $values;

            $values[$this->getAttributeName()] = isset($values[$this->getAttributeName()]) ? $this->getValueWithoutPath($values[$this->getAttributeName()]) : null;
            $entry->{$this->attachedToFakeField} = isset($entry->getCasts()[$this->attachedToFakeField]) ? $values : json_encode($values);

            return $entry;
        }

        $values = $entry->{$this->getRepeatableContainerName()};
        $values = is_string($values) ? json_decode($values, true) : $values;
        $values = array_map(function ($item) use ($repeatableUploaders) {
            foreach ($repeatableUploaders as $upload) {
                $item[$upload->getAttributeName()] = $this->getValuesWithPathStripped($item, $upload);
            }

            return $item;
        }, $values ?? []);

        $entry->{$this->getRepeatableContainerName()} = $values;

        return $entry;
    }

    private function retrieveRepeatableRelationFiles(Model $entry)
    {
        switch($this->getRepeatableRelationType()) {
            case 'BelongsToMany':
            case 'MorphToMany':
                $pivotClass = app('crud')->getModel()->{$this->getUploaderSubfield()['baseEntity']}()->getPivotClass();
                $pivotFieldName = 'pivot_'.$this->getAttributeName();
                $connectedEntry = new $pivotClass([$this->getAttributeName() => $entry->$pivotFieldName]);
                $entry->{$pivotFieldName} = $this->retrieveFiles($connectedEntry)->{$this->getAttributeName()};

                break;
            default:
                $entry = $this->retrieveFiles($entry);
        }

        return $entry;
    }

    private function getRepeatableRelationType()
    {
        return $this->getUploaderField()->getAttributes()['relation_type'];
    }

    private function getUploaderField()
    {
        return app('crud')->field($this->getRepeatableContainerName());
    }

    private function getUploaderSubfield()
    {
        return collect($this->getUploaderFieldSubfields())->where('name', '===', $this->getName())->first();
    }

    private function getUploaderFieldSubfields()
    {
        return $this->getUploaderField()->getAttributes()['subfields'];
    }

    private function deleteRepeatableFiles(Model $entry): void
    {
        if ($this->isRelationship) {
            $this->deleteRelationshipFiles($entry);

            return;
        }

        if ($this->attachedToFakeField) {
            $repeatableValues = $entry->{$this->attachedToFakeField}[$this->getRepeatableContainerName()] ?? null;
            $repeatableValues = is_string($repeatableValues) ? json_decode($repeatableValues, true) : $repeatableValues;
            $repeatableValues = collect($repeatableValues);
        }

        $repeatableValues ??= collect($entry->{$this->getRepeatableContainerName()});

        foreach (app('UploadersRepository')->getRepeatableUploadersFor($this->getRepeatableContainerName()) as $upload) {
            if (! $upload->shouldDeleteFiles()) {
                continue;
            }
            $values = $repeatableValues->pluck($upload->getName())->toArray();
            foreach ($values as $value) {
                if (! $value) {
                    continue;
                }

                if (is_array($value)) {
                    foreach ($value as $subvalue) {
                        Storage::disk($upload->getDisk())->delete($upload->getPath().$subvalue);
                    }

                    continue;
                }

                Storage::disk($upload->getDisk())->delete($upload->getPath().$value);
            }
        }
    }
    /*******************************
     * Helper methods
     *******************************/

    /**
     * Given two multidimensional arrays/collections, merge them recursively.
     */
    protected function mergeValuesRecursive(array|Collection $array1, array|Collection $array2): array|Collection
    {
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->mergeValuesRecursive($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Repeatable items send `_order_` parameter in the request.
     * This holds the order of the items in the repeatable container.
     */
    protected function getFileOrderFromRequest(): array
    {
        $items = CRUD::getRequest()->input('_order_'.$this->getRepeatableContainerName()) ?? [];

        array_walk($items, function (&$key, $value) {
            $requestValue = $key[$this->getName()] ?? null;
            $key = $this->handleMultipleFiles ? (is_string($requestValue) ? explode(',', $requestValue) : $requestValue) : $requestValue;
        });

        return $items;
    }

    private function getPreviousRepeatableValues(Model $entry, UploaderInterface $uploader): array
    {
        $previousValues = $entry->getOriginal($uploader->getRepeatableContainerName());

        if (! is_array($previousValues)) {
            $previousValues = json_decode($previousValues, true);
        }

        if (! empty($previousValues)) {
            $previousValues = array_column($previousValues, $uploader->getName());
        }

        return $previousValues ?? [];
    }

    private function getValuesWithPathStripped(array|string|null $item, UploaderInterface $uploader)
    {
        $uploadedValues = $item[$uploader->getName()] ?? null;
        if (is_array($uploadedValues)) {
            return array_map(function ($value) use ($uploader) {
                return $uploader->getValueWithoutPath($value);
            }, $uploadedValues);
        }

        return isset($uploadedValues) ? $uploader->getValueWithoutPath($uploadedValues) : null;
    }

    private function deleteRelationshipFiles(Model $entry): void
    {
        if (! is_a($entry, Pivot::class, true) &&
            ! $entry->relationLoaded($this->getRepeatableContainerName()) &&
            method_exists($entry, $this->getRepeatableContainerName())
        ) {
            $entry->loadMissing($this->getRepeatableContainerName());
        }

        foreach (app('UploadersRepository')->getRepeatableUploadersFor($this->getRepeatableContainerName()) as $uploader) {
            if ($uploader->shouldDeleteFiles()) {
                $uploader->deleteRepeatableRelationFiles($entry);
            }
        }
    }

    protected function deleteRepeatableRelationFiles(Model $entry)
    {
        match ($this->getRepeatableRelationType()) {
            'BelongsToMany', 'MorphToMany' => $this->deletePivotFiles($entry),
            default => $this->deleteRelatedFiles($entry),
        };
    }

    private function deleteRelatedFiles(Model $entry)
    {
        if (get_class($entry) === get_class(app('crud')->model)) {
            $relatedEntries = $entry->{$this->getRepeatableContainerName()} ?? [];
        }

        if (! is_a($relatedEntries ?? '', Collection::class, true)) {
            $relatedEntries = ! empty($relatedEntries) ? [$relatedEntries] : [$entry];
        }

        foreach ($relatedEntries as $relatedEntry) {
            $this->deleteFiles($relatedEntry);
        }
    }

    protected function deletePivotFiles(Pivot|Model $entry)
    {
        if (! is_a($entry, Pivot::class, true)) {
            $pivots = $entry->{$this->getRepeatableContainerName()};
            foreach ($pivots as $pivot) {
                $this->deletePivotModelFiles($pivot);
            }

            return;
        }

        $pivotAttributes = $entry->getAttributes();
        $connectedPivot = $entry->pivotParent->{$this->getRepeatableContainerName()}->where(function ($item) use ($pivotAttributes) {
            $itemPivotAttributes = $item->pivot->only(array_keys($pivotAttributes));

            return $itemPivotAttributes === $pivotAttributes;
        })->first();

        if (! $connectedPivot) {
            return;
        }

        $this->deletePivotModelFiles($connectedPivot);
    }

    private function deletePivotModelFiles(Pivot|Model $entry)
    {
        $files = $entry->getOriginal()['pivot_'.$this->getAttributeName()];

        if (! $files) {
            return;
        }

        if ($this->handleMultipleFiles && is_string($files)) {
            try {
                $files = json_decode($files, true);
            } catch (\Exception) {
                Log::error('Could not parse files for deletion pivot entry with key: '.$entry->getKey().' and uploader: '.$this->getName());

                return;
            }
        }

        if (is_array($files)) {
            foreach ($files as $value) {
                $value = Str::start($value, $this->getPath());
                Storage::disk($this->getDisk())->delete($value);
            }

            return;
        }

        $value = Str::start($files, $this->getPath());
        Storage::disk($this->getDisk())->delete($value);
    }
}
