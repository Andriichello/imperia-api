<?php

namespace Tests\Http\Controllers\Auth;

use Tests\RegisteringTestCase;

/**
 * Class RegisterControllerTest.
 */
class RegisterControllerTest extends RegisteringTestCase
{
    /**
     * Test register with valid form data.
     *
     * @return void
     */
    public function testRegisterWithValidFormData()
    {
        $response = $this->postJson(
            '/api/register',
            [
                'name' => 'Test Testers',
                'email' => 'test@email.com',
                'password' => 'pa$$w0rd',
            ],
        );

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'user',
                'token',
            ],
            'message'
        ]);
    }

    /**
     * Test register with invalid form data.
     *
     * @return void
     */
    public function testRegisterWithInvalidFormData()
    {
        $response = $this->postJson(
            '/api/register',
            [
                'name' => 'N',
                'email' => 'wrong-email@',
                'password' => 'word',
            ],
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'name',
            'email',
            'password',
        ]);
    }
}
