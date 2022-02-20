<?php

namespace Tests\Http\Controllers\Model;

use App\Enums\FamilyRelation;
use App\Models\Customer;
use App\Models\FamilyMember;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Tests\RegisteringTestCase;

/**
 * Class CustomerControllerTest.
 */
class CustomerControllerTest extends RegisteringTestCase
{
    use MakesHttpRequests;

    /**
     * If true, then user should be logged-in on set up.
     *
     * @var bool
     */
    protected bool $shouldLogin = true;

    /**
     * Test customer.
     *
     * @var Customer
     */
    protected Customer $customer;

    /**
     * Family members of the test customer.
     *
     * @var Collection
     */
    protected Collection $familyMembers;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::factory()
            ->create();

        $this->familyMembers = FamilyMember::factory()
            ->withRelative($this->customer, FamilyRelation::Child())
            ->count(2)
            ->create();
    }

    /**
     * Test index customers.
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->getJson(route('api.customers.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'meta'
        ]);

        $this->assertCount(1, $response['data']);
    }

    /**
     * Test index customers with family members relation included.
     *
     * @return void
     */
    public function testIndexWithFamilyMembersIncluded()
    {
        $response = $this->getJson(route('api.customers.index', ['include' => 'family_members']));

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'meta'
        ]);

        $this->assertCount(1, $response['data']);
        $this->assertCount(2, $response['data'][0]['family_members']);
    }

    /**
     * Test show customer by id.
     *
     * @return void
     */
    public function testShow()
    {
        $response = $this->getJson(
            route('api.customers.show', ['id' => $this->customer->id])
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'message'
        ]);

        $this->assertEquals($this->customer->id, data_get($response, 'data.id'));
    }

    /**
     * Test show customer by id with family members relation included.
     *
     * @return void
     */
    public function testShowWithFamilyMembersIncluded()
    {
        $response = $this->getJson(
            route('api.customers.show', ['id' => $this->customer->id, 'include' => 'family_members'])
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'message'
        ]);

        $this->assertEquals($this->customer->id, data_get($response, 'data.id'));
        $this->assertCount($this->familyMembers->count(), data_get($response, 'data.family_members'));
    }

    /**
     * Test store customer.
     *
     * @return void
     */
    public function testStore()
    {
        $response = $this->postJson(
            route('api.customers.store'),
            $attributes = [
                'name' => 'John',
                'surname' => 'John Doe',
                'email' => 'john.doe@email.com',
                'phone' => '+380507777777',
                'birthdate' => '1995-10-15',
                'comments' => [
                    ['text' => 'Comment one...'],
                    ['text' => 'Comment two...'],
                    ['text' => 'Comment three...'],
                ]
            ],
        );

        $response->assertCreated();
        $response->assertJsonStructure([
            'data',
            'message'
        ]);

        $this->assertDatabaseHas(Customer::class, Arr::only($attributes, ['name', 'surname', 'email']));
        /** @var Customer $customer */
        $customer = Customer::query()->findOrFail(data_get($response, 'data.id'));
        $this->assertCount(3, $customer->comments);
    }

    /**
     * Test update customer's name and email.
     *
     * @return void
     */
    public function testUpdateNameAndEmail()
    {
        $attributes = [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@email.com',
        ];

        $response = $this->patchJson(
            route('api.customers.update', ['id' => $this->customer->id]),
            $attributes,
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'message'
        ]);

        $this->assertDatabaseHas(Customer::class, $attributes);
    }

    /**
     * Test delete customer.
     *
     * @return void
     */
    public function testDeleteCustomer()
    {
        $response = $this->deleteJson(
            route('api.customers.destroy', ['id' => $this->customer->id]),
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'message',
        ]);

        $this->assertNotEmpty($this->customer->fresh()->deleted_at);
    }

    /**
     * Test restore soft-deleted customer.
     *
     * @return void
     */
    public function testRestoreSoftDeletedCustomer()
    {
        $this->customer->delete();
        $response = $this->postJson(
            route('api.customers.restore', ['id' => $this->customer->id]),
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'message',
        ]);

        $this->assertEmpty($this->customer->fresh()->deleted_at);
    }
}
