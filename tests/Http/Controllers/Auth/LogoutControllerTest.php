<?php

namespace Tests\Http\Controllers\Auth;

use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Tests\RegisteringTestCase;

/**
 * Class LogoutControllerTest.
 */
class LogoutControllerTest extends RegisteringTestCase
{
    use MakesHttpRequests;

    /**
     * If true, then user should be logged-in on set up.
     *
     * @var bool
     */
    protected bool $shouldLogin = true;

    /**
     * Test logout user request.
     *
     * @return void
     */
    public function testLogout()
    {
        $response = $this->deleteJson('/api/logout');
        $response->assertOk();
    }
}
