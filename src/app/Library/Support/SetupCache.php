<?php

namespace Backpack\CRUD\app\Library\Support;

use Illuminate\Support\Facades\Cache;

abstract class SetupCache
{
    protected string $cachePrefix = 'setup_cache_';
    protected int $cacheDuration = 60; // minutes

    /**
     * Generate a cache key for the given identifier.
     */
    protected function generateCacheKey($identifier): string
    {
        return $this->cachePrefix.$identifier;
    }

    /**
     * Store data in the cache.
     */
    public function store($identifier, ...$args)
    {
        $cacheKey = $this->generateCacheKey($identifier);
        $data = $this->prepareDataForStorage(...$args);

        if ($data !== false && $data !== null) {
            Cache::forget($cacheKey);
            Cache::put($cacheKey, $data, now()->addMinutes($this->cacheDuration));

            return true;
        }

        return false;
    }

    /**
     * Apply cached data.
     */
    public function apply($identifier, ...$args)
    {
        $cacheKey = $this->generateCacheKey($identifier);
        $cachedData = Cache::get($cacheKey);

        if (! $cachedData) {
            return false;
        }

        return $this->applyFromCache($cachedData, ...$args);
    }

    /**
     * Get cached data without applying it.
     */
    public function get($identifier)
    {
        $cacheKey = $this->generateCacheKey($identifier);

        return Cache::get($cacheKey);
    }

    /**
     * Check if cache exists for the given identifier.
     */
    public function has($identifier): bool
    {
        $cacheKey = $this->generateCacheKey($identifier);

        return Cache::has($cacheKey);
    }

    /**
     * Remove cached data.
     */
    public function forget($identifier): bool
    {
        $cacheKey = $this->generateCacheKey($identifier);

        return Cache::forget($cacheKey);
    }

    /**
     * Set the cache prefix.
     */
    public function setCachePrefix(string $prefix): self
    {
        $this->cachePrefix = $prefix;

        return $this;
    }

    /**
     * Set the cache duration in minutes.
     */
    public function setCacheDuration(int $minutes): self
    {
        $this->cacheDuration = $minutes;

        return $this;
    }

    /**
     * Prepare data for storage in the cache.
     * This method should be implemented by child classes.
     */
    abstract protected function prepareDataForStorage(...$args);

    /**
     * Apply data from the cache.
     * This method should be implemented by child classes.
     */
    abstract protected function applyFromCache($cachedData, ...$args);
}
