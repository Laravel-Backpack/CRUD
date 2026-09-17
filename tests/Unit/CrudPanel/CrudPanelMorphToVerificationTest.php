<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\config\Http\Controllers\FetchSourceCrudController;
use Backpack\CRUD\Tests\config\Models\Comment;
use Backpack\CRUD\Tests\config\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Validation\ValidationException;

class CrudPanelMorphToVerificationTest extends \Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel
{
    protected function tearDown(): void
    {
        Relation::morphMap([], false);

        parent::tearDown();
    }

    private function setupConstrainedPanel()
    {
        $this->crudPanel->setModel(Comment::class);
        $this->crudPanel->addField([
            'name' => 'commentable',
            'type' => 'relationship',
            'morphOptions' => [
                ['Backpack\CRUD\Tests\config\Models\User', 'User', ['query' => fn ($query) => $query->where('id', 1)]],
            ],
        ]);
    }

    /**
     * A morph option served over ajax, the way a developer would declare it: the entries come
     * from the controller's fetchUser(), which is also what its data_source URL points at.
     */
    private function setupAjaxPanel(array $morphOption, $optionKey = 'Backpack\CRUD\Tests\config\Models\User')
    {
        $this->crudPanel->setModel(Comment::class);
        $this->crudPanel->setController(FetchSourceCrudController::class);
        $this->crudPanel->addField([
            'name' => 'commentable',
            'type' => 'relationship',
            'morphOptions' => [[$optionKey, 'User', $morphOption]],
        ]);
    }

    private function victim()
    {
        User::query()->firstOrCreate(['email' => 'victim@tenant2.com'], ['name' => 'victim', 'password' => 'x']);

        return User::where('email', 'victim@tenant2.com')->first();
    }

    public function testItRejectsAnOutOfScopeMorphId()
    {
        $this->setupConstrainedPanel();
        $victim = $this->victim();
        $this->assertNotEquals(1, $victim->id);

        $this->expectException(ValidationException::class);

        $this->crudPanel->create([
            'text' => 'hello',
            'commentable' => [
                'commentable_type' => 'Backpack\CRUD\Tests\config\Models\User',
                'commentable_id' => $victim->id,
            ],
        ]);
    }

    public function testItRejectsAnUndeclaredMorphType()
    {
        $this->setupConstrainedPanel();

        $this->expectException(ValidationException::class);

        $this->crudPanel->create([
            'text' => 'hello',
            'commentable' => [
                'commentable_type' => 'Backpack\CRUD\Tests\config\Models\Article',
                'commentable_id' => 99999,
            ],
        ]);
    }

    public function testItRejectsAnOutOfScopeMorphIdOnUpdate()
    {
        $this->setupConstrainedPanel();
        $victim = $this->victim();

        $entry = $this->crudPanel->create([
            'text' => 'hello',
            'commentable' => [
                'commentable_type' => 'Backpack\CRUD\Tests\config\Models\User',
                'commentable_id' => 1,
            ],
        ]);

        $this->assertEquals(1, $entry->commentable_id);

        try {
            $this->crudPanel->update($entry->id, [
                'text' => 'hello',
                'commentable' => [
                    'commentable_type' => 'Backpack\CRUD\Tests\config\Models\User',
                    'commentable_id' => $victim->id,
                ],
            ]);
            $this->fail('expected a ValidationException');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('commentable.commentable_id', $e->errors());
        }

        $this->assertEquals(1, $entry->fresh()->commentable_id);
    }

    public function testItAllowsAnInScopeValue()
    {
        $this->setupConstrainedPanel();

        $entry = $this->crudPanel->create([
            'text' => 'hello',
            'commentable' => [
                'commentable_type' => 'Backpack\CRUD\Tests\config\Models\User',
                'commentable_id' => 1,
            ],
        ]);

        $this->assertEquals('Backpack\CRUD\Tests\config\Models\User', $entry->commentable_type);
        $this->assertEquals(1, $entry->commentable_id);
    }

    public function testItAllowsAnyIdWhenTheOptionIsNotConstrained()
    {
        $this->crudPanel->setModel(Comment::class);
        $this->crudPanel->addField([
            'name' => 'commentable',
            'type' => 'relationship',
            'morphOptions' => [['Backpack\CRUD\Tests\config\Models\User', 'User']],
        ]);

        $victim = $this->victim();

        $entry = $this->crudPanel->create([
            'text' => 'hello',
            'commentable' => [
                'commentable_type' => 'Backpack\CRUD\Tests\config\Models\User',
                'commentable_id' => $victim->id,
            ],
        ]);

        $this->assertEquals($victim->id, $entry->commentable_id);
    }

    public function testItRespectsExplicitOptionsArrays()
    {
        $this->crudPanel->setModel(Comment::class);
        $this->crudPanel->addField([
            'name' => 'commentable',
            'type' => 'relationship',
            'morphOptions' => [['Backpack\CRUD\Tests\config\Models\User', 'User', ['options' => [1 => 'one']]]],
        ]);

        $entry = $this->crudPanel->create([
            'text' => 'hello',
            'commentable' => ['commentable_type' => 'Backpack\CRUD\Tests\config\Models\User', 'commentable_id' => 1],
        ]);
        $this->assertEquals(1, $entry->commentable_id);

        $this->expectException(ValidationException::class);
        $this->crudPanel->create([
            'text' => 'hello',
            'commentable' => ['commentable_type' => 'Backpack\CRUD\Tests\config\Models\User', 'commentable_id' => 2],
        ]);
    }

