<?php

namespace Tests\Http\Controllers\Model;

use App\Enums\FamilyRelation;
use App\Enums\Weekday;
use App\Models\Customer;
use App\Models\FamilyMember;
use App\Models\Restaurant;
use App\Models\Schedule;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Support\Arr;
use Tests\RegisteringTestCase;

/**
 * Class ScheduleControllerTest.
 */
class ScheduleControllerTest extends RegisteringTestCase
{
    use MakesHttpRequests;

    /**
     * If true, then user should be logged-in on set up.
     *
     * @var bool
     */
    protected bool $shouldLogin = true;

    /**
     * Restaurant for tests.
     *
     * @var Restaurant
     */
    protected Restaurant $restaurant;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        foreach (Weekday::getValues() as $weekday) {
            Schedule::factory()
                ->withWeekday($weekday)
                ->create(['beg_hour' => 9, 'end_hour' => 22]);
        }

        $this->restaurant = Restaurant::factory()
            ->create();

        Schedule::factory()
            ->withWeekday(Weekday::Friday)
            ->withRestaurant($this->restaurant)
            ->create(['beg_hour' => 14, 'end_hour' => 3]);
    }

    /**
     * Test index schedules.
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->getJson(route('api.schedules.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'message',
        ]);

        $this->assertCount(count(Weekday::getValues()), $response['data']);

        foreach ($response->json('data') as $schedule) {
            $this->assertNull(data_get($schedule, 'restaurant_id'));
        }
    }

    /**
     * Test index schedules filtered down to a restaurant.
     *
     * @return void
     */
    public function testIndexForRestaurant()
    {
        $response = $this->getJson(
            route('api.schedules.index', [
                'filter' => [
                    'restaurant_id' => $this->restaurant->id,
                ]
            ])
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'message',
        ]);

        $this->assertCount(count(Weekday::getValues()), $response['data']);

        foreach ($response->json('data') as $schedule) {
            $id = data_get($schedule, 'restaurant_id');

            data_get($schedule, 'weekday') === Weekday::Friday
                ? $this->assertEquals($this->restaurant->id, $id)
                : $this->assertNull($id);
        }
    }

    /**
     * Test index schedules filtered down to a weekday.
     *
     * @return void
     */
    public function testIndexForWeekday()
    {
        $response = $this->getJson(
            route('api.schedules.index', [
                'filter' => [
                    'weekday' => Weekday::Friday,
                ]
            ])
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'message',
        ]);

        $this->assertCount(1, $response['data']);

        foreach ($response->json('data') as $schedule) {
            $this->assertEquals(Weekday::Friday, data_get($schedule, 'weekday'));
            $this->assertNull(data_get($schedule, 'restaurant_id'));
        }
    }

    /**
     * Test index schedules filtered down to a restaurant and a weekday.
     *
     * @return void
     */
    public function testIndexForRestaurantAndWeekday()
    {
        $response = $this->getJson(
            route('api.schedules.index', [
                'filter' => [
                    'weekday' => Weekday::Monday,
                    'restaurant_id' => $this->restaurant->id,
                ]
            ])
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'message',
        ]);

        $this->assertCount(1, $response['data']);

        foreach ($response->json('data') as $schedule) {
            $this->assertEquals(Weekday::Monday, data_get($schedule, 'weekday'));
            $this->assertNull(data_get($schedule, 'restaurant_id'));
        }

        $response = $this->getJson(
            route('api.schedules.index', [
                'filter' => [
                    'weekday' => Weekday::Friday,
                    'restaurant_id' => $this->restaurant->id,
                ]
            ])
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'message',
        ]);

        $this->assertCount(1, $response['data']);

        foreach ($response->json('data') as $schedule) {
            $this->assertEquals(Weekday::Friday, data_get($schedule, 'weekday'));
            $this->assertEquals($this->restaurant->id, data_get($schedule, 'restaurant_id'));
        }
    }
}
