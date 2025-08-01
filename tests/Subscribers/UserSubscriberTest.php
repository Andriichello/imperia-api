<?php

namespace Tests\Subscribers;

use App\Enums\UserRole;
use App\Events\User\Registered;
use App\Models\User;
use App\Subscribers\UserSubscriber;
use Exception;
use Tests\TestCase;

/**
 * Class UserSubscriberTest.
 */
class UserSubscriberTest extends TestCase
{
    /**
     * Test User's Register event subscription.
     *
     * @throws Exception
     */
    public function testRegistered()
    {
        $admin = User::factory()->withRole(UserRole::Admin())->create();
        event(new Registered($admin));

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isManager());
        $this->assertFalse($admin->isCustomer());
        $this->assertEmpty($admin->customer);

        $manager = User::factory()->withRole(UserRole::Manager())->create();
        event(new Registered($manager));

        $this->assertFalse($manager->isAdmin());
        $this->assertTrue($manager->isManager());
        $this->assertFalse($manager->isCustomer());
        $this->assertEmpty($manager->customer);

        $user = User::factory()->create();
        event(new Registered($user));

        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isManager());
        $this->assertFalse($user->isCustomer());
        $this->assertEmpty($admin->customer);

        $customer = User::factory()->withRole(UserRole::Customer())->create();
        event(new Registered($customer, $phone = '+380509876543'));

        $this->assertFalse($customer->isAdmin());
        $this->assertFalse($customer->isManager());
        $this->assertTrue($customer->isCustomer());
        $this->assertNotEmpty($customer->customer);
        $this->assertEquals($phone, $customer->customer->phone);

        $subscriber = $this->createPartialMock(UserSubscriber::class, ['registered']);
        $subscriber->expects($this->once())
            ->method('registered');

        $this->app->instance(UserSubscriber::class, $subscriber);

        $user = User::factory()->create();
        event(new Registered($user));
    }
}
