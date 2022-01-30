<?php

namespace Tests\Http\Controllers\Auth;

use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Tests\RegisteringTestCase;

/**
 * Class LoginControllerTest.
 */
class LoginControllerTest extends RegisteringTestCase
{
    use MakesHttpRequests;

    /**
     * If true, then user should be registered on set up.
     *
     * @var bool
     */
    protected bool $shouldRegister = true;

    /**
     * Test login user with valid credentials.
     *
     * @return void
     */
    public function testLoginWithValidCredentials()
    {
        $credentials = Arr::only($this->registerFormData, ['email', 'password']);
        $response = $this->postJson('/api/login', $credentials);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'user',
                'token',
            ],
            'message'
        ]);
    }

    /**
     * Test login user with invalid credentials.
     *
     * @return void
     */
    public function testLoginWithInvalidCredentials()
    {
        $credentials = Arr::only($this->registerFormData, ['email', 'password']);
        $credentials['password'] = 'wrong-password';

        $response = $this->postJson('/api/login', $credentials);
        $response->assertUnauthorized();
    }

    /**
     * Test login user with valid remember token.
     *
     * @return void
     */
    public function testLoginWithValidRememberToken()
    {
        $credentials = ['remember_token' => $this->user->remember_token];
        $response = $this->postJson('/api/login', $credentials);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'user',
                'token',
            ],
            'message'
        ]);
    }

    /**
     * Test login user with invalid remember token.
     *
     * @return void
     */
    public function testLoginWithInValidRememberToken()
    {
        $credentials = ['remember_token' => Str::random(10)];
        $response = $this->postJson('/api/login', $credentials);
        $response->assertUnauthorized();
    }
}
