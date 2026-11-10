<?php

namespace Backpack\CRUD\Tests\Feature;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Models\NotifiableUser as User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;

/**
 * @covers Backpack\CRUD\app\Http\Controllers\MyAccountController
 */
class MyAccountTest extends BaseDBCrudPanel
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('backpack.base.user_model_fqn', User::class);
        $app['config']->set('backpack.base.setup_my_account_routes', true);
        $app['config']->set('backpack.base.setup_email_verification_routes', true);
        $app['config']->set('backpack.base.setup_email_verification_middleware', false);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable();
        });

        User::query()->update(['email_verified_at' => now()]);

        Notification::fake();
        $this->actingAs(User::find(1), backpack_guard_name());
    }

    public function test_changing_the_email_requires_verifying_the_new_address()
    {
        $this->post(backpack_url('edit-account-info'), [
            'name' => 'New Name',
            'email' => 'new-address@example.com',
            'current_password' => 'secret',
        ])->assertRedirect();

        $user = User::find(1);

        $this->assertSame('new-address@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_changing_only_the_name_keeps_the_email_verified()
    {
        $user = User::find(1);

        $this->post(backpack_url('edit-account-info'), [
            'name' => 'New Name',
            'email' => $user->email,
        ])->assertRedirect();

        $user->refresh();

        $this->assertSame('New Name', $user->name);
        $this->assertNotNull($user->email_verified_at);
        Notification::assertNothingSent();
    }
}
