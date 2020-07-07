<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\Unit\Models\Article;
use Backpack\CRUD\Tests\Unit\Models\User;
use Faker\Factory;
use Illuminate\Support\Arr;

class CrudPanelCreateTest extends BaseDBCrudPanelTest
{
    private $nonRelationshipField = [
        'name'  => 'field1',
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
            'label'     => 'Author',
            'type'      => 'select',
            'name'      => 'user_id',
            'entity'    => 'user',
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
            'label'     => 'Roles',
            'type'      => 'select_multiple',
            'name'      => 'roles',
            'entity'    => 'roles',
            'attribute' => 'name',
            'pivot'     => true,
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
            'label'     => 'Roles',
            'type'      => 'relationship',
            'name'      => 'roles',
            'entity'    => 'roles',
            'attribute' => 'name',
        ], [
            'label'     => 'Street',
            'name'      => 'street',
            'entity'    => 'accountDetails.addresses',
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

    public function testCreate()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships);
        $faker = Factory::create();
        $inputData = [
            'name'     => $faker->name,
            'email'    => $faker->safeEmail,
            'password' => bcrypt($faker->password()),
        ];

        $entry = $this->crudPanel->create($inputData);

        $this->assertInstanceOf(User::class, $entry);
        $this->assertEntryEquals($inputData, $entry);
        $this->assertEmpty($entry->articles);
    }

    /**
     * @group failing
     */
    public function testCreateWithOneToOneRelationship()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsNoRelationships);
        $this->crudPanel->addFields($this->userInputHasOneRelation);
        $faker = Factory::create();
        $inputData = [
            'name'     => $faker->name,
            'email'    => $faker->safeEmail,
            'password' => bcrypt($faker->password()),
            'accountDetails' => [
                'nickname' => $faker->name,
                'profile_picture' => 'test.jpg',
            ],
        ];
        $entry = $this->crudPanel->create($inputData);
        $this->markTestIncomplete('Has one relation is not created in tests.');
    }

    public function testCreateWithOneToManyRelationship()
    {
        $this->crudPanel->setModel(Article::class);
        $this->crudPanel->addFields($this->articleInputFieldsOneToMany);
        $faker = Factory::create();
        $inputData = [
            'content'     => $faker->text(),
            'tags'        => $faker->words(3, true),
            'user_id'     => 1,
            'metas'       => null,
            'extras'      => null,
            'cast_metas'  => null,
            'cast_tags'   => null,
            'cast_extras' => null,
        ];

        $entry = $this->crudPanel->create($inputData);
        $userEntry = User::find(1);
        $article = Article::where('user_id', 1)->with('user')->get()->last();
        $this->assertEntryEquals($inputData, $entry);
        $this->assertEquals($article->user_id, $entry->user_id);
        $this->assertEquals($article->id, $entry->id);
    }

    public function testCreateWithManyToManyRelationship()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsManyToMany);
        $faker = Factory::create();
        $inputData = [
            'name'           => $faker->name,
            'email'          => $faker->safeEmail,
            'password'       => bcrypt($faker->password()),
            'remember_token' => null,
            'roles'          => [1, 2],
        ];

        $entry = $this->crudPanel->create($inputData);

        $this->assertInstanceOf(User::class, $entry);
        $this->assertEntryEquals($inputData, $entry);
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
        //var_dump($this->crudPanel->get('create.fields')['street']);

        $this->assertEquals($this->crudPanel->get('create.fields')['street'], Arr::last($relationFields));
    }

    public function testCreateHasOneRelations()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');

        $this->crudPanel->addFields($this->userInputHasOneRelation);
        $faker = Factory::create();

        $inputData = [
            'name'           => $faker->name,
            'email'          => $faker->safeEmail,
            'password'       => bcrypt($faker->password()),
            'remember_token' => null,
            'roles'          => [1, 2],
            'accountDetails' => [
                'nickname' => 'i_have_has_one',
                'profile_picture' => 'simple_picture.jpg',
            ],
        ];
        $entry = $this->crudPanel->create($inputData);
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

    public function testCreateOneToOneRelationships()
    {
    }

    public function testSyncPivot()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsManyToMany);
        $faker = Factory::create();
        $inputData = [
            'name'           => $faker->name,
            'email'          => $faker->safeEmail,
            'password'       => bcrypt($faker->password()),
            'remember_token' => null,
            'roles'          => [1, 2],
        ];

        $entry = User::find(1);
        $this->crudPanel->syncPivot($entry, $inputData);

        $this->assertEquals($inputData['roles'], $entry->roles->pluck('id')->toArray());
    }

    public function testSyncPivotUnknownData()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->nonRelationshipField);
        $faker = Factory::create();
        $inputData = [
            'name'           => $faker->name,
            'email'          => $faker->safeEmail,
            'password'       => bcrypt($faker->password()),
            'remember_token' => null,
            'roles'          => [1, 2],
        ];

        $entry = User::find(1);
        $this->crudPanel->syncPivot($entry, $inputData);

        $this->assertEquals(1, $entry->roles->count());
    }

    public function testSyncPivotUnknownModel()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsManyToMany);
        $faker = Factory::create();
        $inputData = [
            'name'           => $faker->name,
            'email'          => $faker->safeEmail,
            'password'       => bcrypt($faker->password()),
            'remember_token' => null,
            'roles'          => [1, 2],
        ];

        $entry = Article::find(1);
        $this->crudPanel->syncPivot($entry, $inputData);
    }
}
