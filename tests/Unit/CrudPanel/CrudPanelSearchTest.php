<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\config\Models\User;
use Backpack\CRUD\Tests\config\Models\UserWithTranslations;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Search
 */
class CrudPanelSearchTest extends \Backpack\CRUD\Tests\config\CrudPanel\BaseCrudPanel
{
    private string $expectedDefaultColumnValue = '<span>'.PHP_EOL.'                        user'.PHP_EOL.'            </span>';

    public function setUp(): void
    {
        parent::setUp();
        $this->crudPanel->setModel(User::class);
    }

    #[DataProvider('columnsDefaultSearchLogic')]
    public function testItCanApplyCustomSearchLogicOnColumns($searchTerm, $columnType, $resultSql)
    {
        $this->crudPanel->addColumn([
            'name' => 'test',
            'type' => $columnType,
            'searchLogic' => $columnType,
            'tableColumn' => true,
            'entity' => $columnType === 'select' ? 'accountDetails' : ($columnType === 'select_multiple' ? 'articles' : false),
            'relation_type' => $columnType === 'select' ? 'HasOne' : ($columnType === 'select_multiple' ? 'HasMany' : false),
        ]);

        $this->crudPanel->applySearchTerm($searchTerm);

        $this->assertEquals($resultSql, $this->crudPanel->query->toRawSql());
    }

    public function testItDoesNotAttemptToSearchTheColumnIfSearchLogicIsDisabled()
    {
        $this->crudPanel->addColumn([
            'name' => 'test',
            'type' => 'text',
            'searchLogic' => false,
            'tableColumn' => true,
        ]);

        $this->crudPanel->applySearchTerm('test');

        $this->assertEquals('select * from "users"', $this->crudPanel->query->toRawSql());
    }

    public function testItDoesNotAttemptToApplyDefaultLogicIfColumnIsNotATableColumn()
    {
        $this->crudPanel->addColumn([
            'name' => 'test',
            'type' => 'text',
            'searchLogic' => 'text',
            'tableColumn' => false,
        ]);

        $this->crudPanel->applySearchTerm('test');

        $this->assertEquals('select * from "users"', $this->crudPanel->query->toRawSql());
    }

    public function testItValidateDateAndDatetimeSearchTermsAndDoesNotApplySearchIfValidationFails()
    {
        $this->crudPanel->addColumn([
            'name' => 'test',
            'type' => 'date',
            'searchLogic' => 'date',
            'tableColumn' => true,
        ]);

        $this->crudPanel->addColumn([
            'name' => 'test',
            'type' => 'datetime',
            'searchLogic' => 'datetime',
            'tableColumn' => true,
        ]);

        $this->crudPanel->applySearchTerm('invalid-date');

        $this->assertEquals('select * from "users"', $this->crudPanel->query->toRawSql());
    }

    public function testItCanApplySearchLogicFromClosure()
    {
        $this->crudPanel->addColumn([
            'name' => 'test',
            'type' => 'my_custom_type',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->where($column['name'], 'like', "%{$searchTerm}%");
            },
            'tableColumn' => true,
        ]);

        $this->crudPanel->applySearchTerm('test');

