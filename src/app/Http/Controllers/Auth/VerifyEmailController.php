<?php

namespace Backpack\CRUD\app\Http\Controllers\Auth;

use Backpack\CRUD\app\Http\Requests\EmailVerificationRequest;
use Backpack\CRUD\app\Library\Auth\UserFromCookie;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VerifyEmailController extends Controller
{
    public ?string $redirectTo = null;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        if (! app()->runningInConsole() && ! array_key_exists('signed', app('router')->getMiddleware())) {
            throw new Exception('Missing "signed" alias middleware in App/Http/Kernel.php. More info: https://backpackforlaravel.com/docs/6.x/base-how-to#enable-email-verification-in-backpack-routes');
        }

        $this->middleware('signed')->only('verifyEmail');
        $this->middleware('throttle:'.config('backpack.base.email_verification_throttle_access'))->only('resendVerificationEmail');

        if (! backpack_users_have_email()) {
            abort(500, trans('backpack::base.no_email_column'));
        }
        // where to redirect after the email is verified
        $this->redirectTo = $this->redirectTo ?? backpack_url('dashboard');
    }

    public function emailVerificationRequired(Request $request): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $this->getUserOrRedirect($request);

        return view(backpack_view('auth.verify-email'));
    }

    /**
     * Verify the user's email address.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function verifyEmail(EmailVerificationRequest $request)
    {
        $this->getUserOrRedirect($request);

        $request->fulfill();

        return redirect($this->redirectTo);
    }

    /**
     * Resend the email verification notification.
     */
    public function resendVerificationEmail(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $this->getUserOrRedirect($request);

        if (is_a($user, \Illuminate\Http\RedirectResponse::class)) {
            return $user;
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }

    private function getUser(Request $request): ?\Illuminate\Contracts\Auth\MustVerifyEmail
    {
        return $request->user(backpack_guard_name()) ?? (new UserFromCookie())();
    }

    private function getUserOrRedirect(Request $request): \Illuminate\Contracts\Auth\MustVerifyEmail|\Illuminate\Http\RedirectResponse
    {
        if ($user = $this->getUser($request)) {
            return $user;
        }

        return redirect()->route('backpack.auth.login');
    }
}
