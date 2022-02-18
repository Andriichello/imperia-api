<?php

namespace Tests\Http\Controllers\Model;

use App\Enums\BanquetState;
use App\Enums\UserRole;
use App\Models\Banquet;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\ProductOrderField;
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
     * @var Space
     */
    protected Space $space;

    /**
     * @var Service
     */
    protected Service $service;

    /**
     * @var Product
     */
    protected Product $product;

    /**
     * @var Ticket
     */
    protected Ticket $ticket;

    /**
     * @var array
     */
    protected array $attributes;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->space = Space::factory()->create();
        $this->ticket = Ticket::factory()->create();
        $this->service = Service::factory()->create();
        $this->product = Product::factory()->create();

        $this->banquet = Banquet::factory()
            ->withCustomer(Customer::factory()->create())
            ->withCreator($this->user)
            ->withState(BanquetState::Draft)
            ->create();

        $this->attributes = [
            'spaces' => [
                [
                    'space_id' => $this->space->id,
                    'start_at' => $this->banquet->start_at,
                    'end_at' => $this->banquet->end_at,
                ]
            ],
            'tickets' => [
                [
                    'ticket_id' => $this->ticket->id,
                    'amount' => 5,
                ]
            ],
            'services' => [
                [
                    'service_id' => $this->service->id,
                    'amount' => 2,
                    'duration' => 90,
                ]
            ],
            'products' => [
                [
                    'product_id' => $this->product->id,
                    'amount' => 2,
                ]
            ],
        ];
    }

    /**
     * Test store order.
     *
     * @return void
     */
    public function testStore()
    {
        $this->attributes['banquet_id'] = $this->banquet->id;

        $response = $this->postJson(route('api.orders.store', $this->attributes));
        $response->assertCreated();
        $response->assertJsonStructure([
            'data',
            'message'
        ]);
        $this->assertDatabaseHas(Order::class, Arr::only($this->attributes, 'banquet_id'));

        $response = $this->postJson(route('api.orders.store'), $this->attributes);
        $response->assertUnprocessable();
    }

    /**
     * Test update order.
     *
     * @return void
     */
    public function testUpdate()
    {
        $this->attributes['banquet_id'] = $this->banquet->id;

        $response = $this->postJson(route('api.orders.store'), $this->attributes);
        $response->assertCreated();
        $response->assertJsonStructure([
            'data',
            'message'
        ]);

        /** @var Order $order */
        $order = Order::query()->findOrFail(data_get($response, 'data.id'));
        $this->assertEquals($this->banquet->id, $order->banquet_id);
        $this->assertCount(1, $order->spaces);
        $this->assertCount(1, $order->tickets);
        $this->assertCount(1, $order->products);
        $this->assertCount(1, $order->services);

        $response = $this->patchJson(
            route('api.orders.update', ['id' => data_get($response, 'data.id')]),
            [
                'spaces' => [],
                'tickets' => [],
                'services' => [],
                'products' => [
                    [
                        'product_id' => $this->product->id,
                        'amount' => 10,
                    ]
                ]
            ]
        );
        $response->assertOk();

        /** @var Order $order */
        $order = Order::query()->findOrFail(data_get($response, 'data.id'));
        $this->assertEquals($this->banquet->id, $order->banquet_id);
        $this->assertCount(0, $order->spaces);
        $this->assertCount(0, $order->tickets);
        $this->assertCount(1, $order->products);
        $this->assertCount(0, $order->services);

        /** @var ProductOrderField $productOrderField */
        $productOrderField = $order->products->first();
        $this->assertEquals(10, $productOrderField->amount);

        $this->banquet->update(['state' => BanquetState::Completed]);

        $response = $this->patchJson(
            route('api.orders.update', ['id' => data_get($response, 'data.id')]),
            [
                'tickets' => [
                    [
                        'ticket_id' => $this->ticket->id,
                        'amount' => 10,
                    ]
                ],
            ]
        );
        $response->assertStatus(403);
    }
}
