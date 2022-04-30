<?php

namespace Tests\Jobs;

use App\Jobs\StoreTotals;
use App\Models\Banquet;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Class StoreTotalsTest.
 */
class StoreTotalsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    public function testHandle()
    {
        $banquet = Banquet::factory()->create();
        $this->assertEmpty($banquet->getFromJson('metadata', 'totals'));

        $order = Order::factory()->withBanquet($banquet)->create();
        $job = new StoreTotals($order);
        $job->handle();

        $banquet = $banquet->fresh();
        $totals = $banquet->getFromJson('metadata', 'totals');

        $this->assertNotEmpty($totals);
        $this->assertArrayHasKey('all', $totals);
        $this->assertArrayHasKey('spaces', $totals);
        $this->assertArrayHasKey('tickets', $totals);
        $this->assertArrayHasKey('services', $totals);
        $this->assertArrayHasKey('products', $totals);
    }
}
