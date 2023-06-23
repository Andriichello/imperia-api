<?php

namespace Tests;

use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\UserRepository;
use Laravel\Sanctum\Sanctum;

/**
 * Class RegisteringTestCase.
 */
abstract class RegisteringTestCase extends TestCase
{
    /**
     * If true, then user should be logged-in on set up.
     *
     * @var bool
     */
    protected bool $shouldLogin = false;

    /**
     * If true, then user should be registered on set up.
     *
     * @var bool
     */
    protected bool $shouldRegister = false;

    /**
     * Currently registered|logged-in user.
     *
     * @var User
     */
    protected User $user;

    /**
     * User repository instance.
     *
     * @var UserRepository
     */
    protected UserRepository $userRepository;

    /**
     * Test user sign up form data.
     *
     * @var array
     */
    protected array $registerFormData = [
        'name' => 'Test',
        'surname' => 'Testers',
        'email' => 'test@email.com',
        'password' => 'pa$$w0rd',
        'metadata' => '{"isPreviewOnly": false}',
    ];

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->registerFormData = [
            'name' => 'Test',
            'surname' => 'Testers',
            'email' => 'test@email.com',
            'password' => 'pa$$w0rd',
            'metadata' => '{"isPreviewOnly": false}',
        ];

        $this->userRepository = new UserRepository();

        if ($this->shouldLogin) {
            $this->user = $this->login($this->registerFormData);
        }
        if (!$this->shouldLogin && $this->shouldRegister) {
            $this->user = $this->register($this->registerFormData);
        }
    }

    /**
     * Register a user with given attributes.
     *
     * @param array $attributes
     * @param string $role
     *
     * @return User
     */
    public function register(array $attributes, string $role = UserRole::Admin): User
    {
        $user = $this->userRepository->create($attributes, $role);
        $user->createToken('testing');

        return $user;
    }

    /**
     * Register a user with given attributes.
     *
     * @param array $attributes
     * @param string $role
     *
     * @return User
     */
    public function login(array $attributes, string $role = UserRole::Admin): User
    {
        /** @var User|null $user */
        $user = User::query()
            ->where('email', $this->registerFormData['email'])
            ->first();

        if ($user === null) {
            $user = $this->register($attributes, $role);
        }

        if (!$user->hasRole($role)) {
            $user->assignRole($role);
        }

        Sanctum::actingAs($user, ['*']);
        return $user;
    }

    /**
     * Set the environment so that given user makes a request.
     *
     * @param User $user
     *
     * @return void
     */
    public function actAs(User $user): void
    {
        Sanctum::actingAs($user, ['*']);
    }
}
