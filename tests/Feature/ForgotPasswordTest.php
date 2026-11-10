<?php

namespace Backpack\CRUD\Tests\Feature;

use Backpack\CRUD\app\Notifications\ResetPasswordNotification;
use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Models\NotifiableUser as User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;

/**
 * @covers Backpack\CRUD\app\Http\Controllers\Auth\ForgotPasswordController
 */
class ForgotPasswordTest extends BaseDBCrudPanel
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('backpack.base.user_model_fqn', User::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create(config('auth.passwords.'.config('backpack.base.passwords').'.table'), function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Notification::fake();
    }

    public function test_the_response_is_the_same_whether_the_account_exists_or_not()
    {
        $existing = $this->post(backpack_url('password/email'), ['email' => User::find(1)->email]);
        $missing = $this->post(backpack_url('password/email'), ['email' => 'nobody@example.com']);
        $throttled = $this->post(backpack_url('password/email'), ['email' => User::find(1)->email]);

        foreach ([$existing, $missing, $throttled] as $response) {
            $response->assertRedirect();
            $response->assertSessionHasNoErrors();
            $response->assertSessionHas('status', trans('backpack::base.reset_link_sent'));
        }

        Notification::assertSentToTimes(User::find(1), ResetPasswordNotification::class, 1);
    }
}
