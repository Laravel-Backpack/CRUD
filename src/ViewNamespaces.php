<?php

namespace Backpack\CRUD;

class ViewNamespaces
{
    private static $viewNamespaces = [];

    /**
     * Return all the view namespaces including the ones stored in the laravel config files.
     *
     * @param  string  $domain  (eg. fields, filters, buttons)
     * @return array
     */
    public static function getFor(string $domain)
    {
        $viewNamespacesFromConfig = self::getFromConfigFor($domain);

        return array_unique(array_merge($viewNamespacesFromConfig, self::getForDomain($domain)));
    }

    /**
     * Add view namespaces for a given domain.
     *
     * @param  string  $domain  (eg. fields, filters, buttons)
     * @param  string|array  $viewNamespaces
     * @return void
     */
    public static function addFor(string $domain, $viewNamespaces)
    {
        foreach ((array) $viewNamespaces as $viewNamespace) {
            if (! in_array($viewNamespace, self::getForDomain($domain))) {
                self::$viewNamespaces[$domain][] = $viewNamespace;
            }
        }
    }

    /**
     * Return the namespaces stored for a given domain.
     *
     * @param  string  $domain
     */
    private static function getForDomain(string $domain)
    {
        return self::$viewNamespaces[$domain] ?? [];
    }

    /**
     * Return the array of view namespaces for backpack components from the Laravel config files.
     * It uses the default `backpack.crud.view_namespaces` key or a custom provided key.
     *
     * @param  string  $domain
     * @param  mixed  $customConfigKey
     * @return array
     */
    private static function getFromConfigFor(string $domain, $customConfigKey = null)
    {
        return config($customConfigKey ?? 'backpack.crud.view_namespaces.'.$domain) ??
            [config('backpack.ui.view_namespace').$domain] ??
            [config('backpack.ui.view_namespace_fallback').$domain];
    }

    /**
     * Return all the view namespaces using a developer provided config key.
     * Allow developer to use view namespaces from other config keys.
     *
     * @param  string  $domain  (eg. fields, filters, buttons)
     * @param  string  $viewNamespacesFromConfigKey
     * @return array
     */
    public static function getWithFallbackFor(string $domain, string $viewNamespacesFromConfigKey)
    {
        $viewNamespacesFromConfig = self::getFromConfigFor($domain, $viewNamespacesFromConfigKey);

        return array_unique(array_merge($viewNamespacesFromConfig, self::getFor($domain)));
    }

    /**
     * This is an helper function that returns the view namespace with the view name appended.
     * It's useful to use in blade templates with `@includeFirst(ViewNamespaces::getViewPathsFor('columns', 'some_column'))`.
     *
     * @param  string  $domain
     * @param  string  $viewName
     * @return array
     */
    public static function getViewPathsFor(string $domain, string $viewName)
    {
        return array_map(function ($item) use ($viewName) {
            return $item.'.'.$viewName;
        }, self::getFor($domain));
    }

    /**
     * Get view paths for a domain and view name with fallback support.
     * This is useful for @includeFirst() calls that need a guaranteed fallback.
     *
     * @param  string  $domain  (eg. fields, filters, buttons, columns)
     * @param  string  $viewName  (eg. text, select, checkbox)
     * @param  ?string  $fallbackViewPath  (eg. 'crud::columns.text')
     * @return array
     */
    public static function getViewPathsWithFallbackFor(string $domain, string $viewName, ?string $fallbackViewPath = null)
    {
        $paths = self::getViewPathsFor($domain, $viewName);

        // Add fallback if provided and not already in the list
        if ($fallbackViewPath && ! in_array($fallbackViewPath, $paths)) {
            $paths[] = $fallbackViewPath;
        }

        return $paths;
    }
}
