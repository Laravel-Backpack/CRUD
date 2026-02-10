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
}
