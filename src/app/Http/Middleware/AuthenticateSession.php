<?php

namespace Backpack\CRUD\app\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Session\Middleware\AuthenticateSession as LaravelAuthenticateSession;

class AuthenticateSession extends LaravelAuthenticateSession
{
    /**
     * The authentication factory implementation.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    protected $user;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(AuthFactory $auth)
    {
        $this->auth = $auth;
        $this->user = backpack_user();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $request->hasSession() || ! $this->user || ! $this->user->getAuthPassword()) {
            return $next($request);
        }

        if ($this->guard()->viaRemember()) {
            $passwordHash = explode('|', $request->cookies->get($this->guard()->getRecallerName()))[2] ?? null;

            if (! $passwordHash || ! $this->validatePasswordHash($this->user->getAuthPassword(), $passwordHash)) {
                $this->logout($request);
            }
        }

        if (! $request->session()->has('password_hash_'.backpack_guard_name())) {
            $this->storePasswordHashInSession($request);
        }

        if (! $this->validatePasswordHash($this->user->getAuthPassword(), $request->session()->get('password_hash_'.backpack_guard_name()))) {
            $this->logout($request);
        }

        return tap($next($request), function () use ($request) {
            if (! is_null($this->guard()->user())) {
                $this->storePasswordHashInSession($request);
            }
        });
    }

    /**
     * Store the user's current password hash in the session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function storePasswordHashInSession($request)
    {
        if (! $this->user) {
            return;
        }

        $request->session()->put([
            'password_hash_'.backpack_guard_name() => $this->hashPasswordForCookie($this->user->getAuthPassword()),
        ]);
    }

    /**
     * Validate the password hash against the value stored in the session or "remember me" cookie.
     * Laravel 12.45+ stores an HMAC of the password hash, before it stored the hash itself.
     *
     * @param  string  $passwordHash
     * @param  string  $storedValue
     * @return bool
     */
    protected function validatePasswordHash($passwordHash, $storedValue)
    {
        return hash_equals($this->hashPasswordForCookie($passwordHash), $storedValue)
            || hash_equals($passwordHash, $storedValue);
    }

    /**
     * Get the password hash as Laravel stores it in the session and "remember me" cookie,
     * an HMAC of it since Laravel 12.45, or the hash itself on older versions.
     *
     * @param  string  $passwordHash
     * @return string
     */
    protected function hashPasswordForCookie($passwordHash)
    {
        $guard = $this->auth->guard(backpack_guard_name());

        return method_exists($guard, 'hashPasswordForCookie')
            ? $guard->hashPasswordForCookie($passwordHash)
            : $passwordHash;
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function logout($request)
    {
        $this->guard()->logoutCurrentDevice();

        $request->session()->flush();

        \Alert::error(trans('backpack::base.session_expired_error'))->flash();

        throw new AuthenticationException('Unauthenticated.', [backpack_guard_name()], backpack_url('login'));
    }

    /**
     * Get the guard instance that should be used by the middleware.
     *
     * @return \Illuminate\Contracts\Auth\Factory|\Illuminate\Contracts\Auth\Guard
     */
    protected function guard()
    {
        return $this->auth;
    }
}
