<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Class WelcomeController.
 */
class WelcomeController extends Controller
{
    /**
     * Returns welcome page for UI with Inertia.js.
     *
     * @return Response
     */
    public function __invoke(): Response
    {
        return Inertia::render('Welcome');
    }
}
