<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\config\Models\Article;
use Backpack\CRUD\Tests\config\Models\Bang;
use Backpack\CRUD\Tests\config\Models\Comet;
use Backpack\CRUD\Tests\config\Models\Planet;
use Backpack\CRUD\Tests\config\Models\PlanetNonNullable;
use Backpack\CRUD\Tests\config\Models\Star;
use Backpack\CRUD\Tests\config\Models\Universe;
use Backpack\CRUD\Tests\config\Models\User;
use Faker\Factory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Create
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Relationships
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\FieldsProtectedMethods
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Update
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Input
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\MorphRelationships
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Fields
 * @covers Backpack\CRUD\app\Library\CrudPanel\CrudField
 */
class CrudPanelCreateTest extends \Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel
{
    private $nonRelationshipField = [
        'name' => 'field1',
        'label' => 'Field1',
    ];

    private $userInputFieldsNoRelationships = [
        [
            'name' => 'id',
            'type' => 'hidden',
        ], [
            'name' => 'name',
        ], [
            'name' => 'email',
            'type' => 'email',
        ], [
            'name' => 'password',
            'type' => 'password',
        ],
    ];

    private $articleInputFieldsOneToMany = [
        [
            'name' => 'id',
            'type' => 'hidden',
        ], [
            'name' => 'content',
        ], [
            'name' => 'tags',
        ], [
            'label' => 'Author',
            'type' => 'select',
            'name' => 'user_id',
            'entity' => 'user',
            'attribute' => 'name',
        ],
    ];

    private $userInputFieldsManyToMany = [
        [
            'name' => 'id',
            'type' => 'hidden',
        ], [
            'name' => 'name',
        ], [
            'name' => 'email',
            'type' => 'email',
        ], [
            'name' => 'password',
            'type' => 'password',
        ], [
            'label' => 'Roles',
            'type' => 'select_multiple',
            'name' => 'roles',
            'entity' => 'roles',
            'attribute' => 'name',
            'pivot' => true,
        ],
    ];

    private $userInputFieldsDotNotation = [
        [
            'name' => 'id',
            'type' => 'hidden',
        ], [
            'name' => 'name',
        ], [
            'name' => 'email',
            'type' => 'email',
        ], [
            'name' => 'password',
            'type' => 'password',
        ], [
            'label' => 'Roles',
            'type' => 'relationship',
            'name' => 'roles',
            'entity' => 'roles',
            'attribute' => 'name',
        ], [
            'label' => 'Street',
            'name' => 'street',
            'entity' => 'accountDetails.addresses',
            'attribute' => 'street',
        ],
    ];

    private $userInputHasOneRelation = [
        [
            'name' => 'accountDetails.nickname',
        ],
        [
            'name' => 'accountDetails.profile_picture',
        ],
    ];

    private $articleInputBelongsToRelationName = [
        [
            'name' => 'user',
        ],
    ];

