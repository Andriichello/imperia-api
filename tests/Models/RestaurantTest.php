<?php

namespace Tests\Models;

use App\Enums\Weekday;
use App\Models\Holiday;
use App\Models\Restaurant;
use App\Models\Schedule;
use Tests\TestCase;

/**
 * Class RestaurantTest.
 */
class RestaurantTest extends TestCase
{
    /**
     * Instance of the tested class.
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

        $this->restaurant = Restaurant::factory()->create();

        $this->seedHolidays();
        $this->seedSchedules();
    }

    public function seedHolidays()
    {
        Holiday::factory()
            ->withRestaurant($this->restaurant)
            ->withDate(now()->subWeek())
            ->create();

        Holiday::factory()
            ->withDate(now())
            ->create();

        Holiday::factory()
            ->withRestaurant($this->restaurant)
            ->withDate(now())
            ->create();

        Holiday::factory()
            ->withDate(now()->addWeek())
            ->create();

        Holiday::factory()
            ->withDate(now()->addYear()->addWeek())
            ->create();
    }

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

    public function testHolidays()
    {
        $holidays = $this->restaurant
            ->relevantHolidays()
            ->get();

        $this->assertCount(3, $holidays);

        $holidays = $this->restaurant
            ->relevantHolidays()
            ->relevantOn($on = now())
            ->get();

        $this->assertCount(2, $holidays);
        foreach ($holidays as $holiday) {
            $holiday->relevantOn($on);
        }
    }

    public function testSchedules()
    {
        $schedules = $this->restaurant
            ->operativeSchedules;

        $this->assertCount(7, $schedules);

        foreach ($schedules as $schedule) {
            /** @var Schedule $schedule */
            $schedule->weekday === Weekday::Friday
                ? $this->assertNotNull($schedule->restaurant_id)
                : $this->assertNull($schedule->restaurant_id);
        }
    }
}
