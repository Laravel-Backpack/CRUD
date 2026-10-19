<?php

namespace Backpack\CRUD\Tests\Feature;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Models\User;
use Illuminate\Support\Facades\DB;

class HelpersTest extends BaseDBCrudPanel
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('backpack.base.user_model_fqn', User::class);
    }

    public function test_backpack_users_have_email_queries_the_schema_only_once()
    {
        // warm the cache
        $this->assertTrue(backpack_users_have_email());

        DB::enableQueryLog();
        DB::flushQueryLog();

        for ($i = 0; $i < 5; $i++) {
            $this->assertTrue(backpack_users_have_email());
        }

        $this->assertCount(0, DB::getQueryLog(), 'repeated calls must not hit the database again');
    }

    public function test_backpack_users_have_email_is_cached_per_email_column()
    {
        $this->assertTrue(backpack_users_have_email());

        config(['backpack.base.email_column' => 'column_that_does_not_exist']);

        $this->assertFalse(backpack_users_have_email());
    }
}
