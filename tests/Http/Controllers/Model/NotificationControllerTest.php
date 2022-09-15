<?php

namespace Tests\Http\Controllers\Model;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Tests\RegisteringTestCase;

/**
 * Class NotificationControllerTest.
 */
class NotificationControllerTest extends RegisteringTestCase
{
    use MakesHttpRequests;

    /**
     * If true, then user should be logged-in on set up.
     *
     * @var bool
     */
    protected bool $shouldLogin = true;

    /**
     * User, which will be receiving the notifications.
     *
     * @var User
     */
    protected User $receiver;

    /**
     * Notification from logged-in user to receiver.
     *
     * @var Notification
     */
    protected Notification $notification;


    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->receiver = User::factory()->create();

        $this->notification = Notification::factory()
            ->withReceiver($this->receiver)
            ->withSender($this->user)
            ->withSentAt(null)
            ->create();
    }

    /**
     * Test index notifications.
     *
     * @return void
     */
    public function testIndex()
    {
        $this->actAs($this->user);
        $response = $this->getJson(route('api.notifications.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'meta'
        ]);

        $this->assertCount(1, $response['data']);

        $this->actAs($this->receiver);
        $response = $this->getJson(route('api.notifications.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'meta'
        ]);

        $this->assertCount(0, $response['data']);

        $this->notification->sent_at = now();
        $this->notification->save();

        $response = $this->getJson(route('api.notifications.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'meta'
        ]);

        $this->assertCount(1, $response['data']);
    }

    /**
     * Test show notification by id.
     *
     * @return void
     */
    public function testShow()
    {
        $this->actAs($this->user);
        $response = $this->getJson(
            route('api.notifications.show', ['id' => $this->notification->id])
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'message'
        ]);

        $this->assertEmpty(data_get($response, 'data.seen_at'));
        $this->assertEquals($this->notification->id, data_get($response, 'data.id'));

        $this->actAs($this->receiver);
        $response = $this->getJson(
            route('api.notifications.show', ['id' => $this->notification->id])
        );

        $response->assertForbidden();

        $this->notification->sent_at = now();
        $this->notification->save();

        $this->actAs($this->receiver);
        $response = $this->getJson(
            route('api.notifications.show', ['id' => $this->notification->id])
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'message'
        ]);

        $this->assertNotEmpty(data_get($response, 'data.seen_at'));
        $this->assertEquals($this->notification->id, data_get($response, 'data.id'));
    }
}
