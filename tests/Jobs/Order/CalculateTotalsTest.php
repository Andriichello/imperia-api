<?php

namespace Tests\Jobs\Order;

use App\Jobs\Order\CalculateTotals;
use App\Models\Banquet;
use App\Models\Orders\BanquetOrder;
use App\Models\Orders\Order;
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
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        BanquetOrder::unsetEventDispatcher();

        $this->banquet = Banquet::factory()
            ->withOrder(Order::factory()->create())
            ->create();
    }

    /**
     * @return void
     */
    public function testHandle()
    {
        $this->assertEmpty($this->banquet->totals);
        $this->assertEmpty($this->banquet->order->totals);

        $job = new CalculateTotals($this->banquet->order);
        $job->handle();

        $this->assertNotEmpty($this->banquet->fresh()->totals);
        $this->assertNotEmpty($this->banquet->order->fresh()->totals);
    }
}
