<?php

namespace Backpack\CRUD\Tests\Feature;

use Backpack\CRUD\Tests\config\CrudPanel\BaseDBCrudPanel;
use Backpack\CRUD\Tests\config\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;

/**
 * @covers Backpack\CRUD\app\Http\Middleware\AuthenticateSession
 * @covers Backpack\CRUD\app\Http\Controllers\Auth\ResetPasswordController
 */
class AuthenticateSessionTest extends BaseDBCrudPanel
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('backpack.base.user_model_fqn', User::class);
    }

    protected function defineRoutes($router)
    {
        $router->get(config('backpack.base.route_prefix').'/authenticate-session', fn () => 'ok')
            ->middleware(['web', backpack_middleware()]);
    }

    /**
     * Laravel 12.69.3+ and 13.33+ store an HMAC of the password hash in the session when logging in.
     */
    public function test_user_stays_authenticated_after_logging_in()
    {
        $this->login();

        $this->get(backpack_url('authenticate-session'))->assertOk();
    }

    /**
     * Laravel 12.45+ stores an HMAC of the password hash in the "remember me" cookie.
     */
    public function test_user_stays_authenticated_through_the_remember_me_cookie()
    {
        // the middleware checks the "remember me" cookie of the default guard
        config(['auth.defaults.guard' => backpack_guard_name()]);

        $recaller = backpack_auth()->getRecallerName();
        $cookie = $this->login(['remember' => 1])->getCookie($recaller);

        // expire the session, so the user is authenticated by the "remember me" cookie
        $this->app['auth']->forgetGuards();
        $this->flushSession();

        $this->withCookie($recaller, $cookie->getValue())
            ->get(backpack_url('authenticate-session'))
            ->assertOk();
    }

    /**
     * Sessions created before Laravel stored the HMAC have the password hash itself.
     */
    public function test_user_stays_authenticated_with_the_password_hash_in_the_session()
    {
        $this->login();

        session()->put('password_hash_'.backpack_guard_name(), User::find(1)->getAuthPassword());

        $this->get(backpack_url('authenticate-session'))->assertOk();
    }

    /**
     * Resetting the password logs the user in and then rehashes the password to log out other devices.
     */
    public function test_user_stays_authenticated_after_resetting_the_password()
    {
        Schema::create(config('auth.passwords.'.config('backpack.base.passwords').'.table'), function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        $user = User::find(1);

        $this->post(backpack_url('password/reset'), [
            'token' => Password::broker(config('backpack.base.passwords'))->createToken($user),
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(backpack_url());

        $this->get(backpack_url('authenticate-session'))->assertOk();
    }

    /**
     * Changing the password in another session must still log the user out.
     */
    public function test_user_is_logged_out_when_the_password_changes()
    {
        $this->login();
        $this->get(backpack_url('authenticate-session'))->assertOk();

        User::find(1)->update(['password' => Hash::make('another-password')]);
        $this->app['auth']->forgetGuards();

        $this->get(backpack_url('authenticate-session'))->assertRedirect(backpack_url('login'));
    }

    private function login(array $data = [])
    {
        return $this->post(backpack_url('login'), [
            'email' => User::find(1)->email,
            'password' => 'secret',
        ] + $data);
    }
}
