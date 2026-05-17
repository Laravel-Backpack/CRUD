<?php

namespace Backpack\CRUD\Tests81\Unit\CrudPanel;

use Backpack\CRUD\Tests\Unit\CrudPanel\BaseDBCrudPanelTest;
use Faker\Factory;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Update
 */
class CrudPanelUpdateTest extends BaseDBCrudPanelTest
{
    public function testGetUpdateFieldsWithEnum()
    {
        if ((int) app()->version() < 9) {
            return;
        }

        $this->crudPanel->setModel(\Backpack\CRUD\Tests81\Unit\Models\ArticleWithEnum::class);
        $this->crudPanel->addFields([[
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
        ], [
            'name' => 'status',
        ],
            [
                'name' => 'state',
            ],
            [
                'name' => 'style',
            ],
        ]);
        $faker = Factory::create();
        $inputData = [
            'content' => $faker->text(),
            'tags' => $faker->words(3, true),
            'user_id' => 1,
            'metas' => null,
            'extras' => null,
            'status' => 'publish',
            'state' => 'COLD',
            'style' => 'DRAFT',
            'cast_metas' => null,
            'cast_tags' => null,
            'cast_extras' => null,
        ];
        $article = $this->crudPanel->create($inputData);

        $updateFields = $this->crudPanel->getUpdateFields(2);

        $this->assertTrue($updateFields['status']['value']->value === 'publish');
        $this->assertTrue($updateFields['status']['value']->name === 'PUBLISHED');
        $this->assertTrue($updateFields['state']['value']->name === 'COLD');
        $this->assertTrue($updateFields['style']['value']->color() === 'red');
    }
}
