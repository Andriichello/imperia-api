<?php

namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Repositories\UserRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\RedirectResponse;

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
        return route('nova.login');
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
     * @return RedirectResponse
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $this->userRepository->register($request->validated());

        return redirect($this->redirectPath());
    }
}
