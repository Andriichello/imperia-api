<?php

namespace Tests\Http\Controllers\Model;

use App\Enums\BanquetState;
use App\Enums\PaymentMethod;
use App\Models\Banquet;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Service;
use App\Models\Space;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Support\Collection;
use Tests\RegisteringTestCase;

/**
 * Class BanquetControllerTest.
 */
class BanquetControllerTest extends RegisteringTestCase
{
    use MakesHttpRequests;

    /**
     * If true, then user should be logged-in on set up.
     *
     * @var bool
     */
    protected bool $shouldLogin = true;

    /**
     * @var Collection
     */
    protected Collection $spaces;

    /**
     * @var Collection
     */
    protected Collection $services;

    /**
     * @var Collection
     */
    protected Collection $tickets;

    /**
     * @var Collection
     */
    protected Collection $products;

    /**
     * @var Collection
     */
    protected Collection $customers;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->spaces = Space::factory()
            ->count(3)
            ->create();
        $this->tickets = Ticket::factory()
            ->count(3)
            ->create();
        $this->services = Service::factory()
            ->count(3)
            ->create();
        $this->products = Product::factory()
            ->count(3)
            ->create();
        $this->customers = Customer::factory()
            ->count(3)
            ->create();

        foreach ($this->customers as $customer) {
            $user = User::factory()
                ->fromCustomer($customer)
                ->create();

            /** @var Customer $customer */
            $customer->user_id = $user->id;
            $customer->save();
        }
    }

    /**
     * Test store banquet.
     *
     * @return void
     */
    public function testStore()
    {
        $attributes = [
            'title' => 'Simple title',
            'description' => 'Simple description...',
            'advance_amount' => 0,
            'advance_amount_payment_method' => PaymentMethod::Card,
            'actual_total' => 100,
            'is_birthday_club' => true,
            'start_at' => Carbon::tomorrow()->setHour(8)->toDateTimeString(),
            'end_at' => Carbon::tomorrow()->setHour(23)->toDateTimeString(),
            'state' => BanquetState::New,
            'creator_id' => $this->user->id,
            'customer_id' => $this->customers->first()->id,
            'comments' => [
                ['text' => 'Comment one...'],
                ['text' => 'Comment two...'],
                ['text' => 'Comment three...'],
            ],
        ];

        $response = $this->postJson(route('api.banquets.store'), $attributes);
        $response->assertCreated();
        $response->assertJsonStructure([
            'data',
            'message'
        ]);

        /** @var Banquet $banquet */
        $banquet = Banquet::query()->findOrFail(data_get($response, 'data.id'));

        $this->assertCount(3, $banquet->comments);
        $this->assertEquals(100.0, $banquet->actual_total);
        $this->assertEquals(PaymentMethod::Card, $banquet->advance_amount_payment_method);
        $this->assertTrue($banquet->is_birthday_club);
    }

    /**
     * Test update banquet.
     *
     * @return void
     */
    public function testUpdate()
    {
        /** @var Banquet $banquet */
        $banquet = Banquet::factory()
            ->withCustomer($this->customers->first())
            ->withCreator($this->user)
            ->withState(BanquetState::New)
            ->create();

        $banquet->attachComments('Comment one...', 'Comment two...');
        $this->assertCount(2, $banquet->comments);

        $response = $this->patchJson(
            route('api.banquets.update', ['id' => $banquet->id]),
            [
                'advance_amount_payment_method' => PaymentMethod::Card,
                'actual_total' => 100,
                'is_birthday_club' => true,
                'comments' => [
                    ['text' => 'Updated comment...'],
                ],
            ]
        );
        $response->assertOk();

        $banquet = $banquet->fresh();

        $this->assertCount(1, $banquet->comments);
        $this->assertEquals(100.0, $banquet->actual_total);
        $this->assertEquals(PaymentMethod::Card, $banquet->advance_amount_payment_method);
        $this->assertTrue($banquet->is_birthday_club);

        // $banquet->update(['state' => BanquetState::Completed]);
        // $response = $this->patchJson(
        //     route('api.banquets.update', ['id' => $banquet->id]),
        //    [
        //         'title' => 'Random title.',
        //     ]
        // );
        // $response->assertForbidden();
    }

    /**
     * Test delete banquet.
     *
     * @return void
     */
    public function testDelete()
    {
        /** @var Banquet $banquet */
        $banquet = Banquet::factory()
            ->withCustomer($this->customers->first())
            ->withCreator($this->user)
            ->withState(BanquetState::New)
            ->create();

        $response = $this->deleteJson(
            route('api.banquets.destroy', ['id' => $banquet->id]),
            [
                'force' => false,
            ]
        );
        $response->assertOk();

        $this->assertNotNull($banquet->fresh()->deleted_at);

        $response = $this->deleteJson(
            route('api.banquets.destroy', ['id' => $banquet->id]),
            [
                'force' => true,
            ]
        );
        $response->assertOk();

        $this->assertDatabaseMissing(Banquet::class, ['id' => $banquet->id]);
    }

    /**
     * Test restore banquet.
     *
     * @return void
     */
    public function testRestore()
    {
        /** @var Banquet $banquet */
        $banquet = Banquet::factory()
            ->withCustomer($this->customers->first())
            ->withCreator($this->user)
            ->withState(BanquetState::New)
            ->create();

        $banquet->delete();

        $response = $this->postJson(
            route('api.banquets.restore', ['id' => $banquet->id])
        );
        $response->assertOk();

        $this->assertDatabaseHas(Banquet::class, ['id' => $banquet->id]);
    }
}
