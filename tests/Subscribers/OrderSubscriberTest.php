<?php

namespace Tests\Subscribers;

use App\Models\Banquet;
use App\Models\Orders\Order;
use App\Subscribers\OrderSubscriber;
use Exception;
use Tests\TestCase;

/**
 * Class OrderSubscriber.
 */
class OrderSubscriberTest extends TestCase
{
    protected Banquet $banquet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->banquet = Banquet::factory()->create();
    }

    /**
     * Test Order's eloquent 'saved' event subscription.
     *
     * @throws Exception
     */
    public function testDeleted()
    {
        $this->banquet->totals = ['key' => 'value'];
        $this->banquet->save();

        $this->assertNotEmpty($this->banquet->fresh()->totals);

        $order = Order::factory()->withBanquet($this->banquet)->create();
        $order->forceDelete();

        $this->assertEmpty($this->banquet->fresh()->totals);

        $subscriber = $this->createPartialMock(OrderSubscriber::class, ['deleted']);
        $subscriber->expects($this->atLeast(2))
            ->method('deleted');

        $this->app->instance(OrderSubscriber::class, $subscriber);

        $order = Order::factory()->withBanquet($this->banquet)->create();
        $order->forceDelete();

        $order = Order::factory()->withBanquet($this->banquet)->create();
        $order->delete();
    }
}
