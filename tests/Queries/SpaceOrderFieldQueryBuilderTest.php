<?php

namespace Tests\Queries;

use App\Models\Banquet;
use App\Models\Orders\Order;
use App\Models\Orders\SpaceOrderField;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Class SpaceOrderFieldQueryBuilderTest.
 */
class SpaceOrderFieldQueryBuilderTest extends TestCase
{
    /**
     * @var Banquet
     */
    protected Banquet $banquet;

    /**
     * @var Collection
     */
    protected Collection $spaces;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->banquet = Banquet::factory()
            ->withOrder(Order::factory()->create())
            ->create([
                'start_at' => Carbon::now()->subMinutes(30),
                'end_at' => Carbon::now()->addMinutes(30),
            ]);

        $this->spaces = Space::factory()
            ->count(3)
            ->create();
    }

    /**
     * @return void
     */
    public function testBetween()
    {
        $fields = [];
        foreach ($this->spaces as $space) {
            $fields[] = SpaceOrderField::factory()
                ->withOrder($this->banquet->order)
                ->withSpace($space)
                ->create();
        }

        // test exact interval match
        $query = SpaceOrderField::query()->between($this->banquet->start_at, $this->banquet->end_at);
        $this->assertEquals(count($fields), $query->count());

        // test interval start intersection
        $query = SpaceOrderField::query()->between(Carbon::now()->subMinutes(45), Carbon::now());
        $this->assertEquals(count($fields), $query->count());

        // test interval end intersection
        $query = SpaceOrderField::query()->between(Carbon::now(), Carbon::now()->addMinutes(45));
        $this->assertEquals(count($fields), $query->count());

        // test interval without intersection
        $query = SpaceOrderField::query()->between(Carbon::now()->subMinutes(55), Carbon::now()->subMinutes(45));
        $this->assertEquals(0, $query->count());

        // test interval without intersection
        $query = SpaceOrderField::query()->between(Carbon::now()->addMinutes(45), Carbon::now()->addMinutes(55));
        $this->assertEquals(0, $query->count());

        // test interval that contains reservation interval
        $query = SpaceOrderField::query()->between(Carbon::now()->subMinutes(45), Carbon::now()->addMinutes(45));
        $this->assertEquals(3, $query->count());
    }
}
