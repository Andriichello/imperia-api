<?php

namespace Tests\Http\Controllers\Auth;

use App\Models\Customer;
use App\Models\User;
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
                'name' => $name = 'Test',
                'surname' => $surname = 'Testers',
                'email' => 'test@email.com',
                'password' => 'pa$$w0rd',
            ],
        );

        $response->assertCreated();
        $response->assertJsonStructure([
            'data',
            'message',
        ]);

        /** @var User $user */
        $user = User::query()->findOrFail($response->json('data.id'));

        $this->assertNotEmpty($user->customer);

        $this->assertEquals($name . ' ' . $surname, $user->name);
        $this->assertEquals($name . ' ' . $surname, $user->customer->fullName);

        $this->assertStringContainsString($user->customer->name, $name);
        $this->assertStringContainsString($user->customer->surname, $surname);
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

    /**
     * Test register for already attached customer.
     *
     * @return void
     */
    public function testRegisterForAttachedCustomer()
    {
        $user = User::factory()
            ->create(['email' => 'one@email.com']);

        Customer::factory()
            ->create(['email' => 'two@email.com', 'user_id' => $user->id]);

        $response = $this->postJson(
            '/api/register',
            [
                'name' => 'Test',
                'surname' => 'Two',
                'email' => 'two@email.com',
                'password' => 'pa$$w0rd',
            ],
        );

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'email',
        ]);
    }
}
