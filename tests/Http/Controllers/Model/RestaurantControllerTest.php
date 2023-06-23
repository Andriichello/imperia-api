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
        $holiday = Holiday::factory()
            ->withRestaurant($this->restaurant)
            ->withDate(now()->subWeek())
            ->create();

        $holiday = Holiday::factory()
            ->withRestaurant($this->restaurant)
            ->withDate(now())
            ->create();

        $holiday = Holiday::factory()
            ->withRestaurant($this->restaurant)
            ->withDate(now()->addWeek())
            ->create();

        $holiday = Holiday::factory()
            ->withRestaurant($this->restaurant)
            ->withDate(now()->addYear())
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
            ->withRestaurant($this->restaurant)
            ->withWeekday(Weekday::Monday)
            ->create(['beg_hour' => 9, 'end_hour' => 22]);

        Schedule::factory()
            ->withRestaurant($this->restaurant)
            ->withWeekday(Weekday::Tuesday)
            ->create(['beg_hour' => 9, 'end_hour' => 22]);

        Schedule::factory()
            ->withRestaurant($this->restaurant)
            ->withWeekday(Weekday::Wednesday)
            ->create(['beg_hour' => 9, 'end_hour' => 22]);

        Schedule::factory()
            ->withRestaurant($this->restaurant)
            ->withWeekday(Weekday::Thursday)
            ->create(['beg_hour' => 9, 'end_hour' => 22]);

        Schedule::factory()
            ->withRestaurant($this->restaurant)
            ->withWeekday(Weekday::Friday)
            ->create(['beg_hour' => 9, 'end_hour' => 23]);

        Schedule::factory()
            ->withRestaurant($this->restaurant)
            ->withWeekday(Weekday::Saturday)
            ->create(['beg_hour' => 11, 'end_hour' => 21]);

        Schedule::factory()
            ->withRestaurant($this->restaurant)
            ->withWeekday(Weekday::Sunday)
            ->create(['beg_hour' => 11, 'end_hour' => 21]);
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
                'from' => now()->toDateString(),
            ]
        );
        $response = $this->get($url);

        $response->assertOk();
        $response->assertJsonCount(3, 'data');

        $url = route(
            'api.restaurants.holidays',
            [
                'id' => $this->restaurant->id,
                'from' => now()->addWeek()->toDateString(),
            ]
        );
        $response = $this->get($url);

        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $url = route(
            'api.restaurants.holidays',
            [
                'id' => $this->restaurant->id,
                'from' => now()->addYear()->toDateString(),
            ]
        );
        $response = $this->get($url);

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }
}
