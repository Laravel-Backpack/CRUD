<?php

namespace Backpack\CRUD\app\Library\CrudTesting;

interface CrudTestConfiguration
{
    /**
     * Setup the test environment.
     * Use this to mock things, set config values, etc.
     */
    public function setup();

    /**
     * Get the parameters to mock the current route with.
     *
     * @return array
     */
    public function getRouteParameters();

    /**
     * Get valid input for the create operation.
     *
     * @return array|null
     */
    public function validCreateInput($model);

    /**
     * Get valid input for the update operation.
     *
     * @return array|null
     */
    public function validUpdateInput($model);

    /**
     * Get invalid input to test validation failure.
     *
     * @return array|null
     */
    public function invalidInput();

    /**
     * Create a test entry for the model.
     *
     * @param  string  $model
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function createTestEntry(string $model, array $attributes = []);

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
     * Create multiple test entries.
     *
     * @param  int  $count
     * @param  array  $attributes
     * @return mixed
     */
    public function createTestEntries(int $count = 5, array $attributes = []);

    /**
     * Create a test entry for the model (instance method).
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createEntry(array $attributes = []);

    /**
     * Get a specific setting for the current operation.
     *
     * @param  string  $setting
     * @param  mixed  $default
     * @return mixed
     */
    public function getOperationSetting(string $setting, $default = null);
}
