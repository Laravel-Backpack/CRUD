<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Models\TranslatableModel;

/**
 * @covers Backpack\CRUD\app\Models\Traits\HasTranslatableFields
 */
class TranslatableFieldsTest extends BaseDBCrudPanel
{
    /**
     * Setup function for each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->crudPanel->setModel(TranslatableModel::class);

        $this->crudPanel->field('title');
        $this->crudPanel->field('description');
    }

    public function testFieldsGetsTranslated()
    {
        $this->crudPanel->create([
            'title' => 'english title',
            'description' => 'english description',
        ]);

        $model = TranslatableModel::first();

        $this->assertEquals('english title', $model->title);

        config(['app.locale' => 'fr']);

        $this->assertEquals('english title', $model->title);

        $this->crudPanel->update(1, ['title' => 'french title']);

        $model->refresh();

        $this->assertEquals('french title', $model->title);
    }

    public function testUpdateSetsTheEditedLocaleOnTheEntry()
    {
        $this->crudPanel->create([
            'title' => 'english title',
        ]);

        // anything reading translatable attributes while saving (eg. sluggable, observers)
        // should get the values in the locale being edited, not in the app locale
        $titleWhileSaving = null;
        TranslatableModel::saving(function ($entry) use (&$titleWhileSaving) {
            $titleWhileSaving = $entry->title;
        });

        $entry = $this->crudPanel->update(1, [
            'title' => 'french title',
            '_locale' => 'fr',
        ]);

        $this->assertEquals('fr', $entry->locale);
        $this->assertEquals('french title', $entry->title);
        $this->assertEquals('french title', $titleWhileSaving);
        $this->assertEquals('english title', $entry->getTranslation('title', 'en'));
    }

    public function testUpdateWithoutLocaleInputUsesTheAppLocale()
    {
        $this->crudPanel->create([
            'title' => 'english title',
        ]);

        app()->setLocale('fr');

        $titleWhileSaving = null;
        TranslatableModel::saving(function ($entry) use (&$titleWhileSaving) {
            $titleWhileSaving = $entry->title;
        });

        // a _locale in the request but not in the input is not used to save, so it must not be used to read either
        $this->crudPanel->getRequest()->merge(['_locale' => 'es']);

        $entry = $this->crudPanel->update(1, [
            'title' => 'french title',
        ]);

        $this->assertEquals('fr', $entry->locale);
        $this->assertEquals('french title', $titleWhileSaving);
        $this->assertEquals('french title', $entry->getTranslation('title', 'fr'));
        $this->assertEquals('english title', $entry->getTranslation('title', 'en'));
    }

    public function testGetEntryWithoutFakesSetsTheRequestLocaleOnTheEntry()
    {
        $this->crudPanel->create([
            'title' => 'english title',
        ]);
        $this->crudPanel->update(1, [
            'title' => 'french title',
            '_locale' => 'fr',
        ]);

        $this->crudPanel->getRequest()->merge(['_locale' => 'fr']);

        $entry = $this->crudPanel->getEntryWithoutFakes(1);

        $this->assertEquals('fr', $entry->locale);
        $this->assertEquals('french title', $entry->title);
    }

    public function testCreateReadsTranslatableAttributesInTheAppLocale()
    {
        app()->setLocale('fr');

        $titleWhileSaving = null;
        TranslatableModel::saving(function ($entry) use (&$titleWhileSaving) {
            $titleWhileSaving = $entry->title;
        });

        $entry = $this->crudPanel->create([
            'title' => 'french title',
        ]);

        $this->assertEquals('french title', $titleWhileSaving);
        $this->assertEquals('french title', $entry->title);
        $this->assertEquals('french title', TranslatableModel::find(1)->getTranslation('title', 'fr'));
    }
}
