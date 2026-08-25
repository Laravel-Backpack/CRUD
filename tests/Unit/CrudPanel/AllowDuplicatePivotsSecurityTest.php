<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Models\User;
use Faker\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Create
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Update
 */
class AllowDuplicatePivotsSecurityTest extends BaseDBCrudPanel
{
    private function setUpDuplicatePivotCrud(): void
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setOperation('create');
        $this->crudPanel->addFields([
            ['name' => 'id', 'type' => 'hidden'],
            ['name' => 'name'],
            ['name' => 'email', 'type' => 'email'],
            ['name' => 'password', 'type' => 'password'],
        ]);
        $this->crudPanel->addField([
            'name' => 'superArticlesDuplicates',
            'allow_duplicate_pivots' => true,
            'pivot_key_name' => 'id',
            'options' => fn () => [1],
            'subfields' => [
                ['name' => 'notes'],
            ],
        ]);
    }

    private function userInput(?array $pivotRows = null): array
    {
        $faker = Factory::create();

        $input = [
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => 'password123',
            'remember_token' => null,
        ];

        if ($pivotRows !== null) {
            $input['superArticlesDuplicates'] = $pivotRows;
        }

        return $input;
    }

    public function testDuplicatePivotUpdateCannotReparentAnotherUsersPivotRow()
    {
        $this->setUpDuplicatePivotCrud();

        $firstUser = $this->crudPanel->create($this->userInput([
            ['superArticlesDuplicates' => 1, 'notes' => 'first user note', 'id' => null],
        ]));

        $firstUserPivotId = DB::table('articles_user')->where('user_id', $firstUser->id)->value('id');
        $this->assertNotNull($firstUserPivotId);

        $secondUser = $this->crudPanel->create($this->userInput());

        $this->crudPanel->update($secondUser->id, $this->userInput([
            ['superArticlesDuplicates' => 1, 'notes' => 'second user note', 'id' => $firstUserPivotId],
        ]));

        $this->assertDatabaseHas('articles_user', [
            'id' => $firstUserPivotId,
            'user_id' => $firstUser->id,
            'notes' => 'first user note',
        ]);

        $this->assertDatabaseMissing('articles_user', [
            'id' => $firstUserPivotId,
            'user_id' => $secondUser->id,
        ]);

        $this->assertCount(1, $firstUser->fresh()->superArticlesDuplicates);
        $this->assertCount(0, $secondUser->fresh()->superArticlesDuplicates);
    }

    public function testDuplicatePivotDeleteIsScopedToTheSavedRecord()
    {
        $this->setUpDuplicatePivotCrud();

        $firstUser = $this->crudPanel->create($this->userInput([
            ['superArticlesDuplicates' => 1, 'notes' => 'first user note', 'id' => null],
        ]));

        $firstUserPivotId = DB::table('articles_user')->where('user_id', $firstUser->id)->value('id');

        $secondUser = $this->crudPanel->create($this->userInput([
            ['superArticlesDuplicates' => 1, 'notes' => 'second user note', 'id' => null],
        ]));

        $secondUserPivotId = DB::table('articles_user')->where('user_id', $secondUser->id)->value('id');

        $this->crudPanel->update($secondUser->id, $this->userInput([
            ['superArticlesDuplicates' => 1, 'notes' => 'second user note updated', 'id' => $secondUserPivotId],
        ]));

        $this->assertDatabaseHas('articles_user', [
            'id' => $firstUserPivotId,
            'user_id' => $firstUser->id,
        ]);
        $this->assertCount(1, $firstUser->fresh()->superArticlesDuplicates);
    }
}
