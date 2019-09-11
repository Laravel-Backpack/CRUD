<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Faker\Factory;
use Backpack\CRUD\Tests\Unit\Models\User;
use Backpack\CRUD\Tests\Unit\Models\Article;

class CrudPanelCreateTest extends BaseDBCrudPanelTest
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
            'type' => 'select_multiple',
            'name' => 'roles',
            'entity' => 'roles',
            'attribute' => 'name',
            'pivot' => true,
        ], [
            'label' => 'Street',
            'name' => 'street',
            'entity' => 'accountDetails.addresses',
            'attribute' => 'street',
        ],
    ];

    private $userInputFieldsManyToOne = [
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
        ], [
            'name' => 'articles',
            'type' => 'select2_many',
            'entity' => 'articles',
            'model' => Article::class,
            'attribute' => 'content',
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
            'password' => bcrypt($faker->password()),
        ];

        $entry = $this->crudPanel->create($inputData);

        $this->assertInstanceOf(User::class, $entry);
        $this->assertEntryEquals($inputData, $entry);
        $this->assertEmpty($entry->articles);
    }

    public function testCreateWithOneToOneRelationship()
    {
        $this->markTestIncomplete('Not yet implemented');
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
        $this->assertEquals($article->toArray(), $entry->toArray());
    }

    public function testCreateWithManyToOneRelationship()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsManyToOne);
        $faker = Factory::create();
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => bcrypt($faker->password()),
            'remember_token' => null,
            'roles' => [1, 2],
            'articles' => [1, 2],
        ];

        $entry = $this->crudPanel->create($inputData);

        $this->assertInstanceOf(User::class, $entry);
        $this->assertEntryEquals($inputData, $entry);
    }

    public function testCreateWithManyToManyRelationship()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsManyToMany);
        $faker = Factory::create();
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => bcrypt($faker->password()),
            'remember_token' => null,
            'roles' => [1, 2],
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

        $this->assertEquals($this->crudPanel->create_fields['roles'], array_last($relationFields));
    }

    public function testGetRelationFieldsCreateForm()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsManyToMany, 'create');

        $relationFields = $this->crudPanel->getRelationFields('create');

        $this->assertEquals($this->crudPanel->create_fields['roles'], array_last($relationFields));
    }

    public function testGetRelationFieldsUpdateForm()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsManyToMany, 'update');

        $relationFields = $this->crudPanel->getRelationFields('update');

        $this->assertEquals($this->crudPanel->update_fields['roles'], array_last($relationFields));
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
        $this->crudPanel->addFields($this->userInputFieldsDotNotation, 'create');

        $relationFields = $this->crudPanel->getRelationFields('create');

        $this->assertEquals($this->crudPanel->create_fields['street'], array_last($relationFields));
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
        $this->crudPanel->addFields($this->userInputFieldsDotNotation, 'create');

        $relationFields = $this->crudPanel->getRelationFieldsWithPivot('create');

        $this->assertEquals($this->crudPanel->create_fields['roles'], array_last($relationFields));
    }

    public function testGetRelationFieldsWithPivotNoRelations()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->nonRelationshipField, 'create');

        $relationFields = $this->crudPanel->getRelationFieldsWithPivot('create');

        $this->assertEmpty($relationFields);
    }

    public function testSyncPivot()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->addFields($this->userInputFieldsManyToMany);
        $faker = Factory::create();
        $inputData = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => bcrypt($faker->password()),
            'remember_token' => null,
            'roles' => [1, 2],
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
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => bcrypt($faker->password()),
            'remember_token' => null,
            'roles' => [1, 2],
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
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => bcrypt($faker->password()),
            'remember_token' => null,
            'roles' => [1, 2],
        ];

        $entry = Article::find(1);
        $this->crudPanel->syncPivot($entry, $inputData);
    }
}
