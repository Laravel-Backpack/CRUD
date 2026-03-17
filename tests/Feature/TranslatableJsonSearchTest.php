<?php

namespace Backpack\CRUD\Tests\Feature;

use Backpack\CRUD\Tests\BaseTestClass;
use Backpack\CRUD\Tests\Config\Models\TranslatableModel;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TranslatableJsonSearchTest extends BaseTestClass
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registerMigrations();
    }

    protected function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../config/database/migrations');
    }

    public function test_case_insensitive_search_on_translatable_field()
    {
        $model = TranslatableModel::create([
            'title' => 'Test Product Name',
            'description' => 'A great product',
            'locale' => 'en',
        ]);

        $results = TranslatableModel::where(function ($query) {
            $searchTerm = 'test';
            $locale = app()->getLocale();
            $tableName = (new TranslatableModel())->getTable();

            $query->orWhereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(\"$tableName\".\"title\", ?))) LIKE ?",
                ["\$$locale", '%'.strtolower($searchTerm).'%']
            );
        })->get();

        $this->assertCount(1, $results);
        $this->assertEquals($model->id, $results->first()->id);
    }

    public function test_exact_case_search_still_works()
    {
        $model = TranslatableModel::create([
            'title' => 'Smith Family Business',
            'description' => 'Professional Services',
            'locale' => 'en',
        ]);

        $results = TranslatableModel::where(function ($query) {
            $searchTerm = 'Smith';
            $locale = app()->getLocale();
            $tableName = (new TranslatableModel())->getTable();

            $query->orWhereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(\"$tableName\".\"title\", ?))) LIKE ?",
                ["\$$locale", '%'.strtolower($searchTerm).'%']
            );
        })->get();

        $this->assertCount(1, $results);
        $this->assertEquals($model->id, $results->first()->id);
    }

    public function test_lowercase_search_on_mixed_case_translatable()
    {
        TranslatableModel::create([
            'title' => 'TechVision Solutions',
            'description' => 'Technology Provider',
            'locale' => 'en',
        ]);

        $results = TranslatableModel::where(function ($query) {
            $searchTerm = 'techvision';
            $locale = app()->getLocale();
            $tableName = (new TranslatableModel())->getTable();

            $query->orWhereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(\"$tableName\".\"title\", ?))) LIKE ?",
                ["\$$locale", '%'.strtolower($searchTerm).'%']
            );
        })->get();

        $this->assertCount(1, $results);
    }

    public function test_no_match_for_non_existent_search_term()
    {
        TranslatableModel::create([
            'title' => 'Existing Company',
            'description' => 'Service Provider',
            'locale' => 'en',
        ]);

        $results = TranslatableModel::where(function ($query) {
            $searchTerm = 'nonexistent';
            $locale = app()->getLocale();
            $tableName = (new TranslatableModel())->getTable();

            $query->orWhereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(\"$tableName\".\"title\", ?))) LIKE ?",
                ["\$$locale", '%'.strtolower($searchTerm).'%']
            );
        })->get();

        $this->assertCount(0, $results);
    }

    public function test_partial_match_in_translatable_field()
    {
        TranslatableModel::create([
            'title' => 'GlobalTech Industries',
            'description' => 'Global Solutions',
            'locale' => 'en',
        ]);

        $results = TranslatableModel::where(function ($query) {
            $searchTerm = 'global';
            $locale = app()->getLocale();
            $tableName = (new TranslatableModel())->getTable();

            $query->orWhereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(\"$tableName\".\"title\", ?))) LIKE ?",
                ["\$$locale", '%'.strtolower($searchTerm).'%']
            );
        })->get();

        $this->assertCount(1, $results);
    }

    public function test_multiple_translatable_records_search()
    {
        TranslatableModel::create([
            'title' => 'UPPERCASE TITLE',
            'description' => 'First Record',
            'locale' => 'en',
        ]);

        TranslatableModel::create([
            'title' => 'lowercase title',
            'description' => 'Second Record',
            'locale' => 'en',
        ]);

        TranslatableModel::create([
            'title' => 'MixedCase Title',
            'description' => 'Third Record',
            'locale' => 'en',
        ]);

        $results = TranslatableModel::where(function ($query) {
            $searchTerm = 'title';
            $locale = app()->getLocale();
            $tableName = (new TranslatableModel())->getTable();

            $query->orWhereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(\"$tableName\".\"title\", ?))) LIKE ?",
                ["\$$locale", '%'.strtolower($searchTerm).'%']
            );
        })->get();

        $this->assertCount(3, $results);
    }
}