    public function testCreate()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships);
        $faker = Factory::create();
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
        ];

        $entry = $this->crudPanel->create($inputData);

        $this->assertInstanceOf(User::class, $entry);
        $this->assertEntryEquals($inputData, $entry);
        $this->assertEmpty($entry->articles);
    }

    public function testCreateWithOneToOneRelationship()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships);
        $this->crudPanel->addFields($this->userInputHasOneRelation);
        $faker = Factory::create();
        $account_details_nickname = $faker->name;
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'accountDetails' => [
                'nickname' => $account_details_nickname,
                'profile_picture' => 'test.jpg',
            ],
        ];
        $entry = $this->crudPanel->create($inputData);
        $account_details = $entry->accountDetails()->first();

        $this->assertEquals($account_details->nickname, $account_details_nickname);
    }

    public function testCreateWithOneToOneDynamicRelationship()
    {
        User::resolveRelationUsing('dynamicRelation', function ($user) {
            return $user->hasOne(\Backpack\CRUD\Tests\config\Models\AccountDetails::class);
        });
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships);
        $this->crudPanel->addFields([
            [
                'name' => 'dynamicRelation.nickname',
            ],
            [
                'name' => 'dynamicRelation.profile_picture',
            ],
        ]);
        $faker = Factory::create();
        $account_details_nickname = $faker->name;
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'dynamicRelation' => [
                'nickname' => $account_details_nickname,
                'profile_picture' => 'test.jpg',
            ],
        ];
        $entry = $this->crudPanel->create($inputData);
        $account_details = $entry->accountDetails()->first();

        $this->assertEquals($account_details->nickname, $account_details_nickname);
    }

    public function testCreateWithOneToOneRelationshipUsingRepeatableInterface()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships);
        $this->crudPanel->addField([
            'name' => 'accountDetails',
            'fields' => [
                [
                    'name' => 'nickname',
                ],
                [
                    'name' => 'profile_picture',
                ],
            ],
        ]);
        $faker = Factory::create();
        $account_details_nickname = $faker->name;
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'accountDetails' => [
                ['nickname' => $account_details_nickname, 'profile_picture' => 'test.jpg'],
            ],
        ];
        $entry = $this->crudPanel->create($inputData);
        $account_details = $entry->accountDetails()->first();

        $this->assertEquals($account_details->nickname, $account_details_nickname);
    }

    public function testCreateBelongsToWithRelationName()
    {
        $this->crudPanel->setModel(Article::class);
        $this->crudPanel->addFields($this->articleInputFieldsOneToMany);
        $this->crudPanel->removeField('user_id');
        $this->crudPanel->addFields($this->articleInputBelongsToRelationName);
        $faker = Factory::create();
        $inputData = [
            'content' => $faker->text(),
            'tags' => $faker->words(3, true),
            'user' => 1,
            'metas' => null,
            'extras' => null,
            'cast_metas' => null,
            'cast_tags' => null,
            'cast_extras' => null,
        ];
        $entry = $this->crudPanel->create($inputData);
        $userEntry = User::find(1);
        $article = Article::where('user_id', 1)->with('user')->get()->last();
        $this->assertEquals($article->user_id, $entry->user_id);
        $this->assertEquals($article->id, $entry->id);
    }

    public function testCreateWithOneToManyRelationship()
    {
        $this->crudPanel->setModel(Article::class);
        $this->crudPanel->addFields($this->articleInputFieldsOneToMany);
        $faker = Factory::create();
        $inputData = [
            'content' => $faker->text(),
            'tags' => $faker->words(3, true),
            'user_id' => 1,
            'metas' => null,
            'extras' => null,
            'cast_metas' => null,
            'cast_tags' => null,
            'cast_extras' => null,
        ];

        $entry = $this->crudPanel->create($inputData);
        $userEntry = User::find(1);
        $article = Article::where('user_id', 1)->with('user')->get()->last();
        $this->assertEntryEquals($inputData, $entry);
        $this->assertEquals($article->user_id, $entry->user_id);
        $this->assertEquals($article->id, $entry->id);
    }

    public function testCreateWithOneToManyDynamicRelationship()
    {
        Article::resolveRelationUsing('dynamicRelation', function ($article) {
            return $article->belongsTo(\Backpack\CRUD\Tests\config\Models\User::class, 'user_id');
        });

        $this->crudPanel->setModel(Article::class);
        $this->crudPanel->addFields([
            [
                'name' => 'id',
                'type' => 'hidden',
            ], [
                'name' => 'content',
            ], [
                'name' => 'tags',
            ], [
                'name' => 'dynamicRelation',
            ],
        ]);
        $faker = Factory::create();
        $inputData = [
            'content' => $faker->text(),
            'tags' => $faker->words(3, true),
            'dynamicRelation' => 1,
            'metas' => null,
            'extras' => null,
            'cast_metas' => null,
            'cast_tags' => null,
            'cast_extras' => null,
        ];

        $entry = $this->crudPanel->create($inputData);

        unset($inputData['dynamicRelation']);
        $inputData['user_id'] = 1;

        $userEntry = User::find(1);
        $article = Article::where('user_id', 1)->with('dynamicRelation')->get()->last();
        $this->assertEntryEquals($inputData, $entry);
        $this->assertEquals($article->user_id, $entry->user_id);
        $this->assertEquals($article->id, $entry->id);
        $this->assertEquals($article->user_id, $entry->dynamicRelation->id);
    }

    public function testCreateWithManyToManyRelationship()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsManyToMany);
        $faker = Factory::create();
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'roles' => [1, 2],
        ];

        $entry = $this->crudPanel->create($inputData);

        $this->assertInstanceOf(User::class, $entry);
        $this->assertEntryEquals($inputData, $entry);
    }

    public function testCreateWithManyToManyDynamicRelationship()
    {
        User::resolveRelationUsing('dynamicRelation', function ($user) {
            return $user->belongsToMany(\Backpack\CRUD\Tests\config\Models\Role::class, 'user_role');
        });

        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields([
            [
                'name' => 'id',
                'type' => 'hidden',
            ], [
                'name' => 'name',
            ], [
                'name' => 'email',
                'type' => 'email',
            ], [
                'name' => 'password',
                'type' => 'password',
            ], [
                'name' => 'dynamicRelation',
            ],
        ]);
        $faker = Factory::create();
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'dynamicRelation' => [1, 2],
        ];

        $entry = $this->crudPanel->create($inputData);

        $this->assertInstanceOf(User::class, $entry);
        $this->assertEntryEquals($inputData, $entry);
        $this->assertCount(2, $entry->dynamicRelation);
    }

    public function testGetRelationFields()
    {
        $this->markTestIncomplete('Not correctly implemented');

        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsManyToMany, 'create');

        // TODO: fix method and documentation. when 'both' is passed as the $form value, the getRelationFields searches
        //       for relationship fields in the update fields.
        $relationFields = $this->crudPanel->getRelationFields('both');

        $this->assertEquals($this->crudPanel->create_fields['roles'], Arr::last($relationFields));
    }

    public function testGetRelationFieldsCreateForm()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');
        $this->crudPanel->addFields($this->userInputFieldsManyToMany);

        $relationFields = $this->crudPanel->getRelationFields();

        $this->assertEquals($this->crudPanel->get('create.fields')['roles'], Arr::last($relationFields));
    }

    public function testGetRelationFieldsUpdateForm()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('update');
        $this->crudPanel->addFields($this->userInputFieldsManyToMany);

        $relationFields = $this->crudPanel->getRelationFields();

        $this->assertEquals($this->crudPanel->get('update.fields')['roles'], Arr::last($relationFields));
    }

    public function testGetRelationFieldsUnknownForm()
    {
        $this->markTestIncomplete('Not correctly implemented');

        $this->expectException(\InvalidArgumentException::class);

        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsManyToMany);

        // TODO: this should throw an invalid argument exception but instead it searches for relationship fields in the
        //       update fields.
        $this->crudPanel->getRelationFields('unknownForm');
    }

    public function testGetRelationFieldsDotNotation()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');

        $this->crudPanel->addFields($this->userInputFieldsDotNotation);

        //get all fields with a relation
        $relationFields = $this->crudPanel->getRelationFields();

        $this->assertEquals($this->crudPanel->get('create.fields')['street'], Arr::last($relationFields));
    }

    public function testCreateHasOneRelations()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');

        $this->crudPanel->addFields($this->userInputHasOneRelation);
        $faker = Factory::create();

        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'roles' => [1, 2],
            'accountDetails' => [
                'nickname' => 'i_have_has_one',
                'profile_picture' => 'simple_picture.jpg',
            ],
        ];
        $entry = $this->crudPanel->create($inputData);
        $updateFields = $this->crudPanel->getUpdateFields($entry->id);
        $account_details = $entry->accountDetails()->first();

        $this->assertEquals($account_details->nickname, 'i_have_has_one');
    }

    public function testGetRelationFieldsNoRelations()
    {
        $this->crudPanel->addField($this->nonRelationshipField);

        $relationFields = $this->crudPanel->getRelationFields();

        $this->assertEmpty($relationFields);
    }

    public function testGetRelationFieldsNoFields()
    {
        $relationFields = $this->crudPanel->getRelationFields();

        $this->assertEmpty($relationFields);
    }

    public function testGetRelationFieldsWithPivot()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');
        $this->crudPanel->addFields($this->userInputFieldsDotNotation);

        $relationFields = $this->crudPanel->getRelationFieldsWithPivot();
        $this->assertEquals($this->crudPanel->get('create.fields')['roles'], Arr::first($relationFields));
    }

    public function testGetRelationFieldsWithPivotNoRelations()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');
        $this->crudPanel->addFields($this->nonRelationshipField);

        $relationFields = $this->crudPanel->getRelationFieldsWithPivot();

        $this->assertEmpty($relationFields);
    }

    public function testMorphToManySelectableRelationship()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships, 'both');
        $this->crudPanel->addField(['name' => 'bills'], 'both');

        $faker = Factory::create();
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'bills' => [1],
        ];

        $entry = $this->crudPanel->create($inputData);

        $updateFields = $this->crudPanel->getUpdateFields($entry->id);

        $this->assertCount(1, $entry->bills);

        $this->assertEquals(1, $entry->bills()->first()->id);

        $inputData['bills'] = [1, 2];

        $this->crudPanel->update($entry->id, $inputData);

        $this->assertCount(2, $entry->fresh()->bills);

        $this->assertEquals([1, 2], $entry->fresh()->bills->pluck('id')->toArray());
    }

    public function testMorphToManyCreatableRelationship()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships, 'both');
        $this->crudPanel->addField(['name' => 'recommends', 'subfields' => [
            [
                'name' => 'text',
            ],
        ]], 'both');

        $faker = Factory::create();
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'recommends' => [
                [
                    'recommends' => 1,
                    'text' => 'my pivot recommend field',
                ],
            ],
        ];

        $entry = $this->crudPanel->create($inputData);
        $updateFields = $this->crudPanel->getUpdateFields($entry->id);

        $this->assertCount(1, $entry->recommends);

        $this->assertEquals(1, $entry->recommends()->first()->id);

        $inputData['recommends'] = [
            [
                'recommends' => 2,
                'text' => 'I changed the recommend and the pivot text',
            ],
        ];

        $this->crudPanel->update($entry->id, $inputData);

        $this->assertCount(1, $entry->fresh()->recommends);

        $this->assertEquals(2, $entry->recommends()->first()->id);

        $this->assertEquals('I changed the recommend and the pivot text', $entry->fresh()->recommends->first()->pivot->text);
    }

    public function testMorphToManyCreatableRelationshipWithMultiple()
    {
        $inputData = $this->getPivotInputData(['recommendsDuplicate' => [
            [
                'recommendsDuplicate' => 1,
                'text' => 'my pivot recommend field 1',
            ],
            [
                'recommendsDuplicate' => 2,
                'text' => 'my pivot recommend field 2',
            ],
            [
                'recommendsDuplicate' => 1,
                'text' => 'my pivot recommend field 1x1',
            ],
        ],
        ], true, true);

        $entry = $this->crudPanel->create($inputData);

        $entry = $entry->fresh();

        $this->assertCount(3, $entry->recommendsDuplicate);

        $this->assertEquals(1, $entry->recommendsDuplicate[0]->id);
        $this->assertEquals(1, $entry->recommendsDuplicate[2]->id);

        $inputData['recommendsDuplicate'] = [
            [
                'recommendsDuplicate' => 1,
                'text' => 'I changed the recommend and the pivot text',
                'id' => 1,
            ],
            [
                'recommendsDuplicate' => 2,
                'text' => 'I changed the recommend and the pivot text 2',
                'id' => 2,
            ],
            [
                'recommendsDuplicate' => 3,
                'text' => 'new recommend and the pivot text 3',
                'id' => null,
            ],
        ];

        $this->crudPanel->update($entry->id, $inputData);

        $entry = $entry->fresh();

        $this->assertCount(3, $entry->recommendsDuplicate);
        $this->assertDatabaseCount('recommendables', 3);

        $this->assertEquals('I changed the recommend and the pivot text', $entry->recommendsDuplicate[0]->pivot->text);
        $this->assertEquals('I changed the recommend and the pivot text 2', $entry->recommendsDuplicate[1]->pivot->text);
        $this->assertEquals('new recommend and the pivot text 3', $entry->recommendsDuplicate[2]->pivot->text);
    }

    public function testBelongsToManyWithPivotDataRelationship()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships);
        $this->crudPanel->addField([
            'name' => 'superArticles',
            'subfields' => [
                [
                    'name' => 'notes',
                ],
            ],
        ]);

        $faker = Factory::create();
        $articleData = [
            'content' => $faker->text(),
            'tags' => $faker->words(3, true),
            'user_id' => 1,
        ];

        $article = Article::create($articleData);

        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'superArticles' => [
                [
                    'superArticles' => $article->id,
                    'notes' => 'my first article note',
                ],
            ],
        ];

        $entry = $this->crudPanel->create($inputData);

        $this->assertCount(1, $entry->fresh()->superArticles);
        $this->assertEquals('my first article note', $entry->fresh()->superArticles->first()->pivot->notes);
    }

    public function testBelongsToManyWithPivotDataDynamicRelationship()
    {
        User::resolveRelationUsing('dynamicRelation', function ($user) {
            return $user->belongsToMany(\Backpack\CRUD\Tests\config\Models\Article::class, 'articles_user')->withPivot(['notes', 'start_date', 'end_date']);
        });

        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships);
        $this->crudPanel->addField([
            'name' => 'dynamicRelation',
            'subfields' => [
                [
                    'name' => 'notes',
                ],
            ],
        ]);

        $faker = Factory::create();
        $articleData = [
            'content' => $faker->text(),
            'tags' => $faker->words(3, true),
            'user_id' => 1,
        ];

        $article = Article::create($articleData);

        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'dynamicRelation' => [
                [
                    'dynamicRelation' => $article->id,
                    'notes' => 'my first article note',
                ],
            ],
        ];

        $entry = $this->crudPanel->create($inputData);

        $this->assertCount(1, $entry->fresh()->dynamicRelation);
        $this->assertEquals('my first article note', $entry->fresh()->dynamicRelation->first()->pivot->notes);
    }

    public function testBelongsToManyWithMultipleSameRelationIdAndPivotDataRelationship()
    {
        $inputData = $this->getPivotInputData(['superArticlesDuplicates' => [
            [
                'superArticlesDuplicates' => 1,
                'notes' => 'my first article note',
                'id' => null,
            ],
            [
                'superArticlesDuplicates' => 1,
                'notes' => 'my second article note',
                'id' => null,
            ],
            [
                'superArticlesDuplicates' => 2,
                'notes' => 'my first article2 note',
                'id' => null,
            ],
        ],
        ], true, true);

        $entry = $this->crudPanel->create($inputData);
        $relationField = $this->crudPanel->getUpdateFields($entry->id)['superArticlesDuplicates'];

        $this->assertCount(3, $relationField['value']);

        $entry = $entry->fresh();

        $this->assertCount(3, $entry->superArticlesDuplicates);
        $this->assertEquals('my first article note', $entry->superArticlesDuplicates->first()->pivot->notes);
        $this->assertEquals('my second article note', $entry->superArticlesDuplicates[1]->pivot->notes);
        $this->assertEquals('my first article2 note', $entry->superArticlesDuplicates[2]->pivot->notes);

        $inputData = $this->getPivotInputData(['superArticlesDuplicates' => [
            [
                'superArticlesDuplicates' => 1,
                'notes' => 'my first article note updated',
                'id' => 1,
            ],
            [
                'superArticlesDuplicates' => 1,
                'notes' => 'my second article note updated',
                'id' => 2,
            ],
            [
                'superArticlesDuplicates' => 2,
                'notes' => 'my first article2 note updated',
                'id' => 3,
            ],
        ],
        ], false, true);

        $entry = $this->crudPanel->update($entry->id, $inputData);
        $relationField = $this->crudPanel->getUpdateFields($entry->id)['superArticlesDuplicates'];
        $this->assertCount(3, $relationField['value']);

        $entry = $entry->fresh();

        $this->assertCount(3, $entry->superArticlesDuplicates);
        $this->assertEquals('my first article note updated', $entry->superArticlesDuplicates[0]->pivot->notes);
        $this->assertEquals('my second article note updated', $entry->superArticlesDuplicates[1]->pivot->notes);
        $this->assertEquals('my first article2 note updated', $entry->superArticlesDuplicates[2]->pivot->notes);
    }

    public function testBelongsToManyWithMultipleSameRelationIdAndPivotDataDynamicRelationship()
    {
        User::resolveRelationUsing('dynamicRelation', function ($user) {
            return $user->belongsToMany(\Backpack\CRUD\Tests\config\Models\Article::class, 'articles_user')->withPivot(['notes', 'start_date', 'end_date', 'id'])->using('Backpack\CRUD\Tests\config\Models\SuperArticlePivot');
        });

        $inputData = $this->getPivotInputData(['dynamicRelation' => [
            [
                'dynamicRelation' => 1,
                'notes' => 'my first article note',
                'id' => null,
            ],
            [
                'dynamicRelation' => 1,
                'notes' => 'my second article note',
                'id' => null,
            ],
            [
                'dynamicRelation' => 2,
                'notes' => 'my first article2 note',
                'id' => null,
            ],
        ],
        ], true, true);

        $entry = $this->crudPanel->create($inputData);
        $relationField = $this->crudPanel->getUpdateFields($entry->id)['dynamicRelation'];

        $this->assertCount(3, $relationField['value']);

        $entry = $entry->fresh();

        $this->assertCount(3, $entry->dynamicRelation);
        $this->assertEquals('my first article note', $entry->dynamicRelation->first()->pivot->notes);
        $this->assertEquals('my second article note', $entry->dynamicRelation[1]->pivot->notes);
        $this->assertEquals('my first article2 note', $entry->dynamicRelation[2]->pivot->notes);

        $inputData = $this->getPivotInputData(['dynamicRelation' => [
            [
                'dynamicRelation' => 1,
                'notes' => 'my first article note updated',
                'id' => 1,
            ],
            [
                'dynamicRelation' => 1,
                'notes' => 'my second article note updated',
                'id' => 2,
            ],
            [
                'dynamicRelation' => 2,
                'notes' => 'my first article2 note updated',
                'id' => 3,
            ],
        ],
        ], false, true);

        $entry = $this->crudPanel->update($entry->id, $inputData);
        $relationField = $this->crudPanel->getUpdateFields($entry->id)['dynamicRelation'];
        $this->assertCount(3, $relationField['value']);

        $entry = $entry->fresh();

        $this->assertCount(3, $entry->dynamicRelation);
        $this->assertEquals('my first article note updated', $entry->dynamicRelation[0]->pivot->notes);
        $this->assertEquals('my second article note updated', $entry->dynamicRelation[1]->pivot->notes);
        $this->assertEquals('my first article2 note updated', $entry->dynamicRelation[2]->pivot->notes);
    }

    public function testBelongsToManyAlwaysSaveSinglePivotWhenMultipleNotAllowed()
    {
        $inputData = $this->getPivotInputData(['superArticlesDuplicates' => [
            [
                'superArticlesDuplicates' => 1,
                'notes' => 'my first article note',
                'id' => null,
            ],
            [
                'superArticlesDuplicates' => 1,
                'notes' => 'my second article note',
                'id' => null,
            ],
            [
                'superArticlesDuplicates' => 2,
                'notes' => 'my first article2 note',
                'id' => null,
            ],
        ],
        ]);

        $entry = $this->crudPanel->create($inputData);
        $relationField = $this->crudPanel->getUpdateFields($entry->id)['superArticlesDuplicates'];

        $this->assertCount(2, $relationField['value']);

        $entry = $entry->fresh();

        $this->assertCount(2, $entry->superArticlesDuplicates);
        $this->assertEquals('my second article note', $entry->superArticles[0]->pivot->notes);
        $this->assertEquals('my first article2 note', $entry->superArticles[1]->pivot->notes);
    }

    public function testBelongsToManyDeletesPivotData()
    {
        $inputData = $this->getPivotInputData(['superArticlesDuplicates' => [
            [
                'superArticlesDuplicates' => 1,
                'notes' => 'my first article note',
                'id' => null,
            ],
            [
                'superArticlesDuplicates' => 1,
                'notes' => 'my second article note',
                'id' => null,
            ],
            [
                'superArticlesDuplicates' => 2,
                'notes' => 'my first article2 note',
                'id' => null,
            ],
        ],
        ], true, true);

        $entry = $this->crudPanel->create($inputData);
        $relationField = $this->crudPanel->getUpdateFields($entry->id)['superArticlesDuplicates'];

        $this->assertCount(3, $relationField['value']);

        $inputData = $this->getPivotInputData(['superArticlesDuplicates' => [
            [
                'superArticlesDuplicates' => 1,
                'notes' => 'new first article note',
                'id' => null,
            ],
            [
                'superArticlesDuplicates' => 1,
                'notes' => 'my second article note updated',
                'id' => 2,
            ],
            [
                'superArticlesDuplicates' => 3,
                'notes' => 'my first article2 note updated',
                'id' => 3,
            ],
        ],
        ], false, true);

        $entry = $this->crudPanel->update($entry->id, $inputData);
        $relationField = $this->crudPanel->getUpdateFields($entry->id)['superArticlesDuplicates'];
        $this->assertCount(3, $relationField['value']);

        $entry = $entry->fresh();

        $this->assertCount(3, $entry->superArticlesDuplicates);
        $this->assertEquals('new first article note', $entry->superArticles[2]->pivot->notes);
        $this->assertEquals('my second article note updated', $entry->superArticles[0]->pivot->notes);
        $this->assertEquals('my first article2 note updated', $entry->superArticles[1]->pivot->notes);
    }

    public function testCreateHasOneWithNestedRelationsRepeatableInterface()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships);
        $this->crudPanel->addField(
            [
                'name' => 'accountDetails',
                'subfields' => [
                    [
                        'name' => 'nickname',
                    ],
                    [
                        'name' => 'profile_picture',
                    ],
                    [
                        'name' => 'article',
                    ],
                    [
                        'name' => 'addresses',
                        'subfields' => [
                            [
                                'name' => 'bang',
                            ],
                            [
                                'name' => 'street',
                            ],
                            [
                                'name' => 'number',
                            ],
                        ],
                    ],
                    [
                        'name' => 'bangs',
                    ],
                    [
                        'name' => 'bangsPivot',
                        'subfields' => [
                            [
                                'name' => 'pivot_field',
                            ],
                        ],
                    ],
                ],
            ]);

        $faker = Factory::create();

        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'roles' => [1, 2],
            'accountDetails' => [
                [
                    'nickname' => 'i_have_has_one',
                    'profile_picture' => 'ohh my picture 1.jpg',
                    'article' => 1,
                    'addresses' => [
                        [
                            'bang' => 1,
                            'street' => 'test',
                            'number' => 1,
                        ],
                        [
                            'bang' => 1,
                            'street' => 'test2',
                            'number' => 2,
                        ],
                    ],
                    'bangs' => [1, 2],
                    'bangsPivot' => [
                        ['bangsPivot' => 1, 'pivot_field' => 'test1'],
                        ['bangsPivot' => 2, 'pivot_field' => 'test2'],
                    ],
                ],
            ],
        ];

        $entry = $this->crudPanel->create($inputData);
        $updateFields = $this->crudPanel->getUpdateFields($entry->id);
        $account_details = $entry->accountDetails()->first();

        $this->assertEquals($account_details->article, Article::find(1));
        $this->assertEquals($account_details->addresses->count(), 2);
        $this->assertEquals($account_details->addresses->first()->city, 1);
        $this->assertEquals($account_details->addresses->first()->street, 'test');
        $this->assertEquals($account_details->addresses->first()->number, 1);
        $this->assertEquals($account_details->bangs->first()->name, Bang::find(1)->name);
        $this->assertEquals($account_details->bangsPivot->count(), 2);
        $this->assertEquals($account_details->bangsPivot->first()->pivot->pivot_field, 'test1');
    }

    public function testCreateBelongsToFake()
    {
        $belongsToField = [   // select_grouped
            'label' => 'Select_grouped',
            'type' => 'select_grouped', //https://github.com/Laravel-Backpack/CRUD/issues/502
            'name' => 'bang_relation_field',
            'fake' => true,
            'entity' => 'bang',
            'model' => 'Backpack\CRUD\Tests\config\Models\Bang',
            'attribute' => 'title',
            'group_by' => 'category', // the relationship to entity you want to use for grouping
            'group_by_attribute' => 'name', // the attribute on related model, that you want shown
            'group_by_relationship_back' => 'articles', // relationship from related model back to this model
            'tab' => 'Selects',
            'wrapperAttributes' => ['class' => 'form-group col-md-6'],
        ];

        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships);
        $this->crudPanel->addField($belongsToField);

        $faker = Factory::create();

        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'bang_relation_field' => 1,
        ];

        $entry = $this->crudPanel->create($inputData);
        $updateFields = $this->crudPanel->getUpdateFields($entry->id);
        $this->crudPanel->entry = $entry->withFakes();
        $this->assertEquals($entry->bang_relation_field, 1);
    }

    public function testCreateHasOneWithNestedRelations()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships);
        $this->crudPanel->addFields([
            [
                'name' => 'accountDetails.nickname',
            ],
            [
                'name' => 'accountDetails.profile_picture',
            ],
            [
                'name' => 'accountDetails.article',
            ],
            [
                'name' => 'accountDetails.addresses',
                'subfields' => [
                    [
                        'name' => 'city',
                        'entity' => 'bang',
                    ],
                    [
                        'name' => 'street',
                    ],
                    [
                        'name' => 'number',
                    ],
                ],
            ],
            [
                'name' => 'accountDetails.bangs',
            ],
            [
                'name' => 'accountDetails.bangsPivot',
                'subfields' => [
                    [
                        'name' => 'pivot_field',
                    ],
                ],
            ],
        ]);

        $faker = Factory::create();

        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'roles' => [1, 2],
            'accountDetails' => [
                'nickname' => 'i_have_has_one',
                'profile_picture' => 'ohh my picture 1.jpg',
                'article' => 1,
                'addresses' => [
                    [
                        'city' => 1,
                        'street' => 'test',
                        'number' => 1,
                    ],
                    [
                        'city' => 2,
                        'street' => 'test2',
                        'number' => 2,
                    ],
                ],
                'bangs' => [1, 2],
                'bangsPivot' => [
                    ['bangsPivot' => 1, 'pivot_field' => 'test1'],
                    ['bangsPivot' => 2, 'pivot_field' => 'test2'],
                ],
            ],
        ];

        $entry = $this->crudPanel->create($inputData);
        $updateFields = $this->crudPanel->getUpdateFields($entry->id);
        $account_details = $entry->accountDetails()->first();

        $this->assertEquals($account_details->article, Article::find(1));
        $this->assertEquals($account_details->addresses->count(), 2);
        $this->assertEquals($account_details->addresses->first()->bang->id, 1);
        $this->assertEquals($account_details->addresses->first()->street, 'test');
        $this->assertEquals($account_details->addresses->first()->number, 1);
        $this->assertEquals($account_details->bangs->first()->name, Bang::find(1)->name);
        $this->assertEquals($account_details->bangsPivot->count(), 2);
        $this->assertEquals($account_details->bangsPivot->first()->pivot->pivot_field, 'test1');

        // Now test the remove process

        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'roles' => [1, 2],
            'accountDetails' => [
                'nickname' => 'i_have_has_one',
                'profile_picture' => 'ohh my picture 1.jpg',
                'article' => 1,
                'addresses' => [ // HasOne is tested in other test function
                    [
                        'city' => 2,
                        'street' => 'test',
                        'number' => 1,
                    ],
                    [
                        'city' => 1,
                        'street' => 'test2',
                        'number' => 2,
                    ],
                ],
                'bangs' => [],
                'bangsPivot' => [],
            ],
        ];

        $entry = $this->crudPanel->update($entry->id, $inputData);
        $account_details = $entry->accountDetails()->first();
        $this->assertEquals($account_details->addresses->count(), 2);
        $this->assertEquals($account_details->addresses->first()->bang->id, 2);
        $this->assertEquals($account_details->bangs->count(), 0);
        $this->assertEquals($account_details->bangsPivot->count(), 0);
    }

    public function testCreateHasOneWithNestedRelationAsTheFirstField()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships);
        $this->crudPanel->addFields([
            [
                'name' => 'accountDetails.article',
            ],
            [
                'name' => 'accountDetails.nickname',
            ],
            [
                'name' => 'accountDetails.profile_picture',
            ],
        ]);

        $faker = Factory::create();

        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'roles' => [1, 2],
            'accountDetails' => [
                'article' => 1,
                'nickname' => 'i_have_has_one',
                'profile_picture' => 'ohh my picture 1.jpg',
            ],
        ];

        $entry = $this->crudPanel->create($inputData);
        $updateFields = $this->crudPanel->getUpdateFields($entry->id);
        $account_details = $entry->accountDetails()->first();

        $this->assertEquals($account_details->article, Article::find(1));
    }

    public function testMorphOneRelationship()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships, 'both');
        $this->crudPanel->addField([
            'name' => 'comment.text',
        ], 'both');

        $faker = Factory::create();
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'comment' => [
                'text' => 'some test comment text',
            ],
        ];

        $entry = $this->crudPanel->create($inputData);
        $updateFields = $this->crudPanel->getUpdateFields($entry->id);

        $this->assertEquals($inputData['comment']['text'], $entry->comment->text);

        $inputData['comment']['text'] = 'updated comment text';

        $this->crudPanel->update($entry->id, $inputData);

        $this->assertEquals($inputData['comment']['text'], $entry->fresh()->comment->text);
    }

    public function testMorphManyCreatableRelationship()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships, 'both');
        $this->crudPanel->addField([
            'name' => 'stars',
            'subfields' => [
                [
                    'name' => 'title',
                ],
            ],
        ], 'both');

        $faker = Factory::create();
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'stars' => [
                [
                    'id' => null,
                    'title' => 'this is the star 1 title',
                ],
                [
                    'id' => null,
                    'title' => 'this is the star 2 title',
                ],
            ],
        ];

        $entry = $this->crudPanel->create($inputData);
        $updateFields = $this->crudPanel->getUpdateFields($entry->id);

        $this->assertCount(2, $entry->stars);

        $this->assertEquals($inputData['stars'][0]['title'], $entry->stars()->first()->title);

        $inputData['stars'] = [
            [
                'id' => 1,
                'title' => 'only one star with changed title',
            ],
        ];

        $this->crudPanel->update($entry->id, $inputData);

        $this->assertCount(1, $entry->fresh()->stars);

        $this->assertEquals($inputData['stars'][0]['title'], $entry->fresh()->stars->first()->title);
        $this->assertEquals($inputData['stars'][0]['id'], $entry->fresh()->stars->first()->id);
    }

    public function testHasManyCreatableRelationship()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships, 'both');
        $this->crudPanel->addField([
            'name' => 'universes',
            'subfields' => [
                [
                    'name' => 'title',
                ],
            ],
        ], 'both');

        $faker = Factory::create();
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'universes' => [
                [
                    'id' => null,
                    'title' => 'this is the star 1 title',
                ],
                [
                    'title' => 'this is the star 2 title',
                ],
            ],
        ];

        $entry = $this->crudPanel->create($inputData);
        $updateFields = $this->crudPanel->getUpdateFields($entry->id);

        $this->assertCount(2, $entry->universes);

        $this->assertEquals($inputData['universes'][0]['title'], $entry->universes()->first()->title);

        $inputData['universes'] = [
            [
                'id' => 1,
                'title' => 'star 1 with changed title',
            ],
            [
                'id' => 2,
                'title' => 'star 2 with changed title',
            ],
        ];

        $this->crudPanel->update($entry->id, $inputData);

        $universes = $entry->fresh()->universes;
        $this->assertCount(2, $universes);
        $this->assertEquals([1, 2], $universes->pluck('id')->toArray());

        $inputData['universes'] = [
            [
                'id' => 1,
                'title' => 'only one star with changed title',
            ],
        ];

        $this->crudPanel->update($entry->id, $inputData);

        $this->assertEquals($inputData['universes'][0]['title'], $entry->fresh()->universes->first()->title);
        $this->assertEquals($inputData['universes'][0]['id'], $entry->fresh()->universes->first()->id);
        $this->assertEquals(1, Universe::all()->count());

        $inputData['universes'] = [
            [
                'id' => null,
                'title' => 'new star 3',
            ],
        ];

        $this->crudPanel->update($entry->id, $inputData);

        $this->assertEquals($inputData['universes'][0]['title'], $entry->fresh()->universes->first()->title);
        $this->assertEquals(3, $entry->fresh()->universes->first()->id);
        $this->assertEquals(1, Universe::all()->count());

        $inputData['universes'] = null;

        $this->crudPanel->update($entry->id, $inputData);

        $this->assertEquals(0, count($entry->fresh()->universes));
        $this->assertEquals(0, Universe::all()->count());
    }

    public function testHasManySelectableRelationshipWithoutForceDelete()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships, 'both');
        $this->crudPanel->addField([
            'name' => 'planets',
            'force_delete' => false,
            'fallback_id' => false,
        ], 'both');

        $faker = Factory::create();
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'planets' => [1, 2],
        ];

        $entry = $this->crudPanel->create($inputData);
        $updateFields = $this->crudPanel->getUpdateFields($entry->id);

        $this->assertCount(2, $entry->planets);

        $inputData['planets'] = [1];

        $this->crudPanel->update($entry->id, $inputData);

        $this->assertCount(1, $entry->fresh()->planets);

        $planets = Planet::all();

        $this->assertCount(2, $planets);
    }

    public function testHasManySelectableRelationshipRemoveAllRelations()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships, 'both');
        $this->crudPanel->addField([
            'name' => 'planets',
            'force_delete' => false,
            'fallback_id' => false,
        ], 'both');

        $faker = Factory::create();
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'planets' => [1, 2],
        ];

        $entry = $this->crudPanel->create($inputData);

        $this->assertCount(2, $entry->planets);

        $inputData['planets'] = [];

        $this->crudPanel->update($entry->id, $inputData);

        $this->assertCount(0, $entry->fresh()->planets);

        $planets = Planet::all();

        $this->assertCount(2, $planets);
    }

    public function testHasManyWithRelationScoped()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships, 'both');
        $this->crudPanel->addField([
            'name' => 'incomes',
            'subfields' => [
                [
                    'name' => 'label',
                    'type' => 'text',
                ],
                [
                    'name' => 'type',
                    'type' => 'hidden',
                    'value' => 'income',
                ],
                [
                    'name' => 'amount',
                    'type' => 'number',
                ],
            ],
        ], 'both');
        $this->crudPanel->addField([
            'name' => 'expenses',
            'subfields' => [
                [
                    'name' => 'label',
                    'type' => 'text',
                ],
                [
                    'name' => 'type',
                    'type' => 'hidden',
                    'value' => 'expense',
                ],
                [
                    'name' => 'amount',
                    'type' => 'number',
                ],
            ],
        ], 'both');

        $faker = Factory::create();
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'incomes' => [
                [
                    'label' => $faker->name,
                    'amount' => 33,
                    'type' => 'income',
                ],
                [
                    'label' => $faker->name,
                    'amount' => 22,
                    'type' => 'income',
                ],
            ],
            'expenses' => [
                [
                    'label' => $faker->name,
                    'amount' => 44,
                    'type' => 'expense',
                ],
                [
                    'label' => $faker->name,
                    'amount' => 10,
                    'type' => 'expense',
                ],
            ],
        ];
        $entry = $this->crudPanel->create($inputData);

        $firstExpense = $entry->expenses->first();
        $firstIncome = $entry->incomes->first();
        $this->assertCount(2, $entry->expenses);
        $this->assertCount(2, $entry->incomes);
        $this->assertEquals(44, $entry->expenses->first()->amount);
        $this->assertEquals(33, $entry->incomes->first()->amount);

        $inputData['incomes'] = [
            [
                'id' => 2,
                'label' => $faker->name,
                'amount' => 222,
                'type' => 'income',
            ],
        ];
        $inputData['expenses'] = [
            [
                'id' => 3,
                'label' => $faker->name,
                'amount' => 44,
                'type' => 'expense',
            ],
            [
                'id' => 4,
                'label' => $faker->name,
                'amount' => 10,
                'type' => 'expense',
            ],
        ];
        $this->crudPanel->update($entry->id, $inputData);

        $freshIncomes = $entry->fresh()->incomes;
        $freshExpenses = $entry->fresh()->expenses;
        $this->assertCount(2, $freshExpenses);
        $this->assertCount(1, $freshIncomes);
        $this->assertEquals(2, $freshIncomes->first()->id);

        $inputData['expenses'] = [];
        $this->crudPanel->update($entry->id, $inputData);

        $freshIncomes = $entry->fresh()->incomes;
        $freshExpenses = $entry->fresh()->expenses;
        $this->assertCount(0, $freshExpenses);
        $this->assertCount(1, $freshIncomes);
    }

    public function testHasManySelectableRelationshipWithFallbackId()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships, 'both');
        $this->crudPanel->addField([
            'name' => 'planets',
            'fallback_id' => 0,
            'force_delete' => false,
        ], 'both');

        $faker = Factory::create();
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'planets' => [1, 2],
        ];

        $entry = $this->crudPanel->create($inputData);

        $this->assertCount(2, $entry->planets);

        $inputData['planets'] = [2];

        $this->crudPanel->update($entry->id, $inputData);

        $this->assertCount(1, $entry->fresh()->planets);

        $planets = Planet::all();
        $this->assertCount(2, $planets);
        $this->assertEquals(0, $planets->first()->user_id);
    }

    public function testHasManySelectableRelationshipWithForceDelete()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships, 'both');
        $this->crudPanel->addField([
            'name' => 'planets',
            'force_delete' => true,
            'fallback_id' => false,
        ], 'both');

        $faker = Factory::create();
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'planets' => [1, 2],
        ];

        $entry = $this->crudPanel->create($inputData);

        $this->assertCount(2, $entry->planets);

        $inputData['planets'] = [2];

        $this->crudPanel->update($entry->id, $inputData);

        $this->assertCount(1, $entry->fresh()->planets);

        $planets = Planet::all();
        $this->assertCount(1, $planets);
    }

    public function testHasManySelectableRelationshipNonNullableForeignKeyAndDefaultInDatabase()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships, 'both');
        $this->crudPanel->addField([
            'name' => 'comets',
            'force_delete' => false,
            'fallback_id' => false,
        ], 'both');

        $faker = Factory::create();
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'comets' => [1, 2],
        ];

        $entry = $this->crudPanel->create($inputData);

        $this->assertCount(2, $entry->comets);

        $inputData['comets'] = [2];

        $this->crudPanel->update($entry->id, $inputData);

        $this->assertCount(1, $entry->fresh()->comets);

        $comets = Comet::all();
        $this->assertCount(2, $comets);
        $this->assertEquals(0, $comets->first()->user_id);
    }

    public function testHasManySelectableRelationshipNonNullable()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships, 'both');
        $this->crudPanel->addField([
            'name' => 'planetsNonNullable',
            'force_delete' => false,
            'fallback_id' => false,
        ], 'both');

        $faker = Factory::create();
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'planetsNonNullable' => [1, 2],
        ];

        $entry = $this->crudPanel->create($inputData);

        $this->assertCount(2, $entry->planetsNonNullable);

        $inputData['planetsNonNullable'] = null;

        $this->crudPanel->update($entry->id, $inputData);

        $this->assertCount(0, $entry->fresh()->planetsNonNullable);

        $planets = PlanetNonNullable::all();
        $this->assertCount(0, $planets);
    }

    public function testCreateHasManyRelationWithDelimitedNameSubfields()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships, 'both');
        $this->crudPanel->addField([
            'name' => 'universes',
            'subfields' => [
                [
                    'name' => 'title',
                ],
                [
                    'name' => 'start_date,end_date',
                    'type' => 'date_range',
                ],
            ],
        ], 'both');

        $faker = Factory::create();
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'universes' => [
                [
                    'id' => null,
                    'title' => 'this is the star 1 title',
                    'start_date' => '2021-02-26',
                    'end_date' => '2091-01-26',
                ],
                [
                    'title' => 'this is the star 2 title',
                    'end_date' => '2021-02-26',
                    'start_date' => '2091-01-26',
                ],
            ],
        ];

        $entry = $this->crudPanel->create($inputData);

        $this->assertCount(2, $entry->universes);

        $this->assertEquals($inputData['universes'][0]['start_date'], $entry->universes()->first()->start_date);
        $this->assertEquals($inputData['universes'][0]['end_date'], $entry->universes()->first()->end_date);
        $this->assertEquals($inputData['universes'][1]['end_date'], $entry->universes()->find(2)->end_date);
        $this->assertEquals($inputData['universes'][1]['start_date'], $entry->universes()->find(2)->start_date);
    }

    public function testCreateHasOneRelationWithDelimitedNameSubfields()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships);
        $this->crudPanel->addField(
            [
                'name' => 'accountDetails',
                'subfields' => [
                    [
                        'name' => 'nickname',
                    ],
                    [
                        'name' => 'start_date,end_date',
                    ],
                    [
                        'name' => 'profile_picture',
                    ],
                ],
            ]);

        $faker = Factory::create();

        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'roles' => [1, 2],
            'accountDetails' => [
                [
                    'nickname' => 'i_have_has_one',
                    'profile_picture' => 'ohh my picture 1.jpg',
                    'start_date' => '2021-02-26',
                    'end_date' => '2091-01-26',
                ],
            ],
        ];

        $entry = $this->crudPanel->create($inputData);
        $account_details = $entry->accountDetails()->first();

        $this->assertEquals($account_details->start_date, '2021-02-26');
        $this->assertEquals($account_details->end_date, '2091-01-26');
    }

    public function testBelongsToManyWithDelimitedNameSubfields()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships);
        $this->crudPanel->addField([
            'name' => 'superArticles',
            'subfields' => [
                [
                    'name' => 'notes',
                ],
                [
                    'name' => 'start_date,end_date',
                ],
            ],
        ]);

        $faker = Factory::create();
        $articleData = [
            'content' => $faker->text(),
            'tags' => $faker->words(3, true),
            'user_id' => 1,
        ];

        $article = Article::create($articleData);

        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'superArticles' => [
                [
                    'superArticles' => $article->id,
                    'notes' => 'my first article note',
                    'start_date' => '2021-02-26',
                    'end_date' => '2091-01-26',
                ],
            ],
        ];

        $entry = $this->crudPanel->create($inputData);

        $this->assertCount(1, $entry->fresh()->superArticles);
        $superArticle = $entry->fresh()->superArticles->first();
        $this->assertEquals($superArticle->pivot->start_date, '2021-02-26');
        $this->assertEquals($superArticle->pivot->end_date, '2091-01-26');

        $this->crudPanel->getUpdateFields($superArticle->id);
    }

    public function testItCanCreateMorphToFieldsStructure()
    {
        $this->crudPanel->setModel(Star::class);
        $this->crudPanel->addField([
            'name' => 'starable',
            'morphOptions' => [
                ['Backpack\CRUD\Tests\config\Models\User', 'User'],
            ],
        ]);

        $this->assertCount(2, $this->crudPanel->fields()['starable']['subfields']);

        [$morphTypeField, $morphIdField] = $this->crudPanel->fields()['starable']['subfields'];

        $this->assertTrue($morphTypeField['name'] === 'starable_type');
        $this->assertTrue($morphIdField['name'] === 'starable_id');
    }

    public function testIPreventsAddingRepeateadMorphOptions()
    {
        $this->crudPanel->setModel(Star::class);
        $this->expectException(\Exception::class);

        $this->crudPanel->addField([
            'name' => 'starable',
            'morphOptions' => [
                ['Backpack\CRUD\Tests\config\Models\User', 'User'],
                ['Backpack\CRUD\Tests\config\Models\User', 'User'],
            ],
        ]);
    }

    public function testItThrowsErrorIfStringIsNotOnMorphMap()
    {
        $this->crudPanel->setModel(Star::class);
        $this->expectException(\Exception::class);

        $this->crudPanel->addField([
            'name' => 'starable',
            'morphOptions' => [
                ['somethingThatDoesNotExist'],
            ],
        ]);
    }

    public function testItCanAddTheOptionsFromTheMorphMap()
    {
        $this->crudPanel->setModel(Star::class);

        Relation::morphMap([
            'user' => 'Backpack\CRUD\Tests\config\Models\User',
        ]);

        $this->crudPanel->addField([
            'name' => 'starable',
            'morphOptions' => [
                ['user'],
            ],
        ]);

        [$morphTypeField, $morphIdField] = $this->crudPanel->fields()['starable']['subfields'];
        $this->assertEquals(['user' => 'Backpack\CRUD\Tests\config\Models\User'], $morphTypeField['morphMap']);
    }

    public function testItThrowsErrorIfDuplicateMorphMapName()
    {
        $this->crudPanel->setModel(Star::class);
        $this->expectException(\Exception::class);

        Relation::morphMap([
            'user' => 'Backpack\CRUD\Tests\config\Models\User',
        ]);

        $this->crudPanel->addField([
            'name' => 'starable',
            'morphOptions' => [
                ['user'],
                ['user'],
            ],
        ]);
    }

    public function testItCanRegisterModelEventsInTheFields()
    {
        $this->crudPanel->setModel(User::class);

        $this->crudPanel->addField([
            'name' => 'name',
            'events' => [
                'created' => function ($entry) {
                },
                'creating' => function ($entry) {
                    $entry->email = 'backpack@laravel.com';
                    $entry->password = Hash::make('password');
                },
                'saving' => function ($entry) {
                },
                'saved' => function ($entry) {
                },
                'updating' => function ($entry) {
                },
                'updated' => function ($entry) {
                },
                'deleting' => function ($entry) {
                },
                'deleted' => function ($entry) {
                },
            ],
        ]);

        $this->crudPanel->registerFieldEvents();

        $this->assertNotEmpty($this->crudPanel->getModel()->getEventDispatcher()->getListeners('eloquent.created: Backpack\CRUD\Tests\Config\Models\User'));
        $this->assertNotEmpty($this->crudPanel->getModel()->getEventDispatcher()->getListeners('eloquent.creating: Backpack\CRUD\Tests\Config\Models\User'));
        $this->assertNotEmpty($this->crudPanel->getModel()->getEventDispatcher()->getListeners('eloquent.saving: Backpack\CRUD\Tests\Config\Models\User'));
        $this->assertNotEmpty($this->crudPanel->getModel()->getEventDispatcher()->getListeners('eloquent.saved: Backpack\CRUD\Tests\Config\Models\User'));
        $this->assertNotEmpty($this->crudPanel->getModel()->getEventDispatcher()->getListeners('eloquent.updating: Backpack\CRUD\Tests\Config\Models\User'));
        $this->assertNotEmpty($this->crudPanel->getModel()->getEventDispatcher()->getListeners('eloquent.updated: Backpack\CRUD\Tests\Config\Models\User'));
        $this->assertNotEmpty($this->crudPanel->getModel()->getEventDispatcher()->getListeners('eloquent.deleting: Backpack\CRUD\Tests\Config\Models\User'));
        $this->assertNotEmpty($this->crudPanel->getModel()->getEventDispatcher()->getListeners('eloquent.deleted: Backpack\CRUD\Tests\Config\Models\User'));

        $this->crudPanel->getModel()->create(['name' => 'test']);

        $this->assertEquals('backpack@laravel.com', User::latest('id')->first()->email);
    }

    public function testItCanCreateDynamicRelationships()
    {
        User::resolveRelationUsing('dynamicRelation', function ($user) {
            return $user->belongsTo(\Backpack\CRUD\Tests\config\Models\Bang::class, 'bang_relation_field');
        });

        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships);
        $this->crudPanel->addField([
            'name' => 'dynamicRelation',
        ]);

        $faker = Factory::create();

        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
            'dynamicRelation' => 1,
        ];

        $entry = $this->crudPanel->create($inputData);

        $this->assertEquals($entry->dynamicRelation()->first()->name, Bang::find(1)->name);
    }

    private function getPivotInputData(array $pivotRelationData, bool $initCrud = true, bool $allowDuplicates = false)
    {
        $faker = Factory::create();

        if ($initCrud) {
            $this->crudPanel->setModel(User::class);
            $this->crudPanel->addFields($this->userInputFieldsNoRelationships);
            $this->crudPanel->addField([
                'name' => array_key_first($pivotRelationData),
                'allow_duplicate_pivots' => $allowDuplicates,
                'pivot_key_name' => 'id',
                'subfields' => [
                    [
                        'name' => 'notes',
                    ],

                ],
            ]);

            $article = Article::create([
                'content' => $faker->text(),
                'tags' => $faker->words(3, true),
                'user_id' => 1,
            ]);
            $article2 = Article::create([
                'content' => $faker->text(),
                'tags' => $faker->words(3, true),
                'user_id' => 1,
            ]);
        }

        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => Hash::make($faker->password()),
            'remember_token' => null,
        ];

        return array_merge($inputData, $pivotRelationData);
    }

    public function testMorphOneRelationshipWithPreventMassAssignment()
    {
        // setup model constrains
        $previousState = \Illuminate\Database\Eloquent\Model::preventsSilentlyDiscardingAttributes();
        \Illuminate\Database\Eloquent\Model::preventSilentlyDiscardingAttributes(true);

        try {
            $this->crudPanel->setModel(\Backpack\CRUD\Tests\config\Models\Comment::class);

            $this->crudPanel->addField([
                'name' => 'commentable',
                'type' => 'relationship',
            ]);

            \Backpack\CRUD\Tests\config\Models\Comment::resolveRelationUsing('star', function ($comment) {
                return $comment->morphOne(\Backpack\CRUD\Tests\config\Models\Star::class, 'starable');
            });

            $this->crudPanel->addField([
                'name' => 'star',
                'type' => 'relationship',
                'subfields' => [
                    ['name' => 'title'],
                ],
            ]);

            $inputData = [
                'commentable' => [
                    'commentable_type' => 'Backpack\CRUD\Tests\config\Models\User',
                    'commentable_id' => 1,
                ],
                'star' => [
                    'title' => 'Sun',
                ],
            ];

            $entry = $this->crudPanel->create($inputData);

            $this->assertInstanceOf(\Backpack\CRUD\Tests\config\Models\Comment::class, $entry);
            $this->assertNotNull($entry->star);
            $this->assertEquals('Sun', $entry->star->title);
        } finally {
            \Illuminate\Database\Eloquent\Model::preventSilentlyDiscardingAttributes($previousState);
        }
    }

    public function testHasOneDotNotationRelationshipWithPreventMassAssignment()
    {
        $previousState = \Illuminate\Database\Eloquent\Model::preventsSilentlyDiscardingAttributes();
        \Illuminate\Database\Eloquent\Model::preventSilentlyDiscardingAttributes(true);

        try {
            $this->crudPanel->setModel(\Backpack\CRUD\Tests\config\Models\Comment::class);

            $this->crudPanel->addField([
                'name' => 'commentable',
                'type' => 'relationship',
            ]);

            \Backpack\CRUD\Tests\config\Models\Comment::resolveRelationUsing('accountDetails', function ($comment) {
                return $comment->hasOne(\Backpack\CRUD\Tests\config\Models\AccountDetails::class, 'article_id');
            });

            $this->crudPanel->addField([
                'name' => 'accountDetails.nickname',
                'type' => 'text',
            ]);
            $this->crudPanel->addField([
                'name' => 'accountDetails.user_id',
                'type' => 'hidden',
            ]);
            $this->crudPanel->addField([
                'name' => 'accountDetails.profile_picture',
                'type' => 'text',
            ]);

            $inputData = [
                'commentable' => [
                    'commentable_type' => 'Backpack\CRUD\Tests\config\Models\User',
                    'commentable_id' => 1,
                ],
                'accountDetails.nickname' => 'Nick',
                'accountDetails.user_id' => 1,
                'accountDetails.profile_picture' => 'pic.jpg',
            ];

            $entry = $this->crudPanel->create($inputData);

            $this->assertInstanceOf(\Backpack\CRUD\Tests\config\Models\Comment::class, $entry);
            $this->assertNotNull($entry->accountDetails);
            $this->assertEquals('Nick', $entry->accountDetails->nickname);
        } finally {
            \Illuminate\Database\Eloquent\Model::preventSilentlyDiscardingAttributes($previousState);
        }
    }
}
