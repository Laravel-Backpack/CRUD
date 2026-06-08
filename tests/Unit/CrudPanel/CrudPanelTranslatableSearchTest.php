<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Models\TranslatableModel;

/**
 * Integration tests for translatable column search.
 *
 * Unlike the SQL-assertion tests in CrudPanelSearchTest, these tests run the
 * generated query against a real (SQLite) database and assert that the correct
 * records are (or are not) returned.
 *
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Search
 */
class CrudPanelTranslatableSearchTest extends BaseDBCrudPanel
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->crudPanel->setModel(TranslatableModel::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Create a TranslatableModel with per-locale title values.
     * Accepts: ['en' => 'Smith Electric', 'fr' => 'Électricité Smith'].
     */
    private function makeRecord(array $titlesByLocale): TranslatableModel
    {
        $model = new TranslatableModel();
        foreach ($titlesByLocale as $locale => $value) {
            $model->setTranslation('title', $locale, $value);
        }
        $model->save();

        return $model;
    }

    private function addTitleColumn(): void
    {
        $this->crudPanel->addColumn([
            'name' => 'title',
            'type' => 'text',
            'tableColumn' => true,
        ]);
    }

    private function runSearch(string $term): \Illuminate\Database\Eloquent\Collection
    {
        $this->crudPanel->applySearchTerm($term);

        return $this->crudPanel->query->get();
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    /**
     * A basic exact-case match must return the matching row and its content.
     */
    public function testItReturnsMatchingRecordWhenSearchTermMatchesStoredValue()
    {
        $this->makeRecord(['en' => 'Smith Electric']);
        $this->makeRecord(['en' => 'Jones Hardware']);

        $this->addTitleColumn();
        $results = $this->runSearch('Smith');

        $this->assertCount(1, $results);
        $this->assertEquals('Smith Electric', $results->first()->getTranslation('title', 'en'));
    }

    /**
     * The core bug: searching in lowercase must still find a value stored in
     * mixed case, and vice versa — search must be case-insensitive.
     */
    public function testSearchIsCaseInsensitive()
    {
        $this->makeRecord(['en' => 'Smith Electric']);
        $this->makeRecord(['en' => 'Jones Hardware']);

        $this->addTitleColumn();

        $results = $this->runSearch('smith');
        $this->assertCount(1, $results);
        $this->assertEquals('Smith Electric', $results->first()->getTranslation('title', 'en'));

        $this->crudPanel->query = clone $this->crudPanel->totalQuery;

        $results = $this->runSearch('SMITH');
        $this->assertCount(1, $results);
    }

    /**
     * A partial substring match must work (LIKE '%term%').
     */
    public function testItMatchesOnPartialSubstring()
    {
        $this->makeRecord(['en' => 'Smith Electric']);
        $this->makeRecord(['en' => 'Jones Hardware']);

        $this->addTitleColumn();
        $results = $this->runSearch('lectric');

        $this->assertCount(1, $results);
        $this->assertEquals('Smith Electric', $results->first()->getTranslation('title', 'en'));
    }

    /**
     * A search term that matches no row must return an empty collection.
     */
    public function testItReturnsEmptyCollectionWhenNoMatchFound()
    {
        $this->makeRecord(['en' => 'Smith Electric']);

        $this->addTitleColumn();
        $results = $this->runSearch('xyz-no-match-anywhere');

        $this->assertCount(0, $results);
    }

    /**
     * When records have multiple locales, searching with a non-default locale
     * finds content stored in that locale.
     */
    public function testItSearchesInTheCurrentAppLocale()
    {
        $this->makeRecord(['en' => 'Smith Electric',   'fr' => 'Électricité Smith']);
        $this->makeRecord(['en' => 'Jones Hardware',   'fr' => 'Quincaillerie Jones']);

        config(['app.locale' => 'fr']);

        $this->addTitleColumn();
        $results = $this->runSearch('quincaillerie');

        $this->assertCount(1, $results);
        $this->assertEquals('Jones Hardware', $results->first()->getTranslation('title', 'en'));
    }

    /**
     * When a term exists only under the fallback locale key, it must still
     * be found because the fallback locale is included in the search locales.
     */
    public function testItSearchesInTheFallbackLocale()
    {
        $this->makeRecord(['fr' => 'Quincaillerie Jones']);

        config(['app.locale' => 'en', 'app.fallback_locale' => 'fr']);

        $this->addTitleColumn();
        $results = $this->runSearch('quincaillerie');

        $this->assertCount(1, $results);
    }

    /**
     * Multiple matching rows must all be returned regardless of which locale
     * holds the matching value.
     */
    public function testItReturnsAllMatchingRowsAcrossLocales()
    {
        $this->makeRecord(['en' => 'Hardware Store',    'fr' => 'Quincaillerie']);
        $this->makeRecord(['en' => 'Hardware Depot']);
        $this->makeRecord(['en' => 'Electric Supplies']);

        $this->addTitleColumn();
        $results = $this->runSearch('Hardware');

        $this->assertCount(2, $results);
    }

    /**
     * Content stored only in a locale that is NOT in the search locales must
     * not be surfaced — the search is scoped to the current + fallback locales.
     */
    public function testItDoesNotReturnRowsWithNoMatchingTranslationInSearchLocales()
    {
        $this->makeRecord(['en' => 'Smith Electric']);
        $this->makeRecord(['fr' => 'Quincaillerie']);

        config(['app.locale' => 'en', 'app.fallback_locale' => 'en', 'backpack.crud.locales' => []]);

        $this->addTitleColumn();
        $results = $this->runSearch('quincaillerie');

        $this->assertCount(0, $results);
    }
}
