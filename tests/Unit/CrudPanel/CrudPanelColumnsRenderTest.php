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

    private function renderColumn(array $column, $value): string
    {
        $this->crudPanel->addColumn($column);

        $entry = new Article();
        $entry->{$column['name']} = $value;

        return (string) $this->crudPanel->getCellView($this->crudPanel->columns()[$column['name']], $entry);
    }
}
