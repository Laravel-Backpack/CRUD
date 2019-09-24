<?php

namespace Backpack\CRUD\ModelTraits\AstrotomicTranslatable;

use Illuminate\Database\Eloquent\Builder;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers as OriginalSluggableScopeHelpers;

trait SluggableScopeHelpers
{
    use OriginalSluggableScopeHelpers;

    /**
     * Query scope for finding a model by its primary slug.
     *
     * @param \Illuminate\Database\Eloquent\Builder $scope
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereSlug(Builder $scope, $slug)
    {
        return $scope->where($this->getSlugKeyName().'->'.$this->getLocale(), $slug);
    }

    /**
     * Find a model by its primary slug.
     *
     * @param string $slug
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public static function findBySlug($slug, array $columns = ['*'])
    {
        return static::whereSlug($slug)->first($columns);
    }

    /**
     * Find a model by its primary slug or throw an exception.
     *
     * @param string $slug
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function findBySlugOrFail($slug, array $columns = ['*'])
    {
        return static::whereSlug($slug)->firstOrFail($columns);
    }
}
