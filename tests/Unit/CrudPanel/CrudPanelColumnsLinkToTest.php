<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\app\Library\CrudPanel\CrudColumn;
use Backpack\CRUD\Tests\config\Models\Article;
use Backpack\CRUD\Tests\config\Models\User;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Columns
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\ColumnsProtectedMethods
 * @covers Backpack\CRUD\app\Library\CrudPanel\CrudColumn
 * @covers Backpack\CRUD\app\Library\CrudPanel\CrudPanel
 */
class CrudPanelColumnsLinkToTest extends \Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->crudPanel->setOperation('list');
    }

    public function testColumnLinkToThrowsExceptionWhenNotAllRequiredParametersAreFilled()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Route [article.show.detail] expects parameters [id, detail]. Insufficient parameters provided in column: [articles].');
        $this->crudPanel->column('articles')->entity('articles')->linkTo('article.show.detail', ['test' => 'testing']);
    }

    public function testItThrowsExceptionIfRouteNotFound()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Route [users.route.doesnt.exist] not found while building the link for column [id].');

        CrudColumn::name('id')->linkTo('users.route.doesnt.exist')->toArray();
    }

    public function testColumnLinkToWithRouteNameOnly()
    {
        $this->crudPanel->column('articles')->entity('articles')->linkTo('articles.show');
        $columnArray = $this->crudPanel->columns()['articles'];
        $reflection = new \ReflectionFunction($columnArray['wrapper']['href']);
        $arguments = $reflection->getClosureUsedVariables();
        $this->crudPanel->entry = Article::first();
        $url = $columnArray['wrapper']['href']($this->crudPanel, $columnArray, $this->crudPanel->entry, 1);
        $this->assertEquals('articles.show', $arguments['route']);
        $this->assertCount(1, $arguments['parameters']);
        $this->assertEquals('http://localhost/admin/articles/1/show', $url);
    }

    public function testColumnLinkToWithRouteNameAndAdditionalParameters()
    {
        $this->crudPanel->column('articles')->entity('articles')->linkTo('articles.show', ['test' => 'testing', 'test2' => 'testing2']);
        $columnArray = $this->crudPanel->columns()['articles'];
        $reflection = new \ReflectionFunction($columnArray['wrapper']['href']);
        $arguments = $reflection->getClosureUsedVariables();
        $this->assertEquals('articles.show', $arguments['route']);
        $this->assertCount(3, $arguments['parameters']);
        $this->crudPanel->entry = Article::first();
        $url = $columnArray['wrapper']['href']($this->crudPanel, $columnArray, $this->crudPanel->entry, 1);
        $this->assertEquals('http://localhost/admin/articles/1/show?test=testing&test2=testing2', $url);
    }

    public function testColumnLinkToWithCustomParameters()
    {
        $this->crudPanel->column('articles')->entity('articles')->linkTo('article.show.detail', ['detail' => 'testing', 'otherParam' => 'test']);
        $columnArray = $this->crudPanel->columns()['articles'];
        $this->crudPanel->entry = Article::first();
        $url = $columnArray['wrapper']['href']($this->crudPanel, $columnArray, $this->crudPanel->entry, 1);
        $this->assertEquals('http://localhost/admin/articles/1/show/testing?otherParam=test', $url);
    }

    public function testColumnLinkToWithCustomClosureParameters()
    {
        $this->crudPanel->column('articles')
                        ->entity('articles')
                        ->linkTo('article.show.detail', ['detail' => fn ($entry, $related_key) => $related_key, 'otherParam' => fn ($entry) => $entry->content]);
        $columnArray = $this->crudPanel->columns()['articles'];
        $this->crudPanel->entry = Article::first();
        $url = $columnArray['wrapper']['href']($this->crudPanel, $columnArray, $this->crudPanel->entry, 1);
        $this->assertEquals('http://localhost/admin/articles/1/show/1?otherParam=Some%20Content', $url);
    }

    public function testColumnLinkToDontAutoInferParametersIfAllProvided()
    {
        $this->crudPanel->column('articles')
                        ->entity('articles')
                        ->linkTo('article.show.detail', ['id' => 123, 'detail' => fn ($entry, $related_key) => $related_key, 'otherParam' => fn ($entry) => $entry->content]);
        $columnArray = $this->crudPanel->columns()['articles'];
        $this->crudPanel->entry = Article::first();
        $url = $columnArray['wrapper']['href']($this->crudPanel, $columnArray, $this->crudPanel->entry, 1);
        $this->assertEquals('http://localhost/admin/articles/123/show/1?otherParam=Some%20Content', $url);
    }

    public function testColumnLinkToAutoInferAnySingleParameter()
    {
        $this->crudPanel->column('articles')
                        ->entity('articles')
                        ->linkTo('article.show.detail', ['id' => 123, 'otherParam' => fn ($entry) => $entry->content]);
        $columnArray = $this->crudPanel->columns()['articles'];
        $this->crudPanel->entry = Article::first();
        $url = $columnArray['wrapper']['href']($this->crudPanel, $columnArray, $this->crudPanel->entry, 1);
        $this->assertEquals('http://localhost/admin/articles/123/show/1?otherParam=Some%20Content', $url);
    }

    public function testColumnLinkToWithClosure()
    {
        $this->crudPanel->column('articles')
                        ->entity('articles')
                        ->linkTo(fn ($entry) => route('articles.show', $entry->content));
        $columnArray = $this->crudPanel->columns()['articles'];
        $this->crudPanel->entry = Article::first();
        $url = $columnArray['wrapper']['href']($this->crudPanel, $columnArray, $this->crudPanel->entry, 1);
        $this->assertEquals('http://localhost/admin/articles/Some%20Content/show', $url);
    }

    public function testColumnArrayDefinitionLinkToRouteAsClosure()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->column([
            'name' => 'articles',
            'entity' => 'articles',
            'linkTo' => fn ($entry) => route('articles.show', ['id' => $entry->id, 'test' => 'testing']),
        ]);
        $columnArray = $this->crudPanel->columns()['articles'];
        $this->crudPanel->entry = Article::first();
        $url = $columnArray['wrapper']['href']($this->crudPanel, $columnArray, $this->crudPanel->entry, 1);
        $this->assertEquals('http://localhost/admin/articles/1/show?test=testing', $url);
    }

    public function testColumnArrayDefinitionLinkToRouteNameOnly()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->column([
            'name' => 'articles',
            'entity' => 'articles',
            'linkTo' => 'articles.show',
        ]);
        $columnArray = $this->crudPanel->columns()['articles'];
        $this->crudPanel->entry = Article::first();
        $url = $columnArray['wrapper']['href']($this->crudPanel, $columnArray, $this->crudPanel->entry, 1);
        $this->assertEquals('http://localhost/admin/articles/1/show', $url);
    }

    public function testColumnArrayDefinitionLinkToRouteNameAndAdditionalParameters()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->column([
            'name' => 'articles',
            'entity' => 'articles',
            'linkTo' => [
                'route' => 'articles.show',
                'parameters' => [
                    'test' => 'testing',
                    'test2' => fn ($entry) => $entry->content,
                ],
            ],
        ]);
        $columnArray = $this->crudPanel->columns()['articles'];
        $reflection = new \ReflectionFunction($columnArray['wrapper']['href']);
        $arguments = $reflection->getClosureUsedVariables();
        $this->assertEquals('articles.show', $arguments['route']);
        $this->assertCount(3, $arguments['parameters']);
        $this->crudPanel->entry = Article::first();
        $url = $columnArray['wrapper']['href']($this->crudPanel, $columnArray, $this->crudPanel->entry, 1);
        $this->assertEquals('http://localhost/admin/articles/1/show?test=testing&test2=Some%20Content', $url);
    }
}
