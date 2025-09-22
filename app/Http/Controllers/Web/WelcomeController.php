<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Traits\SharesPropsTrait;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    use SharesPropsTrait;

    public function index(Request $request): View
    {
        return view('web.welcome', [
            ...$this->getSharedProps($request),
        ]);
    }
}
