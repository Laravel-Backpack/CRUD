<?php

namespace Backpack\CRUD\app\Library\CrudTesting;

interface CrudTestConfiguration
{
    /**
     * Get the parameters to mock the current route with.
     *
     * @return array
     */
    public function getRouteParameters();

    /**
     * Get the database assertion input for the model.
     *
     * @param  string  $model
     * @param  array  $data
     * @return array
     */
    public static function getDatabaseAssertInput(string $model, array $data = []): array;

    /**
     * Authenticate as admin.
     *
     * @param  object  $testCase
     * @return object
     */
    public function actingAsAdmin($testCase);

    /**
     * Get the CRUD URL.
     *
     * @param  string|null  $path
     * @return string
     */
    public function getCrudUrl(?string $path = null): string;

    /**
     * Get a specific setting for the current operation.
     *
     * @param  string  $setting
     * @param  mixed  $default
     * @return mixed
     */
    public function getOperationSetting(string $setting, $default = null);
}
