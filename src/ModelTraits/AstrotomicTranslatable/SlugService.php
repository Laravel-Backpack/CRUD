<?php

namespace Backpack\CRUD\ModelTraits\AstrotomicTranslatable;

use Illuminate\Database\Eloquent\Model;

class SlugService extends \Cviebrock\EloquentSluggable\Services\SlugService
{
    /**
     * Slug the current model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param bool $force
     * @return bool
     */
    public function slug(Model $model, $force = false)
    {
        $this->setModel($model);

        $attributes = [];

        foreach ($this->model->sluggable() as $attribute => $config) {
            if (is_numeric($attribute)) {
                $attribute = $config;
                $config = $this->getConfiguration();
            } else {
                $config = $this->getConfiguration($config);
            }

            $slug = $this->buildSlug($attribute, $config, $force);

            // customized for Backpack using AstrotomicTranslatable
            // save the attribute as a JSON
            $this->model->setAttribute($attribute, $slug);

            $attributes[] = $attribute;
        }

        return $this->model->isDirty($attributes);
    }

    /**
     * Checks if the slug should be unique, and makes it so if needed.
     *
     * @param string $slug
     * @param array $config
     * @param string $attribute
     * @return string
     */
    protected function makeSlugUnique($slug, array $config, $attribute)
    {
        if (! $config['unique']) {
            return $slug;
        }

        $separator = $config['separator'];

        // find all models where the slug is like the current one
        $list = $this->getExistingSlugs($slug, $attribute, $config);

        // if ...
        // 	a) the list is empty, or
        // 	b) our slug isn't in the list
        // ... we are okay
        if (
            $list->count() === 0 ||
            $list->contains($slug) === false
        ) {
            return $slug;
        }

        // if our slug is in the list, but
        // 	a) it's for our model, or
        //  b) it looks like a suffixed version of our slug
        // ... we are also okay (use the current slug)
        if ($list->has($this->model->getKey())) {
            $currentSlug = $list->get($this->model->getKey());

            if (
                $currentSlug === $slug ||
                strpos($currentSlug, $slug) === 0
            ) {
                return $currentSlug;
            }
        }

        $method = $config['uniqueSuffix'];
        if ($method === null) {
            $suffix = $this->generateSuffix($slug, $separator, $list);
        } elseif (is_callable($method)) {
            $suffix = call_user_func($method, $slug, $separator, $list);
        } else {
            throw new \UnexpectedValueException('Sluggable "reserved" for '.get_class($this->model).':'.$attribute.' is not null, an array, or a closure that returns null/array.');
        }

        return $slug.$separator.$suffix;
    }
}
