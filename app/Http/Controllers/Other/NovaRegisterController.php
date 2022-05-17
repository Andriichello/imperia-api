<?php

namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Repositories\UserRepository;
use Illuminate\Auth\RequestGuard;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Nova;

/**
 * Class NovaRegisterController.
 */
class NovaRegisterController extends Controller
{
    use RegistersUsers;

    /**
     * @var UserRepository
     */
    protected UserRepository $userRepository;

    /**
     * NovaRegisterController constructor.
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get the post register redirect path.
     *
     * @return string
     */
    public function redirectTo(): string
    {
        return Nova::path();
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return Guard|StatefulGuard
     */
    protected function guard(): Guard|StatefulGuard
    {
        return Auth::guard('web');
    }

    /**
     * Show nova registration form.
     *
     * @return View
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param RegisterRequest $request
     *
     * @return JsonResponse|RedirectResponse
     */
    public function register(RegisterRequest $request): JsonResponse|RedirectResponse
    {
        $user = $this->userRepository->register($request->validated());
        // @phpstan-ignore-next-line
        $this->guard()->login($user);

        return $request->wantsJson()
            ? new JsonResponse([], 201)
            : redirect($this->redirectPath());
    }
}
