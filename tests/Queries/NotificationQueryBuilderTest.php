<?php

namespace Tests\Queries;

use App\Models\Notification;
use App\Models\User;
use Tests\TestCase;

/**
 * Class NotificationQueryBuilderTest.
 */
class NotificationQueryBuilderTest extends TestCase
{
    /**
     * @var User
     */
    protected User $user;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function testWasSent()
    {
        $notification = Notification::factory()
            ->withReceiver($this->user)
            ->withSentAt(null)
            ->create();

        $this->assertTrue(Notification::query()->wasNotSent()->exists());
        $this->assertFalse(Notification::query()->wasSent()->exists());

        $notification->sent_at = now();
        $notification->save();

        $this->assertTrue(Notification::query()->wasSent()->exists());
        $this->assertFalse(Notification::query()->wasNotSent()->exists());
    }

    public function testWasSeen()
    {
        $notification = Notification::factory()
            ->withReceiver($this->user)
            ->withSeenAt(null)
            ->withSentAt(now())
            ->create();

        $this->assertTrue(Notification::query()->wasNotSeen()->exists());
        $this->assertFalse(Notification::query()->wasSeen()->exists());

        $notification->seen_at = now();
        $notification->save();

        $this->assertTrue(Notification::query()->wasSeen()->exists());
        $this->assertFalse(Notification::query()->wasNotSeen()->exists());
    }

    public function testToUsers()
    {
        $other = User::factory()->create();

        $notification = Notification::factory()
            ->withReceiver($this->user)
            ->withSeenAt(null)
            ->withSentAt(now())
            ->create();

        $this->assertTrue(Notification::query()->toUsers($this->user)->exists());
        $this->assertFalse(Notification::query()->toUsers($other)->exists());

        $notification->receiver_id = $other->id;
        $notification->save();

        $this->assertTrue(Notification::query()->toUsers($other)->exists());
        $this->assertFalse(Notification::query()->toUsers($this->user)->exists());
    }

    public function testFromUsers()
    {
        $sender = User::factory()->create();

        $notification = Notification::factory()
            ->withReceiver($this->user)
            ->withSender($sender)
            ->withSeenAt(null)
            ->withSentAt(now())
            ->create();

        $this->assertTrue(Notification::query()->fromUsers($sender)->exists());
        $this->assertFalse(Notification::query()->fromUsers($this->user)->exists());

        $notification->receiver_id = $sender->id;
        $notification->sender_id = $this->user->id;
        $notification->save();

        $this->assertTrue(Notification::query()->fromUsers($this->user)->exists());
        $this->assertFalse(Notification::query()->fromUsers($sender)->exists());
    }

    public function testFromSystem()
    {
        $notification = Notification::factory()
            ->withReceiver($this->user)
            ->create();

        $this->assertTrue(Notification::query()->fromSystem()->exists());
        $this->assertFalse(Notification::query()->notFromSystem()->exists());

        $notification->sender_id = $this->user->id;
        $notification->save();

        $this->assertTrue(Notification::query()->notFromSystem()->exists());
        $this->assertFalse(Notification::query()->fromSystem()->exists());
    }

    public function testSendNowAndLater()
    {
        // already sent
        Notification::factory()
            ->withSendAt(now()->subHour())
            ->withSentAt(now()->subHour())
            ->withReceiver($this->user)
            ->create();

        $this->assertFalse(Notification::query()->sendNow()->exists());
        $this->assertFalse(Notification::query()->sendLater()->exists());

        // should be sent later
        Notification::factory()
            ->withSendAt(now()->addHour())
            ->withReceiver($this->user)
            ->create();

        $this->assertFalse(Notification::query()->sendNow()->exists());
        $this->assertTrue(Notification::query()->sendLater()->exists());

        // should be already sent
        Notification::factory()
            ->withSendAt(now()->subHour())
            ->withReceiver($this->user)
            ->create();

        $this->assertTrue(Notification::query()->sendNow()->exists());
        $this->assertTrue(Notification::query()->sendLater()->exists());
    }
}
