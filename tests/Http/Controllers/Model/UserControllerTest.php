<?php

namespace Tests\Http\Controllers\Model;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Tests\RegisteringTestCase;

/**
 * Class UserControllerTest.
 */
class UserControllerTest extends RegisteringTestCase
{
    use MakesHttpRequests;

    /**
     * If true, then user should be logged-in on set up.
     *
     * @var bool
     */
    protected bool $shouldLogin = true;

    /**
     * Test index users.
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->getJson(route('api.users.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'meta'
        ]);

        $this->assertCount(1, $response['data']);
    }

    /**
     * Test get me (currently logged-in user).
     *
     * @return void
     */
    public function testMe()
    {
        $response = $this->getJson(route('api.users.me'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'message'
        ]);

        $this->assertEquals($this->user->id, data_get($response, 'data.id'));
    }

    /**
     * Test show user by id.
     *
     * @return void
     */
    public function testShow()
    {
        $response = $this->getJson(
            route('api.users.show', ['id' => $this->user->id])
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'message'
        ]);

        $this->assertEquals($this->user->id, data_get($response, 'data.id'));
    }

    /**
     * Test store user.
     *
     * @return void
     */
    public function testStore()
    {
        $response = $this->postJson(
            route('api.users.store', $attributes = [
                'name' => 'John Doe',
                'email' => 'john.doe@email.com',
                'password' => 'pa$$w0rd',
            ])
        );

        $response->assertCreated();
        $response->assertJsonStructure([
            'data',
            'message'
        ]);

        $this->assertDatabaseHas(User::class, Arr::only($attributes, ['name', 'email']));
    }

    /**
     * Test update user's name and email.
     *
     * @return void
     */
    public function testUpdateNameAndEmail()
    {
        $attributes = [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@email.com',
        ];

        $response = $this->patchJson(
            route('api.users.update', ['id' => $this->user->id]),
            $attributes,
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'message'
        ]);

        $this->assertDatabaseHas(User::class, $attributes);
    }

    /**
     * Test update user's name and email.
     *
     * @return void
     */
    public function testUpdatePassword()
    {
        $attributes = $this->registerFormData;
        $attributes['password'] = 'new-pa$$w0rd';
        $attributes['current_password'] = 'pa$$w0rd';

        $response = $this->patchJson(
            route('api.users.update', ['id' => $this->user->id]),
            $attributes,
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'message'
        ]);

        $user = $this->user->fresh();
        $this->assertTrue(Hash::check($attributes['password'], $user->password));
    }

    /**
     * Test delete user with a manager role.
     *
     * @return void
     */
    public function testDeleteManager()
    {
        $manager = $this->register(
            [
                'name' => 'Tony Brown',
                'email' => 'tony.brown@email.com',
                'password' => 'pa$$w0rd',
            ],
            UserRole::Manager,
        );

        $response = $this->deleteJson(
            route('api.users.destroy', ['id' => $manager->id]),
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'message',
        ]);

        $this->assertSoftDeleted($manager);
    }

    /**
     * Test delete user with an admin role.
     *
     * @return void
     */
    public function testDeleteAdmin()
    {
        $admin = $this->register(
            [
                'name' => 'Tony Brown',
                'email' => 'tony.brown@email.com',
                'password' => 'pa$$w0rd',
            ],
            UserRole::Admin,
        );

        $response = $this->deleteJson(
            route('api.users.destroy', ['id' => $admin->id]),
        );

        $response->assertOk();
    }

    /**
     * Test delete currently logged-in user.
     *
     * @return void
     */
    public function testDeleteCurrentUser()
    {
        $response = $this->deleteJson(
            route('api.users.destroy', ['id' => $this->user->id]),
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'message',
        ]);

        $this->assertSoftDeleted($this->user);
    }

    /**
     * Test restore soft-deleted user.
     *
     * @return void
     */
    public function testRestoreSoftDeletedUser()
    {
        $manager = $this->register(
            [
                'name' => 'Tony Brown',
                'email' => 'tony.brown@email.com',
                'password' => 'pa$$w0rd',
            ],
            UserRole::Manager,
        );
        $manager->delete();

        $response = $this->postJson(
            route('api.users.restore', ['id' => $manager->id]),
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'message',
        ]);

        $this->assertEmpty($manager->fresh()->deleted_at);
    }
}
