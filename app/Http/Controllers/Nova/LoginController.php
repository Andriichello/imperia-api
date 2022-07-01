<?php

namespace App\Http\Controllers\Nova;

use App\Repositories\UserRepository;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\View\View;

/**
 * Class LoginController.
 */
class LoginController extends \Laravel\Nova\Http\Controllers\LoginController
{
    use AuthenticatesUsers, ValidatesRequests;

    /**
     * @var UserRepository
     */
    protected UserRepository $userRepository;

    /**
     * LoginController constructor.
     */
    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();

        $this->userRepository = $userRepository;
    }

    /**
     * Show the application's login form.
     *
     * @return View
     */
    public function showLoginForm()
    {
        return view('auth.login', ['action' => 'login']);
    }
}
