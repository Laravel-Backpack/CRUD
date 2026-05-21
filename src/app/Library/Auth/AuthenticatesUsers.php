<?php

namespace Backpack\CRUD\app\Library\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;

trait AuthenticatesUsers
{
    use RedirectsUsers, ThrottlesLogins;

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\View
     */
    public function showLoginForm()
    {
        $this->data['title'] = trans('backpack::base.login'); // set the page title
        $this->data['username'] = $this->username();

        $content = view(backpack_view('auth.login'), $this->data)->render();

        return response($this->injectCsrfRefreshScript($content));
    }

    /**
     * @param  string  $html
     * @return string
     */
    protected function injectCsrfRefreshScript(string $html): string
    {
        // Reload at 85% of the session lifetime to give a comfortable margin.
        $reloadAfterMs = (int) round(config('session.lifetime', 120) * 60 * 1000 * 0.85);

        $script = <<<HTML

        <script>
        (function () {
            var loadedAt = Date.now();
            var reloadAfterMs = {$reloadAfterMs};
            var timer;

            function scheduleReload() {
                clearTimeout(timer);
                var remaining = reloadAfterMs - (Date.now() - loadedAt);
                if (remaining <= 0) {
                    window.location.reload();
                    return;
                }
                timer = setTimeout(function () { window.location.reload(); }, remaining);
            }

            // Reload when the tab becomes visible again after being hidden or sleeping.
            document.addEventListener('visibilitychange', function () {
                if (!document.hidden) {
                    scheduleReload();
                }
            });

            // Start the countdown immediately (handles the tab staying open in the foreground).
            scheduleReload();
        })();
        </script>
        HTML;

        return str_replace('</body>', $script."\n</body>", $html);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            if (config('backpack.base.setup_email_verification_routes', false)) {
                return $this->logoutIfEmailNotVerified($request);
            }

            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        if ($response = $this->authenticated($request, $this->guard()->user())) {
            return $response;
        }

        return $request->wantsJson()
                    ? new Response('', 204)
                    : redirect()->intended($this->redirectPath());
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        //
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'email';
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new Response('', 204)
            : redirect('/');
    }

    /**
     * The user has logged out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        //
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    private function logoutIfEmailNotVerified(Request $request): Response|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
    {
        $user = $this->guard()->user();

        // if the user is already verified, do nothing
        if ($user->email_verified_at) {
            return $this->sendLoginResponse($request);
        }
        // user is not yet verified, log him out
        $this->guard()->logout();

        // add a cookie for 30m to remember the email address that needs to be verified
        Cookie::queue('backpack_email_verification', $user->{config('backpack.base.email_column')}, 30);

        if ($request->wantsJson()) {
            return new Response('Email verification required', 403);
        }

        return redirect(route('verification.notice'));
    }
}
