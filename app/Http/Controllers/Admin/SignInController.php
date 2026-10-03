<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SignInRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Class SignInController.
 *
 * Signing in to the restaurant admin (and the admin panel, which shares the session). There's
 * no sign-up: restaurant owners invite their staff.
 */
class SignInController extends AdminPageController
{
    /**
     * Wrong passwords in a row, after which signing in with the email waits a minute.
     */
    public const MAX_ATTEMPTS = 5;

    /**
     * The sign-in page (a signed-in user goes to the dashboard).
     *
     * @param Request $request
     *
     * @return View|RedirectResponse
     */
    public function show(Request $request): View|RedirectResponse
    {
        if ($this->guard()->check()) {
            return redirect()->route('admin.dashboard');
        }

        return $this->page($request, 'sign-in', __('Sign in'), [
            'email' => (string) $request->old('email', $request->query('email', '')),
            'remember' => (bool) ($request->old('remember') ?? true),
            // why signing in failed: "credentials", "throttled", "not_allowed" or "invalid"
            'error' => $request->session()->get('sign_in_error')
                ?? ($request->session()->get('errors')?->any() ? ['code' => 'invalid'] : null),
            // e.g. "password_reset", after a new password was set
            'status' => $request->session()->get('status'),
            'urls' => [
                'submit' => route('admin.login.store'),
                'forgot' => route('admin.password.request'),
            ],
        ]);
    }

    /**
     * Sign in, and go where the user wanted to, or to their home: the dashboard (or the admin
     * panel for staff, who can't edit a restaurant). Customers can't sign in here.
     *
     * @param SignInRequest $request
     *
     * @return RedirectResponse
     */
    public function store(SignInRequest $request): RedirectResponse
    {
        $key = Str::transliterate(Str::lower($request->validated('email')) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($key, static::MAX_ATTEMPTS)) {
            return $this->failed($request, ['code' => 'throttled', 'seconds' => RateLimiter::availableIn($key)]);
        }

        $credentials = $request->only('email', 'password');

        if (!$this->guard()->attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($key);

            return $this->failed($request, ['code' => 'credentials']);
        }

        RateLimiter::clear($key);

        /** @var User $user */
        $user = $this->guard()->user();

        if (!$user->isStaff()) {
            $this->signOut($request);

            return $this->failed($request, ['code' => 'not_allowed']);
        }

        $request->session()->regenerate();

        return redirect()->intended($this->home($user));
    }

    /**
     * Sign out, and go to the sign-in page.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->signOut($request);

        return redirect()->route('admin.login');
    }

    /**
     * Back to the sign-in page with the email and the reason.
     *
     * @param SignInRequest $request
     * @param array $error
     *
     * @return RedirectResponse
     */
    protected function failed(SignInRequest $request, array $error): RedirectResponse
    {
        return redirect()->route('admin.login')
            ->withInput($request->only('email', 'remember'))
            ->with('sign_in_error', $error);
    }

    /**
     * Where the user starts: the dashboard, or the admin panel for staff, who can't edit a restaurant.
     *
     * @param User $user
     *
     * @return string
     */
    protected function home(User $user): string
    {
        return $this->restaurants->editableBy($user)->isNotEmpty()
            ? route('admin.dashboard')
            : route('filament.admin.pages.dashboard');
    }

    /**
     * The guard of the admin's session.
     *
     * @return StatefulGuard
     */
    protected function guard(): StatefulGuard
    {
        /** @var StatefulGuard $guard */
        $guard = Auth::guard('web');

        return $guard;
    }

    /**
     * Sign the user out of this session.
     *
     * @param Request $request
     *
     * @return void
     */
    protected function signOut(Request $request): void
    {
        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
