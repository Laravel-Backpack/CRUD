<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Update
{
    /*
    |--------------------------------------------------------------------------
    |                                   UPDATE
    |--------------------------------------------------------------------------
    */

    /**
     * Update a row in the database.
     *
     * @param  int  $id  The entity's id
     * @param  array  $input  All inputs to be updated.
     * @return object
     */
    public function update($id, $input)
    {
        $item = $this->model->findOrFail($id);

        [$directInputs, $relationInputs] = $this->splitInputIntoDirectAndRelations($input);
        $updated = $item->update($directInputs);
        $this->createRelationsForItem($item, $relationInputs);

        return $item;
    }

    /**
     * Get all fields needed for the EDIT ENTRY form.
     *
     * @param  int  $id  The id of the entry that is being edited.
     * @return array The fields with attributes, fake attributes and values.
     */
    public function getUpdateFields($id = false)
    {
        $fields = $this->fields();
        $entry = ($id != false) ? $this->getEntry($id) : $this->getCurrentEntry();

        foreach ($fields as &$field) {
            $field['value'] = $field['value'] ?? $this->getModelAttributeValue($entry, $field);
        }

        // always have a hidden input for the entry id
        if (! array_key_exists('id', $fields)) {
            $fields['id'] = [
                'name'  => $entry->getKeyName(),
                'value' => $entry->getKey(),
                'type'  => 'hidden',
            ];
        }

        return $fields;
    }

    /**
     * Get the value of the 'name' attribute from the declared relation model in the given field.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model  The current CRUD model.
     * @param  array  $field  The CRUD field array.
     * @return mixed The value of the 'name' attribute from the relation model.
     */
    private function getModelAttributeValue($model, $field)
    {
        $model = $model->withFakes();

        $fieldEntity = $field['entity'] ?? false;
        $fakeField = $field['fake'] ?? false;

        if ($fieldEntity && ! $fakeField) {
            return $this->getModelAttributeValueFromRelationship($model, $field);
        }

        if (is_string($field['name'])) {
            return $model->{$field['name']};
        }

        if (is_array($field['name'])) {
            $result = [];
            foreach ($field['name'] as $name) {
                $result[] = $model->{$name};
            }

            return $result;
        }
    }

    /**
     * Returns the value of the given attribute in the relationship.
     * It takes into account nested relationships.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array  $field
     * @return mixed
     */
    private function getModelAttributeValueFromRelationship($model, $field)
    {
        [$relatedModel, $relationMethod] = $this->getModelAndMethodFromEntity($model, $field);

        if (! method_exists($relatedModel, $relationMethod)) {
            return $relatedModel->{$relationMethod};
        }

        $relation = $relatedModel->{$relationMethod}();
        $relationType = Str::afterLast(get_class($relation), '\\');

        switch ($relationType) {
            case 'MorphMany':
            case 'HasMany':
            case 'BelongsToMany':
            case 'MorphToMany':
                $relationModels = $relatedModel->{$relationMethod};
                $result = collect();

                foreach ($relationModels as $model) {
                    $model = $this->setupRelatedModelLocale($model);
                    // when subfields are NOT set we don't need to get any more values
                    // we just return the plain models as we only need the ids
                    if (! isset($field['subfields'])) {
                        $result->push($model);
                        continue;
                    }
                    // when subfields are set we need to parse their values so they can be displayed
                    switch ($relationType) {
                        case 'HasMany':
                        case 'MorphMany':
                            // we will get model direct attributes and merge with subfields values.
                            $directAttributes = $this->getModelWithFakes($model)->getAttributes();
                            $result->push(array_merge($directAttributes, $this->getSubfieldsValues($field['subfields'], $model)));
                            break;

                        case 'BelongsToMany':
                        case 'MorphToMany':
                            // for any given model, we grab the attributes that belong to our pivot table.
                            $item = $model->{$relation->getPivotAccessor()}->getAttributes();
                            $item[$relationMethod] = $model->getKey();
                            $result->push($item);
                            break;
                    }
                }

                return $result;
                break;
            case 'HasOne':
            case 'MorphOne':
                if (! method_exists($relatedModel, $relationMethod)) {
                    return;
                }

                $model = $relatedModel->{$relationMethod};

                if (! $model) {
                    return;
                }

                $model = $this->setupRelatedModelLocale($model);
                $model = $this->getModelWithFakes($model);

                // if `entity` contains a dot here it means developer added a main HasOne/MorphOne relation with dot notation
                if (Str::contains($field['entity'], '.')) {
                    return $model->{Str::afterLast($field['entity'], '.')};
                }

                // when subfields exists developer used the repeatable interface to manage this relation
                if ($field['subfields']) {
                    return [$this->getSubfieldsValues($field['subfields'], $model)];
                }

                return $this->getModelWithFakes($model);

                break;
            case 'BelongsTo':
                if ($relatedModel->{$relationMethod}) {
                    return $relatedModel->{$relationMethod}->getKey();
                }

                return $relatedModel->{$relationMethod};
                break;
            default:
                return $relatedModel->{$relationMethod};
        }
    }

    /**
     * Set the locale on the related models.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function setupRelatedModelLocale($model)
    {
        if (method_exists($model, 'translationEnabled') && $model->translationEnabled()) {
            $locale = request('_locale', app()->getLocale());
            if (in_array($locale, array_keys($model->getAvailableLocales()))) {
                $model->setLocale($locale);
                $model->useFallbackLocale = false;
            }
        }

        return $model;
    }

    /**
     * This function checks if the provided model uses the CrudTrait.
     * If IT DOES it adds the fakes to the model attributes.
     * Otherwise just return the model back.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function getModelWithFakes($model)
    {
        if (in_array(\Backpack\CRUD\app\Models\Traits\CrudTrait::class, class_uses_recursive($model))) {
            return $model->withFakes();
        }

        return $model;
    }

    /**
     * Returns the model and the method from the relation string starting from the provided model.
     *
     * @param  Illuminate\Database\Eloquent\Model  $model
     * @param  array  $field
     * @return array
     */
    private function getModelAndMethodFromEntity($model, $field)
    {
        // HasOne and MorphOne relations contains the field in the relation string. We want only the relation part.
        $relationEntity = $this->getOnlyRelationEntity($field);

        $relationArray = explode('.', $relationEntity);

        $relatedModel = array_reduce(array_splice($relationArray, 0, -1), function ($obj, $method) {
            // if the string ends with `_id` we strip it out
            $method = Str::endsWith($method, '_id') ? Str::replaceLast('_id', '', $method) : $method;

            return $obj->{$method} ? $obj->{$method} : $obj;
        }, $model);

        $relationMethod = Str::afterLast($relationEntity, '.');

        return [$relatedModel, $relationMethod];
    }

    /**
     * Return the subfields values from the related model.
     *
     * @param  array  $subfields
     * @param  \Illuminate\Database\Eloquent\Model  $relatedModel
     * @return array
     */
    private function getSubfieldsValues($subfields, $relatedModel)
    {
        $result = [];
        foreach ($subfields as $subfield) {
            $name = is_string($subfield) ? $subfield : $subfield['name'];
            // if the subfield name does not contain a dot we just need to check
            // if it has subfields and return the result accordingly.
            foreach ((array) $subfield['name'] as $name) {
                if (! Str::contains($name, '.')) {
                    // when subfields are present, $relatedModel->{$name} returns a model instance
                    // otherwise returns the model attribute.
                    if ($relatedModel->{$name} && isset($subfield['subfields'])) {
                        $result[$name] = [$relatedModel->{$name}->only(array_column($subfield['subfields'], 'name'))];
                    } else {
                        $result[$name] = $relatedModel->{$name};
                    }
                } else {
                    // if the subfield name contains a dot, we are going to iterate through
                    // those parts to get the last connected part and parse it for returning.
                    // $iterator would be either a string (the attribute in model, eg: street)
                    // or a model instance (eg: AddressModel)
                    $iterator = $relatedModel;

                    foreach (explode('.', $name) as $part) {
                        $iterator = $iterator->$part;
                    }

                    Arr::set($result, $name, is_a($iterator, 'Illuminate\Database\Eloquent\Model', true) ? $this->getModelWithFakes($iterator)->getAttributes() : $iterator);
                }
            }
        }

        return $result;
    }
}
