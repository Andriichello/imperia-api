<?php

namespace Tests\Queries;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Class UserQueryBuilderTest.
 */
class UserQueryBuilderTest extends TestCase
{
    /**
     * Users, which have no roles.
     *
     * @var Collection
     */
    protected Collection $users;

    /**
     * Users, which have Admin role.
     *
     * @var Collection
     */
    protected Collection $admins;

    /**
     * Users, which have Manager role.
     *
     * @var Collection
     */
    protected Collection $managers;

    /**
     * Users, which have Customer role.
     *
     * @var Collection
     */
    protected Collection $customers;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->users = User::factory()
            ->count(3)->create();

        $this->admins = User::factory()
            ->withRole(UserRole::Admin())
            ->count(3)
            ->create();

        $this->managers = User::factory()
            ->withRole(UserRole::Manager())
            ->count(3)
            ->create();

        $this->customers = User::factory()
            ->withRole(UserRole::Customer())
            ->count(3)
            ->create();
    }

    public function testOnlyRoles()
    {
        $admins = User::query()
            ->onlyRoles(UserRole::Admin)
            ->get();

        $this->assertCount($this->admins->count(), $admins);
        foreach ($this->admins as $admin) {
            $this->assertTrue($admins->contains($admin));
        }

        $managers = User::query()
            ->onlyRoles(UserRole::Manager)
            ->get();

        $this->assertCount($this->managers->count(), $managers);
        foreach ($this->managers as $manager) {
            $this->assertTrue($managers->contains($manager));
        }

        $customers = User::query()
            ->onlyRoles(UserRole::Customer)
            ->get();

        $this->assertCount($this->customers->count(), $customers);
        foreach ($this->customers as $customer) {
            $this->assertTrue($customers->contains($customer));
        }
    }

    public function testExceptRoles()
    {
        $users = User::query()
            ->exceptRoles(UserRole::Admin, UserRole::Manager, UserRole::Customer)
            ->get();

        $this->assertCount($this->users->count(), $users);
        foreach ($this->users as $user) {
            $this->assertTrue($users->contains($user));
        }

        $noAdmins = User::query()
            ->exceptRoles(UserRole::Admin)
            ->get();

        $collection = collect($this->users)
            ->merge($this->customers)
            ->merge($this->managers);

        $this->assertCount($collection->count(), $noAdmins);
        foreach ($collection as $user) {
            $this->assertTrue($noAdmins->contains($user));
        }

        $noManagers = User::query()
            ->exceptRoles(UserRole::Manager)
            ->get();

        $collection = collect($this->users)
            ->merge($this->customers)
            ->merge($this->admins);

        $this->assertCount($collection->count(), $noManagers);
        foreach ($collection as $user) {
            $this->assertTrue($noManagers->contains($user));
        }

        $noCustomers = User::query()
            ->exceptRoles(UserRole::Customer)
            ->get();

        $collection = collect($this->users)
            ->merge($this->managers)
            ->merge($this->admins);

        $this->assertCount($collection->count(), $noCustomers);
        foreach ($collection as $user) {
            $this->assertTrue($noCustomers->contains($user));
        }
    }
}
