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
 * Class FamilyMemberControllerTest.
 */
class FamilyMemberControllerTest extends RegisteringTestCase
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
     * Family member of the test customer.
     *
     * @var FamilyMember
     */
    protected FamilyMember $familyMember;

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

        $this->familyMember = FamilyMember::factory()
            ->withRelative($this->customer, FamilyRelation::Child())
            ->create(['name' => 'Tommy']);
    }

    /**
     * Test index family members.
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->getJson(route('api.family-members.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'meta'
        ]);

        $this->assertCount(1, $response['data']);
    }

    /**
     * Test index family members with relative relation included.
     *
     * @return void
     */
    public function testIndexWithRelativeIncluded()
    {
        $response = $this->getJson(route('api.family-members.index', ['include' => 'relative']));

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'meta'
        ]);

        $this->assertCount(1, data_get($response, 'data'));
        $this->assertEquals($this->customer->id, data_get($response, 'data.0.relative.id'));
    }

    /**
     * Test show family member by id.
     *
     * @return void
     */
    public function testShow()
    {
        $response = $this->getJson(
            route('api.family-members.show', ['id' => $this->familyMember->id])
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'message'
        ]);

        $this->assertEquals($this->familyMember->id, data_get($response, 'data.id'));
    }

    /**
     * Test show family member by id with relative relation included.
     *
     * @return void
     */
    public function testShowWithRelativeIncluded()
    {
        $response = $this->getJson(
            route('api.family-members.show', ['id' => $this->familyMember->id, 'include' => 'relative'])
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'message'
        ]);

        $this->assertEquals($this->familyMember->id, data_get($response, 'data.id'));
        $this->assertEquals($this->customer->id, data_get($response, 'data.relative.id'));
    }

    /**
     * Test store family member.
     *
     * @return void
     */
    public function testStore()
    {
        $response = $this->postJson(
            route('api.family-members.store', $attributes = [
                'name' => 'Jilly',
                'birthdate' => '2015-10-15',
                'relation' => FamilyRelation::Child,
                'relative_id' => $this->customer->id,
            ])
        );

        $response->assertCreated();
        $response->assertJsonStructure([
            'data',
            'message'
        ]);

        $this->assertDatabaseHas(FamilyMember::class, Arr::only($attributes, 'name'));
    }

    /**
     * Test update family member's name and birthdate.
     *
     * @return void
     */
    public function testUpdateNameAndBirthdate()
    {
        $attributes = [
            'name' => 'Kelly',
            'birthdate' => '2000-01-22',
        ];

        $response = $this->patchJson(
            route('api.family-members.update', ['id' => $this->familyMember->id]),
            $attributes,
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'message'
        ]);

        $this->assertDatabaseHas(FamilyMember::class, Arr::only($attributes, 'name'));
    }

    /**
     * Test delete family member.
     *
     * @return void
     */
    public function testDeleteFamilyMember()
    {
        $response = $this->deleteJson(
            route('api.family-members.destroy', ['id' => $this->familyMember->id]),
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'message',
        ]);

        $this->assertDatabaseMissing(FamilyMember::class, ['id' => $this->familyMember->id]);
    }
}
