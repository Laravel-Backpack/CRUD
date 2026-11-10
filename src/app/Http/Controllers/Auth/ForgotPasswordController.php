<?php

namespace Backpack\CRUD\app\Http\Controllers\Auth;

use Backpack\CRUD\app\Library\Auth\PasswordBrokerManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    protected $data = []; // the information we send to the view

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $guard = backpack_guard_name();

        $this->middleware("guest:$guard");
    }

    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showLinkRequestForm()
    {
        $this->data['title'] = trans('backpack::base.reset_password'); // set the page title

        return view(backpack_view('auth.passwords.email'), $this->data);
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $broker = $this->broker();

        $response = $broker->sendResetLink(
            $this->credentials($request)
        );

        // give the same response whether there is an account for the given email or not
        $responsesForAnyEmail = [
            Password::RESET_LINK_SENT, Password::INVALID_USER, Password::RESET_THROTTLED,
            $broker::RESET_LINK_SENT, $broker::INVALID_USER, $broker::RESET_THROTTLED,
        ];

        return in_array($response, $responsesForAnyEmail, true)
                    ? $this->sendResetLinkResponse($request, Password::RESET_LINK_SENT)
                    : $this->sendResetLinkFailedResponse($request, $response);
    }

    /**
     * Validate the email for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
    }

    /**
     * Get the needed authentication credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only('email');
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        $message = trans('backpack::base.reset_link_sent');

        return $request->wantsJson()
                    ? new JsonResponse(['message' => $message], 200)
                    : back()->with('status', $message);
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        if ($request->wantsJson()) {
            throw ValidationException::withMessages([
                'email' => [trans($response)],
            ]);
        }

        return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => trans($response)]);
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        $passwords = config('backpack.base.passwords', config('auth.defaults.passwords'));
        $manager = new PasswordBrokerManager(app());

        return $manager->broker($passwords);
    }
}
