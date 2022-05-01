<?php

namespace Tests\Jobs\Order;

use App\Jobs\Order\CalculateTotals;
use App\Models\Banquet;
use App\Models\Orders\Order;
use App\Models\Orders\SpaceOrderField;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Class CalculateTotalsTest.
 */
class CalculateTotalsTest extends TestCase
{
    /**
     * @var Banquet
     */
    protected Banquet $banquet;

    /**
     * @var Order
     */
    protected Order $order;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->banquet = Banquet::factory()
            ->create();
        $this->order = Order::factory()
            ->withBanquet($this->banquet)
            ->create();
    }

    /**
     * @return void
     */
    public function testHandle()
    {
        $this->assertEmpty($this->banquet->totals);
        $this->assertEmpty($this->order->totals);

        $job = new CalculateTotals($this->order);
        $job->handle();

        $this->assertNotEmpty($this->banquet->fresh()->totals);
        $this->assertNotEmpty($this->order->fresh()->totals);
    }
}