        $this->assertEquals('select * from "users" where ("test" like \'%test%\')', $this->crudPanel->query->toRawSql());
    }

    public function testItCanGetAndSetPersistentTable()
    {
        $this->crudPanel->enablePersistentTable(true);

        $this->assertTrue($this->crudPanel->getPersistentTable());

        $this->crudPanel->disablePersistentTable();

        $this->assertFalse($this->crudPanel->getPersistentTable());
    }

    public function testItCanGetPersistentTableFromConfig()
    {
        $this->assertNull($this->crudPanel->getPersistentTable());
    }

    public function testItCanGetAndSetTheResponsiveTable()
    {
        $this->crudPanel->enableResponsiveTable(true);

        $this->assertTrue($this->crudPanel->getResponsiveTable());

        $this->crudPanel->disableResponsiveTable();

        $this->assertFalse($this->crudPanel->getResponsiveTable());
    }

    public function testItCanGetPersistentTableDurationFromOperationSetting()
    {
        $this->crudPanel->setOperationSetting('persistentTableDuration', 10);

        $this->assertEquals(10, $this->crudPanel->getPersistentTableDuration());
    }

    public function testItCanGetPersistentTableDurantionFromConfig()
    {
        $this->assertEquals(false, $this->crudPanel->getPersistentTableDuration());

        config(['backpack.crud.operations.list.persistentTableDuration' => 10]);

        $this->assertEquals(10, $this->crudPanel->getPersistentTableDuration());
    }

    public function testItCanGetResponsiveTableFromConfig()
    {
        $this->assertEquals(false, $this->crudPanel->getResponsiveTable());

        config(['backpack.crud.operations.list.responsiveTable' => true]);

        $this->assertTrue($this->crudPanel->getResponsiveTable());
    }

    public function testItCanGetTheRenderedViewsForTheColumns()
    {
        $this->crudPanel->addColumn([
            'name' => 'name',
            'type' => 'test',
        ]);

        $entries = [$this->makeAUserModel()];

        $rowColumnsHtml = trim($this->crudPanel->getEntriesAsJsonForDatatables($entries, 1, 0)['data'][0][0]);

        $this->assertEquals($this->expectedDefaultColumnValue, $rowColumnsHtml);
    }

    public function testItRendersTheDetailsRow()
    {
        $this->crudPanel->addColumn([
            'name' => 'name',
            'type' => 'test',
        ]);

        $this->crudPanel->setOperationSetting('detailsRow', true);
        $entries = [$this->makeAUserModel()];

        $rowColumnsHtml = $this->crudPanel->getEntriesAsJsonForDatatables($entries, 1, 0)['data'][0][0];

        $this->assertStringContainsString('details-row-button', $rowColumnsHtml);
    }

    public function testItRendersTheBulkActions()
    {
        $this->crudPanel->addColumn([
            'name' => 'name',
            'type' => 'test',
        ]);

        $this->crudPanel->setOperationSetting('bulkActions', true);
        $entries = [$this->makeAUserModel()];

        $rowColumnsHtml = $this->crudPanel->getEntriesAsJsonForDatatables($entries, 1, 0)['data'][0][0];

        $this->assertStringContainsString('crud_bulk_actions_line_checkbox', $rowColumnsHtml);
    }

    public function testItRendersTheLineStackButtons()
    {
        $this->crudPanel->addColumn([
            'name' => 'name',
            'type' => 'test',
        ]);

        $this->crudPanel->button('test')->stack('line')->type('view')->content('backpack.theme-coreuiv2::buttons.test');
        $entries = [$this->makeAUserModel()];

        $rowColumnsHtml = $this->crudPanel->getEntriesAsJsonForDatatables($entries, 1, 0)['data'][0][1];

        $this->assertStringContainsString('btn-secondary', $rowColumnsHtml);
    }

    public function testItAppliesCustomOrderByPrimaryKeyForDatatables()
    {
        $this->crudPanel->applyDatatableOrder();

        $this->assertEquals('select * from "users" order by "id" desc', $this->crudPanel->query->toRawSql());
    }

    public function testItCanApplyACustomSearchLogic()
    {
        $this->crudPanel->addColumn([
            'name' => 'name',
            'type' => 'text',
            'tableColumn' => true,
            'orderLogic' => function ($query, $column, $searchTerm) {
                $query->orderBy('name', 'asc');
            },
        ]);

        $this->setupUserCreateRequest();
        $this->crudPanel->getRequest()->merge(['order' => [['column' => 0, 'dir' => 'asc']]]);

        $this->crudPanel->applyDatatableOrder();

        $this->assertEquals('select * from "users" order by "name" asc, "id" desc', $this->crudPanel->query->toRawSql());
    }

    public function testItDoesNotReplacePrimaryKeyIfItAlreadyExists()
    {
        $this->crudPanel->addColumn([
            'name' => 'name',
            'type' => 'text',
            'tableColumn' => true,
            'orderLogic' => function ($query, $column, $searchTerm) {
                $query->orderBy('id', 'asc');
            },
        ]);

        $this->setupUserCreateRequest();
        $this->crudPanel->getRequest()->merge(['order' => [['column' => 0, 'dir' => 'asc']]]);

        $this->crudPanel->applyDatatableOrder();

        $this->assertEquals('select * from "users" order by "id" asc', $this->crudPanel->query->toRawSql());
    }

    public function testItCanApplyDatatableOrderFromRequest()
    {
        $this->crudPanel->addColumn([
            'name' => 'name',
            'type' => 'test',
            'tableColumn' => true,
        ]);
        $this->setupUserCreateRequest();
        $this->crudPanel->getRequest()->merge(['order' => [['column' => 0, 'dir' => 'asc']]]);

        $this->crudPanel->applyDatatableOrder();

        $this->assertEquals('select * from "users" order by "name" asc, "id" desc', $this->crudPanel->query->toRawSql());
    }

    public function testItCanApplySearchLogicForTranslatableJsonColumns()
    {
        $this->crudPanel->setModel(UserWithTranslations::class);

        $this->crudPanel->addColumn([
            'name' => 'json',
            'type' => 'json',
            'tableColumn' => true,
        ]);
        $this->setupUserCreateRequest();
        $this->crudPanel->getRequest()->merge(['order' => [['column' => 0, 'dir' => 'asc']]]);

        $this->crudPanel->applyDatatableOrder();

        $this->assertEquals('select * from "users" order by json_extract("json", \'$."en"\') asc, "id" desc', $this->crudPanel->query->toRawSql());
    }

    public function testItCanApplySearchLogicForTranslatableColumns()
    {
        $this->crudPanel->setModel(UserWithTranslations::class);

        $this->crudPanel->addColumn([
            'name' => 'name',
            'type' => 'text',
            'tableColumn' => true,
        ]);
        $this->setupUserCreateRequest();
        $this->crudPanel->getRequest()->merge(['order' => [['column' => 0, 'dir' => 'asc']]]);

        $this->crudPanel->applyDatatableOrder();

        $this->assertEquals('select * from "users" order by "name" asc, "id" desc', $this->crudPanel->query->toRawSql());
    }

    /**
     * Translatable JSON columns should use case-insensitive JSON path search
     * for each active locale instead of searching the raw JSON string.
     */
    public function testItAppliesTranslatableSearchForJsonColumns()
    {
        $this->crudPanel->setModel(UserWithTranslations::class);

        $this->crudPanel->addColumn([
            'name' => 'json',
            'type' => 'text',
            'searchLogic' => 'text',
            'tableColumn' => true,
        ]);

        $this->crudPanel->applySearchTerm('Smith');

        $sql = $this->crudPanel->query->toRawSql();

        $this->assertStringContainsString('lower(', $sql);
        $this->assertStringContainsString('json_extract', $sql);
        $this->assertStringContainsString('"en"', $sql);
        $this->assertStringContainsString('like lower(', $sql);
    }

    public function testTranslatableAttributesAlwaysUseJsonPathSearch()
    {
        $this->crudPanel->setModel(UserWithTranslations::class);

        $this->crudPanel->addColumn([
            'name' => 'name',
            'type' => 'text',
            'searchLogic' => 'text',
            'tableColumn' => true,
        ]);

        $this->crudPanel->applySearchTerm('test');

        $this->assertStringContainsString(
            'json_extract("users"."name"',
            $this->crudPanel->query->toRawSql()
        );
    }

    /**
     * When the app fallback locale differs from the current locale, both should
     * be searched so results are not missed when content is stored in either locale.
     */
    public function testItSearchesAcrossCurrentAndFallbackLocales()
    {
        $this->crudPanel->setModel(UserWithTranslations::class);

        config(['app.fallback_locale' => 'fr']);

        $this->crudPanel->addColumn([
            'name' => 'json',
            'type' => 'text',
            'searchLogic' => 'text',
            'tableColumn' => true,
        ]);

        $this->crudPanel->applySearchTerm('smith');

        $sql = $this->crudPanel->query->toRawSql();

        // Both the current locale (en) and fallback locale (fr) must be searched
        $this->assertStringContainsString('"en"', $sql);
        $this->assertStringContainsString('"fr"', $sql);
    }

    /**
     * Non-translatable models should be unaffected and continue using standard LIKE search.
     */
    public function testTranslatableSearchDoesNotAffectNonTranslatableModels()
    {
        // Uses the default User model (no HasTranslations trait, translationEnabled() = false)
        // Use a column name that doesn't exist in the table (like other search tests) with explicit searchLogic
        $this->crudPanel->addColumn([
            'name' => 'title',
            'type' => 'text',
            'searchLogic' => 'text',
            'tableColumn' => true,
        ]);

        $this->crudPanel->applySearchTerm('test');

        // Regular User (not translatable) → standard LIKE, no JSON path extraction
        $this->assertEquals(
            'select * from "users" where ("users"."title" like \'%test%\')',
            $this->crudPanel->query->toRawSql()
        );
    }

    public static function columnsDefaultSearchLogic()
    {
        return [
            ['test', 'text', 'select * from "users" where ("users"."test" like \'%test%\')'],
            ['test', 'email', 'select * from "users" where ("users"."test" like \'%test%\')'],
            ['test', 'textarea', 'select * from "users" where ("users"."test" like \'%test%\')'],
            ['2023-12-24', 'date', 'select * from "users" where (strftime(\'%Y-%m-%d\', "users"."test") = cast(\'2023-12-24\' as text))'],
            ['2023-12-24', 'datetime', 'select * from "users" where (strftime(\'%Y-%m-%d\', "users"."test") = cast(\'2023-12-24\' as text))'],
            ['test', 'select', 'select * from "users" where (exists (select * from "account_details" where "users"."id" = "account_details"."user_id" and "account_details"."nickname" like \'%test%\'))'],
            ['test', 'select_multiple', 'select * from "users" where (exists (select * from "articles" where "users"."id" = "articles"."user_id" and "articles"."content" like \'%test%\'))'],
        ];
    }
}
