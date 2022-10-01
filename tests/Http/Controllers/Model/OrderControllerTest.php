<?php

namespace Tests\Http\Controllers\Model;

use App\Enums\BanquetState;
use App\Models\Banquet;
use App\Models\Customer;
use App\Models\Morphs\Comment;
use App\Models\Morphs\Discount;
use App\Models\Orders\Order;
use App\Models\Orders\ProductOrderField;
use App\Models\Product;
use App\Models\Service;
use App\Models\Space;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Support\Arr;
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
     * @var Discount
     */
    protected Discount $discount;


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
        $this->discount = Discount::factory()->create();

        $this->banquet = Banquet::factory()
            ->withCustomer($customer = Customer::factory()->create())
            ->withCreator($this->user)
            ->withState(BanquetState::Draft)
            ->create();

        $this->banquet->orders()->detach($this->banquet->order_id);

        $customer->user_id = $this->user->id;
        $customer->save();

        $this->attributes = [
            'spaces' => [
                [
                    'space_id' => $this->space->id,
                    'start_at' => $this->banquet->start_at,
                    'end_at' => $this->banquet->end_at,
                    'comments' => [
                        ['text' => 'Comment for space.'],
                    ],
                ]
            ],
            'tickets' => [
                [
                    'ticket_id' => $this->ticket->id,
                    'amount' => 5,
                    'comments' => [
                        ['text' => 'Comment for ticket.'],
                    ],
                ]
            ],
            'services' => [
                [
                    'service_id' => $this->service->id,
                    'amount' => 2,
                    'duration' => 90,
                    'comments' => [
                        ['text' => 'Comment for service.'],
                    ],
                ]
            ],
            'products' => [
                [
                    'product_id' => $this->product->id,
                    'amount' => 2,
                    'comments' => [
                        ['text' => 'Comment for product.'],
                    ],
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
        $this->attributes['comments'] = [
            ['text' => 'Comment one...'],
            ['text' => 'Comment two...'],
            ['text' => 'Comment three...'],
        ];
        $this->attributes['discounts'] = [
            ['discount_id' => $this->discount->id],
        ];

        $response = $this->postJson(route('api.orders.store'), $this->attributes);
        $response->assertCreated();
        $response->assertJsonStructure([
            'data',
            'message'
        ]);

        $this->assertEquals($this->banquet->fresh()->order_id, data_get($response->json('data'), 'id'));

        /** @var Order $order */
        $order = Order::query()->findOrFail(data_get($response, 'data.id'));
        $this->assertCount(3, $order->comments);
        $this->assertCount(1, $order->discounts);

        $this->assertCount(1, $order->spaces->first()->comments);
        $this->assertCount(1, $order->tickets->first()->comments);
        $this->assertCount(1, $order->services->first()->comments);
        $this->assertCount(1, $order->products->first()->comments);

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
        $this->attributes['comments'] = [
            ['text' => 'Comment one...'],
            ['text' => 'Comment two...'],
            ['text' => 'Comment three...'],
        ];
        $this->attributes['discounts'] = [
            ['discount_id' => $this->discount->id],
        ];

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
        $this->assertCount(3, $order->comments);
        $this->assertCount(1, $order->discounts);

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
                ],
                'comments' => [
                    ['text' => 'Updated comment...']
                ],
                'discounts' => [],
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
        $this->assertCount(1, $order->comments);
        $this->assertCount(0, $order->discounts);

        /** @var Comment $comment */
        $comment = $order->comments->first();
        $this->assertEquals('Updated comment...', $comment->text);

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

    /**
     * Test delete and restore order.
     *
     * @return void
     */
    public function testDeleteAndRestore()
    {
        $attributes = [
            'banquet_id' => $this->banquet->id,
            'spaces' => [
                [
                    'space_id' => $this->space->id,
                ]
            ],
            'tickets' => [
                [
                    'ticket_id' => $this->ticket->id,
                    'amount' => 3,
                ]
            ],
            'products' => [
                [
                    'product_id' => $this->product->id,
                    'amount' => 2,
                ]
            ],
            'services' => [
                [
                    'service_id' => $this->service->id,
                    'amount' => 2,
                    'duration' => 45,
                ]
            ]
        ];

        $response = $this->postJson(route('api.orders.store'), $attributes);
        $response->assertCreated();

        /** @var Order $order */
        $order = Order::query()->findOrFail(data_get($response, 'data.id'));

        $response = $this->deleteJson(route('api.orders.destroy', ['id' => $order->id]));
        $response->assertOk();

        $this->assertNotNull($order->fresh()->deleted_at);

        $this->assertEquals(0, $order->spaces()->count());
        $this->assertEquals(1, $order->spaces()->withTrashed()->count()); // @phpstan-ignore-line
        $this->assertEquals(0, $order->tickets()->count());
        $this->assertEquals(1, $order->tickets()->withTrashed()->count()); // @phpstan-ignore-line
        $this->assertEquals(0, $order->products()->count());
        $this->assertEquals(1, $order->products()->withTrashed()->count()); // @phpstan-ignore-line
        $this->assertEquals(0, $order->services()->count());
        $this->assertEquals(1, $order->services()->withTrashed()->count()); // @phpstan-ignore-line

        $response = $this->postJson(route('api.orders.restore', ['id' => $order->id]));
        $response->assertOk();

        $this->assertNull($order->fresh()->deleted_at);

        $this->assertEquals(1, $order->spaces()->count());
        $this->assertEquals(1, $order->tickets()->count());
        $this->assertEquals(1, $order->products()->count());
        $this->assertEquals(1, $order->services()->count());
    }
}
