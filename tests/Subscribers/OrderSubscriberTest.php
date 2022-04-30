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
    public function testSaved()
    {
        $subscriber = $this->createPartialMock(OrderSubscriber::class, ['saved']);
        $subscriber->expects($this->atLeast(2))
            ->method('saved');

        $this->app->instance(OrderSubscriber::class, $subscriber);

        $order = Order::factory()
            ->withBanquet($this->banquet)
            ->create();

        $order->setToJson('metadata', 'test', 'value');
        $order->save();
    }
}
