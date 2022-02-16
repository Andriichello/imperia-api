<?php

namespace Tests\Http\Controllers\Model;

use App\Enums\BanquetState;
use App\Enums\UserRole;
use App\Models\Banquet;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Product;
use App\Models\Service;
use App\Models\Space;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Tests\RegisteringTestCase;

/**
 * Class OrderControllerTest.
 */
class OrderControllerTest extends RegisteringTestCase
{
    use MakesHttpRequests;

    /**
     * If true, then user should be logged-in on set up.
     *
     * @var bool
     */
    protected bool $shouldLogin = true;

    /**
     * Target banquet.
     *
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

        $this->banquet = Banquet::factory()
            ->withCustomer(Customer::factory()->create())
            ->withCreator($this->user)
            ->withState(BanquetState::Draft)
            ->create();
    }

    /**
     * Test store order.
     *
     * @return void
     */
    public function testStore()
    {
        /** @var Space $space */
        $space = Space::factory()->create();
        /** @var Ticket $ticket */
        $ticket = Ticket::factory()->create();
        /** @var Service $service */
        $service = Service::factory()->create();
        /** @var Product $product */
        $product = Product::factory()->create();

        $response = $this->postJson(
            route('api.orders.store', $attributes = [
                'banquet_id' => $this->banquet->id,
                'spaces' => [
                    [
                        'space_id' => $space->id,
                        'start_at' => $this->banquet->start_at,
                        'end_at' => $this->banquet->end_at,
                    ]
                ],
                'tickets' => [
                    [
                        'ticket_id' => $ticket->id,
                        'amount' => 5,
                    ]
                ],
                'services' => [
                    [
                        'service_id' => $service->id,
                        'amount' => 2,
                        'duration' => 90,
                    ]
                ],
                'product' => [
                    [
                        'product_id' => $product->id,
                        'amount' => 2,
                        'duration' => 90,
                    ]
                ]
            ])
        );
        $response->assertCreated();
        $response->assertJsonStructure([
            'data',
            'message'
        ]);
        $this->assertDatabaseHas(Order::class, Arr::only($attributes, 'banquet_id'));

        $response = $this->postJson(route('api.orders.store', $attributes));
        $response->assertUnprocessable();
    }
}
