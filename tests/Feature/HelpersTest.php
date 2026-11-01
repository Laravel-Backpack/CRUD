<?php

namespace Backpack\CRUD\Tests\Feature;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Once;

class HelpersTest extends BaseDBCrudPanel
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('backpack.base.user_model_fqn', User::class);
    }

    public function test_backpack_users_have_email_queries_the_schema_only_once()
    {
        DB::enableQueryLog();
        DB::flushQueryLog();

        $this->assertTrue(backpack_users_have_email());
        $this->assertNotEmpty(DB::getQueryLog(), 'the first call must check the schema');

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

    public function test_backpack_users_have_email_does_not_outlive_the_request()
    {
        config(['backpack.base.email_column' => 'backup_email']);

        $this->assertFalse(backpack_users_have_email());

        Schema::table('users', function (Blueprint $table) {
            $table->string('backup_email')->nullable();
        });

        // still memoized within the same request
        $this->assertFalse(backpack_users_have_email());

        // what Octane does between requests
        Once::flush();

        $this->assertTrue(backpack_users_have_email());
    }
}
