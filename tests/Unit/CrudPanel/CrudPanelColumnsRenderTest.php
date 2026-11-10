<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\config\CrudPanel\BaseCrudPanel;
use Backpack\CRUD\Tests\config\Models\Article;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Search
 */
class CrudPanelColumnsRenderTest extends BaseCrudPanel
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->crudPanel->setModel(Article::class);
    }

    public function test_upload_column_escapes_the_value_by_default()
    {
        $html = $this->renderColumn(['name' => 'content', 'type' => 'upload', 'disk' => null], '<img src=x onerror=alert(1)>');

        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('&lt;img', $html);
    }

    public function test_url_column_links_only_to_safe_urls()
    {
        $html = $this->renderColumn(['name' => 'content', 'type' => 'url'], 'https://example.com/page');
        $this->assertStringContainsString('href="https://example.com/page"', $html);

        foreach (['javascript:alert(1)', ' JavaScript:alert(1)', "java\tscript:alert(1)", 'data:text/html,<script>alert(1)</script>'] as $value) {
            $this->crudPanel->removeColumn('content');
            $html = $this->renderColumn(['name' => 'content', 'type' => 'url'], $value);

            $this->assertStringNotContainsString('href="'.e($value).'"', $html);
            $this->assertDoesNotMatchRegularExpression('/href="\s*(java|data)/i', $html);
        }
    }

    public function test_image_column_links_only_to_safe_urls()
    {
        $html = $this->renderColumn(['name' => 'content', 'type' => 'image'], 'javascript://%0aalert(1)');

        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function test_upload_columns_link_only_to_safe_urls()
    {
        $html = $this->renderColumn(['name' => 'content', 'type' => 'upload', 'disk' => null], 'javascript://%0aalert(1)');
        $this->assertDoesNotMatchRegularExpression('/href="javascript/i', $html);

        $this->crudPanel->removeColumn('content');
        $html = $this->renderColumn(['name' => 'content', 'type' => 'upload_multiple', 'disk' => null], ['javascript:alert(1)']);
        $this->assertDoesNotMatchRegularExpression('/href="javascript/i', $html);
    }

    private function renderColumn(array $column, $value): string
    {
        $this->crudPanel->addColumn($column);

        $entry = new Article();
        $entry->{$column['name']} = $value;

        return (string) $this->crudPanel->getCellView($this->crudPanel->columns()[$column['name']], $entry);
    }
}
