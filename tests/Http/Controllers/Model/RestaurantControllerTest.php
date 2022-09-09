<?php

namespace Tests\Http\Controllers\Model;

use App\Enums\Weekday;
use App\Models\Holiday;
use App\Models\Restaurant;
use App\Models\Schedule;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Tests\RegisteringTestCase;

/**
 * Class RestaurantControllerTest.
 */
class RestaurantControllerTest extends RegisteringTestCase
{
    use MakesHttpRequests;

    /**
     * If true, then user should be logged-in on set up.
     *
     * @var bool
     */
    protected bool $shouldLogin = true;

    /**
     * Target restaurant.
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

        $this->restaurant = Restaurant::factory()
            ->withSlug('main')
            ->create();
    }

    /**
     * Seed holidays.
     *
     * @return void
     */
    public function seedHolidays()
    {
        Holiday::factory()
            ->withDay(1)
            ->withMonth(1)
            ->create();

        Holiday::factory()
            ->withDay(2)
            ->withMonth(1)
            ->create();

        Holiday::factory()
            ->withDay(3)
            ->withMonth(1)
            ->create();

        Holiday::factory()
            ->withDay(1)
            ->withMonth(8)
            ->create();

        Holiday::factory()
            ->withDay(2)
            ->withMonth(8)
            ->create();
    }

    /**
     * Seed schedules.
     *
     * @return void
     */
    public function seedSchedules()
    {
        Schedule::factory()
            ->withWeekday(Weekday::Monday)
            ->create(['beg_hour' => 9, 'end_hour' => 22]);

        Schedule::factory()
            ->withWeekday(Weekday::Tuesday)
            ->create(['beg_hour' => 9, 'end_hour' => 22]);

        Schedule::factory()
            ->withWeekday(Weekday::Wednesday)
            ->create(['beg_hour' => 9, 'end_hour' => 22]);

        Schedule::factory()
            ->withWeekday(Weekday::Thursday)
            ->create(['beg_hour' => 9, 'end_hour' => 22]);

        Schedule::factory()
            ->withWeekday(Weekday::Friday)
            ->create(['beg_hour' => 9, 'end_hour' => 23]);

        Schedule::factory()
            ->withWeekday(Weekday::Saturday)
            ->create(['beg_hour' => 11, 'end_hour' => 21]);

        Schedule::factory()
            ->withWeekday(Weekday::Sunday)
            ->create(['beg_hour' => 11, 'end_hour' => 21]);

        Schedule::factory()
            ->withWeekday(Weekday::Friday)
            ->withRestaurant($this->restaurant)
            ->create(['beg_hour' => 14, 'end_hour' => 3]);
    }

    /**
     * Test getting restaurant's schedules.
     *
     * @return void
     */
    public function testGetSchedules()
    {
        $this->seedSchedules();

        $url = route(
            'api.restaurants.schedules',
            [
                'id' => $this->restaurant->id
            ]
        );
        $response = $this->get($url);

        $response->assertOk();
        $response->assertJsonCount(7, 'data');
    }

    /**
     * Test getting restaurant's holidays.
     *
     * @return void
     */
    public function testGetHolidays()
    {
        $this->seedHolidays();

        $url = route(
            'api.restaurants.holidays',
            [
                'id' => $this->restaurant->id,
                'days' => 1,
                'from' => '2022-01-01',
            ]
        );
        $response = $this->get($url);

        $response->assertOk();
        $response->assertJsonCount(1, 'data');

        $url = route(
            'api.restaurants.holidays',
            [
                'id' => $this->restaurant->id,
                'days' => 5,
                'from' => '2022-01-01',
            ]
        );
        $response = $this->get($url);

        $response->assertOk();
        $response->assertJsonCount(3, 'data');

        $url = route(
            'api.restaurants.holidays',
            [
                'id' => $this->restaurant->id,
                'days' => 7,
                'from' => '2022-08-01',
            ]
        );
        $response = $this->get($url);

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
    }
}