    public function testItAcceptsMorphMapAliasesAndNormalizesTheStoredType()
    {
        Relation::morphMap(['user' => 'Backpack\CRUD\Tests\config\Models\User']);

        $this->crudPanel->setModel(Comment::class);
        $this->crudPanel->addField([
            'name' => 'commentable',
            'type' => 'relationship',
            'morphOptions' => [['user', 'User']],
        ]);

        $entry = $this->crudPanel->create([
            'text' => 'hello',
            'commentable' => ['commentable_type' => 'user', 'commentable_id' => 1],
        ]);
        $this->assertEquals('user', $entry->commentable_type);

        $entry = $this->crudPanel->create([
            'text' => 'hello',
            'commentable' => ['commentable_type' => 'Backpack\CRUD\Tests\config\Models\User', 'commentable_id' => 1],
        ]);
        $this->assertEquals('user', $entry->commentable_type);
    }

    public function testItAllowsClearingTheRelation()
    {
        $this->setupConstrainedPanel();

        $entry = $this->crudPanel->create([
            'text' => 'hello',
            'commentable' => ['commentable_type' => '', 'commentable_id' => ''],
        ]);

        $this->assertEmpty($entry->commentable_type);
        $this->assertEmpty($entry->commentable_id);
    }

    public function testItVerifiesAjaxOptionsAgainstTheFetchMethodNamedAfterTheModel()
    {
        // fetchUser() constrains the entries it serves to id 1
        $this->setupAjaxPanel(['data_source' => 'http://localhost/whatever/fetch/user']);
        $victim = $this->victim();

        $entry = $this->crudPanel->create([
            'text' => 'hello',
            'commentable' => ['commentable_type' => 'Backpack\CRUD\Tests\config\Models\User', 'commentable_id' => 1],
        ]);
        $this->assertEquals(1, $entry->commentable_id);

        $this->expectException(ValidationException::class);
        $this->crudPanel->create([
            'text' => 'hello',
            'commentable' => ['commentable_type' => 'Backpack\CRUD\Tests\config\Models\User', 'commentable_id' => $victim->id],
        ]);
    }

    public function testItResolvesTheFetchMethodThroughAMorphMapAlias()
    {
        Relation::morphMap(['user' => 'Backpack\CRUD\Tests\config\Models\User']);

        $this->setupAjaxPanel(['data_source' => 'http://localhost/whatever/fetch/user'], 'user');
        $victim = $this->victim();

        $this->expectException(ValidationException::class);
        $this->crudPanel->create([
            'text' => 'hello',
            'commentable' => ['commentable_type' => 'user', 'commentable_id' => $victim->id],
        ]);
    }

    public function testItVerifiesAjaxOptionsDeclaredWithTheAjaxFlag()
    {
        $this->setupAjaxPanel(['ajax' => true]);
        $victim = $this->victim();

        $this->expectException(ValidationException::class);
        $this->crudPanel->create([
            'text' => 'hello',
            'commentable' => ['commentable_type' => 'Backpack\CRUD\Tests\config\Models\User', 'commentable_id' => $victim->id],
        ]);
    }

    public function testItHonorsAnExplicitFetchSourceOnAMorphOption()
    {
        $this->setupAjaxPanel([
            'data_source' => 'http://localhost/whatever/fetch/moderator-user',
            'relation_options_query_source' => 'fetchModeratorUser',
        ]);
        $victim = $this->victim();

        $this->expectException(ValidationException::class);
        $this->crudPanel->create([
            'text' => 'hello',
            'commentable' => ['commentable_type' => 'Backpack\CRUD\Tests\config\Models\User', 'commentable_id' => $victim->id],
        ]);
    }

    public function testItLeavesAnAjaxOptionUnconstrainedWhenTheControllerExposesNoQuery()
    {
        // the controller has no fetchArticle(), so nothing constrains this option
        $this->crudPanel->setModel(Comment::class);
        $this->crudPanel->setController(FetchSourceCrudController::class);
        $this->crudPanel->addField([
            'name' => 'commentable',
            'type' => 'relationship',
            'morphOptions' => [
                ['Backpack\CRUD\Tests\config\Models\Article', 'Article', ['data_source' => 'http://localhost/whatever/fetch/article']],
            ],
        ]);

        $entry = $this->crudPanel->create([
            'text' => 'hello',
            'commentable' => ['commentable_type' => 'Backpack\CRUD\Tests\config\Models\Article', 'commentable_id' => 4242],
        ]);

        $this->assertEquals(4242, $entry->commentable_id);
    }

    public function testItStillVerifiesTheMorphTypeForAjaxOptions()
    {
        $this->setupAjaxPanel(['data_source' => 'http://localhost/whatever/fetch/user']);

        $this->expectException(ValidationException::class);
        $this->crudPanel->create([
            'text' => 'hello',
            'commentable' => ['commentable_type' => 'Backpack\CRUD\Tests\config\Models\Article', 'commentable_id' => 1],
        ]);
    }
}
