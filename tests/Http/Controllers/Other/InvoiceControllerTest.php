<?php

namespace Tests\Http\Controllers\Other;

use App\Helpers\Objects\Signature;
use App\Helpers\SignatureHelper;
use App\Models\Banquet;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\SpaceOrderField;
use App\Models\Space;
use Carbon\Carbon;
use Tests\RegisteringTestCase;

/**
 * Class InvoiceControllerTest.
 */
class InvoiceControllerTest extends RegisteringTestCase
{
    /**
     * If true, then user should be logged-in on set up.
     *
     * @var bool
     */
    protected bool $shouldLogin = false;

    /**
     * If true, then user should be registered on set up.
     *
     * @var bool
     */
    protected bool $shouldRegister = true;

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

        $this->banquet = Banquet::factory()
            ->withCustomer(Customer::factory()->create())
            ->withCreator($this->user)
            ->create();

        Order::factory()
            ->withBanquet($this->banquet)
            ->create();

        SpaceOrderField::factory()
            ->withOrder($this->banquet->order)
            ->withSpace(Space::factory()->create())
            ->create();
    }

    /**
     * Test authentication without signature.
     *
     * @return void
     */
    public function testWithoutSignature()
    {
        $id = $this->banquet->id;
        $url = route('api.banquets.invoice', compact('id'));

        $this->get($url)
            ->assertUnauthorized();
    }

    /**
     * Test authentication with signature.
     *
     * @return void
     */
    public function testWithSignature()
    {
        $id = $this->banquet->id;
        $url = route('api.banquets.invoice', compact('id'));

        /** @var SignatureHelper $helper */
        $helper = app(SignatureHelper::class);

        $signature = (new Signature())
            ->setExpiration(Carbon::now()->addHour())
            ->setUserId($this->user->id);

        $signature = $helper->encrypt($signature);
        $this->get($url . '?' . http_build_query(compact('signature')))
            ->assertOk();
    }

    /**
     * Test authentication without bearer token.
     *
     * @return void
     */
    public function testWithoutBearer()
    {
        $id = $this->banquet->id;
        $url = route('api.banquets.invoice', compact('id'));

        $this->get($url)
            ->assertUnauthorized();
    }

    /**
     * Test authentication with bearer token.
     *
     * @return void
     */
    public function testWithBearer()
    {
        $id = $this->banquet->id;
        $url = route('api.banquets.invoice', compact('id'));

        $this->actAs($this->user);
        $this->get($url)
            ->assertOk();
    }
}
