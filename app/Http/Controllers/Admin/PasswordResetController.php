<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ResetPasswordRequest;
use App\Http\Requests\Admin\SendPasswordLinkRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Class PasswordResetController.
 *
 * "Forgot password?": an email with a link, which sets a new password. Whether the email
 * belongs to someone isn't told.
 */
class PasswordResetController extends AdminPageController
{
    /**
     * The page, which asks for the email.
     *
     * @param Request $request
     *
     * @return View
     */
    public function create(Request $request): View
    {
        return $this->page($request, 'forgot-password', 'Forgot password', [
            'email' => (string) $request->old('email', $request->query('email', '')),
            // "link_sent", once it's sent
            'status' => $request->session()->get('status'),
            // "throttled" or "invalid"
            'error' => $request->session()->get('password_error')
                ?? ($request->session()->get('errors')?->any() ? 'invalid' : null),
            'urls' => [
                'submit' => route('admin.password.email'),
                'sign_in' => route('admin.login'),
            ],
        ]);
    }

    /**
     * Email the link (if the email belongs to someone).
     *
     * @param SendPasswordLinkRequest $request
     *
     * @return RedirectResponse
     */
    public function store(SendPasswordLinkRequest $request): RedirectResponse
    {
        $status = Password::broker()->sendResetLink($request->only('email'));
        $back = redirect()->route('admin.password.request')->withInput($request->only('email'));

        return $status === Password::RESET_THROTTLED
            ? $back->with('password_error', 'throttled')
            : $back->with('status', 'link_sent');
    }

    /**
     * The page, which sets a new password.
     *
     * @param Request $request
     * @param string $token
     *
     * @return View
     */
    public function edit(Request $request, string $token): View
    {
        return $this->page($request, 'reset-password', 'New password', [
            'token' => $token,
            'email' => (string) $request->old('email', $request->query('email', '')),
            // "invalid_link", or fields with problems: "password"
            'error' => $request->session()->get('password_error'),
            'fields' => array_keys($request->session()->get('errors')?->getMessages() ?? []),
            'urls' => [
                'submit' => route('admin.password.update'),
                'forgot' => route('admin.password.request'),
                'sign_in' => route('admin.login'),
            ],
        ]);
    }

    /**
     * Set the new password, and go to the sign-in page (with the email), to sign in with it.
     *
     * @param ResetPasswordRequest $request
     *
     * @return RedirectResponse
     */
    public function update(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['remember_token' => Str::random(60)]);
                // hashed by the model
                $user->password = $password;
                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->with('password_error', 'invalid_link');
        }

        return redirect()->route('admin.login', ['email' => $request->validated('email')])
            ->with('status', 'password_reset');
    }
}
