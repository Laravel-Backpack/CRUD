<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\Unit\Models\Article;
use Backpack\CRUD\Tests\Unit\Models\Role;
use Backpack\CRUD\Tests\Unit\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CrudPanelReadTest extends BaseDBCrudPanelTest
{
    private $relationshipColumn = [
        'name'      => 'user_id',
        'type'      => 'select',
        'entity'    => 'user',
        'attribute' => 'name',
    ];

    private $relationshipMultipleColumn = [
        'name'      => 'roles',
        'type'      => 'select',
        'entity'    => 'roles',
        'attribute' => 'name',
        'model' => Role::class,
    ];

    private $nonRelationshipColumn = [
        'name'  => 'field1',
        'label' => 'Field1',
    ];

    private $articleFieldsArray = [
        [
            'name'  => 'content',
            'label' => 'The Content',
            'type'  => 'text',
        ],
        [
            'name'  => 'metas',
            'label' => 'Metas',
        ],
        [
            'name' => 'tags',
        ],
        [
            'name' => 'extras',
        ],
    ];

    private $expectedCreateFormArticleFieldsArray = [
        'content' => [
            'name'  => 'content',
            'label' => 'The Content',
            'type'  => 'text',
        ],
        'metas' => [
            'name'  => 'metas',
            'label' => 'Metas',
            'type'  => 'text',
        ],
        'tags' => [
            'name'  => 'tags',
            'label' => 'Tags',
            'type'  => 'text',
        ],
        'extras' => [
            'name'  => 'extras',
            'label' => 'Extras',
            'type'  => 'text',
        ],
    ];

    private $expectedUpdateFormArticleFieldsArray = [
        'content' => [
            'name'  => 'content',
            'label' => 'The Content',
            'type'  => 'text',
            'value' => 'Some Content',
        ],
        'metas' => [
            'name'  => 'metas',
            'label' => 'Metas',
            'type'  => 'text',
            'value' => '{"meta_title":"Meta Title Value","meta_description":"Meta Description Value"}',
        ],
        'tags' => [
            'name'  => 'tags',
            'label' => 'Tags',
            'type'  => 'text',
            'value' => '{"tags":["tag1","tag2","tag3"]}',
        ],
        'extras' => [
            'name'  => 'extras',
            'label' => 'Extras',
            'type'  => 'text',
            'value' => '{"extra_details":["detail1","detail2","detail3"]}',
        ],
        'id' => [
            'name'  => 'id',
            'type'  => 'hidden',
            'value' => 1,
        ],
    ];

    private $uploadField = [
        'name'   => 'image',
        'label'  => 'Image',
        'type'   => 'upload',
        'upload' => true,
    ];

    private $multipleUploadField = [
        'name'   => 'photos',
        'label'  => 'Photos',
        'type'   => 'upload_multiple',
        'upload' => true,
    ];

    public function testGetEntry()
    {
        $this->crudPanel->setModel(User::class);
        $user = User::find(1);

        $entry = $this->crudPanel->getEntry($user->id);

        $this->assertEquals($user, $entry);
    }

    public function testGetEntryWithFakes()
    {
        $this->markTestIncomplete('Not correctly implemented');

        $this->crudPanel->setModel(Article::class);
        $article = Article::find(1);

        $entry = $this->crudPanel->getEntry($article->id);

        // TODO: the withFakes call is needed for this to work. the state of the model should not be changed by the
        //       getEntry method. the transformation of the fakes columns should be kept in a different crud panel
        //       attribute or, at most, the getEntry method should be renamed.
        $article->withFakes();

        $this->assertEquals($article, $entry);
    }

    public function testGetEntryExists()
    {
        $this->crudPanel->setModel(User::class);
        $userEntry = $this->crudPanel->getEntry(1);

        $this->assertInstanceOf(User::class, $userEntry);

        $this->crudPanel->setModel(Article::class);
        $articleEntry = $this->crudPanel->getEntry(1);

        $this->assertInstanceOf(Article::class, $articleEntry);
    }

    public function testGetEntryUnknownId()
    {
        $this->expectException(ModelNotFoundException::class);

        $this->crudPanel->setModel(User::class);

        $unknownId = DB::getPdo()->lastInsertId() + 2;
        $this->crudPanel->getEntry($unknownId);
    }

    public function testAutoEagerLoadRelationshipColumns()
    {
        $this->crudPanel->setModel(Article::class);
        $this->crudPanel->setOperation('list');
        $this->crudPanel->addColumn($this->relationshipColumn);

        $this->crudPanel->autoEagerLoadRelationshipColumns();

        $this->assertArrayHasKey('user', $this->crudPanel->query->getEagerLoads());
    }

    public function testAutoEagerLoadRelationshipColumnsNoRelationships()
    {
        $this->crudPanel->setModel(Article::class);
        $this->crudPanel->addColumn($this->nonRelationshipColumn);

        $this->crudPanel->autoEagerLoadRelationshipColumns();

        $this->assertEmpty($this->crudPanel->query->getEagerLoads());
    }

    public function testGetEntries()
    {
        $this->crudPanel->setModel(User::class);

        $entries = $this->crudPanel->getEntries();

        $this->assertInstanceOf(Collection::class, $entries);
        $this->assertEquals(2, $entries->count());
        $this->assertEquals(User::find(1), $entries->first());
    }

    public function testGetEntriesWithFakes()
    {
        $this->markTestIncomplete('Not correctly implemented');

        $this->crudPanel->setModel(Article::class);

        $entries = $this->crudPanel->getEntries();

        // TODO: the getEntries method automatically adds fakes. the state of the models should not be changed by the
        //       getEntries method. at most, the getEntries method should be renamed.
        $this->assertInstanceOf(Collection::class, $entries);
        $this->assertEquals(1, $entries->count());
        $this->assertEquals(Article::find(1), $entries->first());
    }

    public function testGetFieldsCreateForm()
    {
        $this->crudPanel->addFields($this->articleFieldsArray);

        // TODO: update method documentation. the $form parameter does not default to 'both'.
        $fields = $this->crudPanel->getFields('create');

        $this->assertEquals($this->expectedCreateFormArticleFieldsArray, $fields);
    }

    public function testGetFieldsUpdateForm()
    {
        $this->crudPanel->setModel(Article::class);

        $this->crudPanel->setOperation('update');
        $this->crudPanel->addFields($this->articleFieldsArray);

        // TODO: update method documentation. the $form parameter does not default to 'both'.
        $fields = $this->crudPanel->getUpdateFields(1);

        $this->assertEquals($this->expectedUpdateFormArticleFieldsArray, $fields);
    }

    public function testHasUploadFieldsCreateForm()
    {
        $this->crudPanel->addField($this->uploadField, 'create');

        // TODO: update method documentation. the $form parameter does not default to 'both'.
        $hasUploadFields = $this->crudPanel->hasUploadFields('create');

        $this->assertTrue($hasUploadFields);
    }

    public function testHasMultipleUploadFieldsCreateForm()
    {
        $this->crudPanel->addField($this->multipleUploadField, 'create');

        // TODO: update method documentation. the $form parameter does not default to 'both'.
        $hasMultipleUploadFields = $this->crudPanel->hasUploadFields('create');

        $this->assertTrue($hasMultipleUploadFields);
    }

    public function testHasUploadFieldsUpdateForm()
    {
        $this->crudPanel->setModel(Article::class);
        $this->crudPanel->addField($this->uploadField, 'update');

        // TODO: update method documentation. the $form parameter does not default to 'both'.
        $hasUploadFields = $this->crudPanel->hasUploadFields('update', 1);

        $this->assertTrue($hasUploadFields);
    }

    public function testEnableDetailsRow()
    {
        $this->crudPanel->setOperation('create');
        $this->crudPanel->enableDetailsRow();

        $this->assertTrue($this->crudPanel->getOperationSetting('detailsRow'));
    }

    public function testDisableDetailsRow()
    {
        $this->crudPanel->setOperation('list');
        $this->crudPanel->disableDetailsRow();

        $this->assertFalse($this->crudPanel->get('list.detailsRow'));
    }

    public function testSetDefaultPageLength()
    {
        $pageLength = 20;
        $this->crudPanel->setDefaultPageLength($pageLength);

        $this->assertEquals($pageLength, $this->crudPanel->getDefaultPageLength());
    }

    public function testGetDefaultPageLength()
    {
        $defaultPageLength = $this->crudPanel->getDefaultPageLength();

        $this->assertEquals(25, $defaultPageLength);
    }

    public function testEnableExportButtons()
    {
        $this->crudPanel->enableExportButtons();

        $this->assertTrue($this->crudPanel->exportButtons());
    }

    public function testGetExportButtons()
    {
        $exportButtons = $this->crudPanel->exportButtons();

        $this->assertFalse($exportButtons);
    }

    public function testGetRelatedEntriesAttributesFromBelongsToMany()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('list');
        $user = $this->crudPanel->getModel()->where('id', 2)->first();
        $entries = $this->crudPanel->getRelatedEntriesAttributes($user, 'roles', 'name');
        $this->assertEquals([1 => 'admin', 2 => 'user'], $entries);
    }

    public function testGetRelatedEntriesAttributesFromBelongsToManyWithAcessor()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('list');
        $user = $this->crudPanel->getModel()->where('id', 2)->first();
        $entries = $this->crudPanel->getRelatedEntriesAttributes($user, 'roles', 'role_name');
        $this->assertEquals([1 => 'admin++', 2 => 'user++'], $entries);
    }

    public function testGetRelatedEntriesAttributesFromHasMany()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('list');
        $user = $this->crudPanel->getModel()->first();
        $entries = $this->crudPanel->getRelatedEntriesAttributes($user, 'articles', 'content');
        $this->assertCount(1, $entries);
    }

    public function testGetRelatedEntriesAttributesFromHasManyWithAcessor()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('list');
        $user = $this->crudPanel->getModel()->first();
        $entries = $this->crudPanel->getRelatedEntriesAttributes($user, 'articles', 'content_composed');
        $this->assertCount(1, $entries);
    }

    public function testGetRelatedEntriesAttributesFromBelongsTo()
    {
        $this->crudPanel->setModel(Article::class);
        $this->crudPanel->setOperation('list');
        $article = $this->crudPanel->getModel()->first();
        $entries = $this->crudPanel->getRelatedEntriesAttributes($article, 'user', 'name');
        $this->assertCount(1, $entries);
    }

    public function testGetRelatedEntriesAttributesFromBelongsToWithAcessor()
    {
        $this->crudPanel->setModel(Article::class);
        $this->crudPanel->setOperation('list');
        $article = $this->crudPanel->getModel()->first();
        $entries = $this->crudPanel->getRelatedEntriesAttributes($article, 'user', 'name_composed');
        $this->assertCount(1, $entries);
    }

    public function testGetRelatedEntriesAttributesFromHasOne()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('list');
        $user = $this->crudPanel->getModel()->first();
        $entries = $this->crudPanel->getRelatedEntriesAttributes($user, 'accountDetails', 'nickname');
        $this->assertCount(1, $entries);
    }

    public function testGetRelatedEntriesAttributesFromHasOneWithAcessor()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('list');
        $user = $this->crudPanel->getModel()->first();
        $entries = $this->crudPanel->getRelatedEntriesAttributes($user, 'accountDetails', 'nickname_composed');
        $this->assertCount(1, $entries);
    }
}
