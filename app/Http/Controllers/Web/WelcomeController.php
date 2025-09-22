<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class WelcomeController extends Controller
{
    public function index(): View
    {
        return view('web.welcome', [
            'props' => [
                'initialPath' => request()->getPathInfo(),
                'query' => request()->query(),
            ],
        ]);
    }
}
