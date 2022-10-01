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
        $dates = [
            now()->subWeek(),
            now(),
            now()->addWeek(),
            now()->addYear()->addWeek()
        ];

        foreach ($dates as $date) {
            $holiday = Holiday::factory()
                ->withDate($date)
                ->create();

            $this->restaurant->holidays()->attach($holiday->id);
        }
    }

    public function seedSchedules()
    {
        Schedule::factory()
            ->withWeekday(Weekday::Monday)
            ->withRestaurant($this->restaurant)
            ->create(['beg_hour' => 9, 'end_hour' => 22]);

        Schedule::factory()
            ->withWeekday(Weekday::Tuesday)
            ->withRestaurant($this->restaurant)
            ->create(['beg_hour' => 9, 'end_hour' => 22]);

        Schedule::factory()
            ->withWeekday(Weekday::Wednesday)
            ->withRestaurant($this->restaurant)
            ->create(['beg_hour' => 9, 'end_hour' => 22]);

        Schedule::factory()
            ->withWeekday(Weekday::Thursday)
            ->withRestaurant($this->restaurant)
            ->create(['beg_hour' => 9, 'end_hour' => 22]);

        Schedule::factory()
            ->withWeekday(Weekday::Friday)
            ->withRestaurant($this->restaurant)
            ->create(['beg_hour' => 9, 'end_hour' => 23]);

        Schedule::factory()
            ->withWeekday(Weekday::Saturday)
            ->withRestaurant($this->restaurant)
            ->create(['beg_hour' => 11, 'end_hour' => 21]);

        Schedule::factory()
            ->withWeekday(Weekday::Sunday)
            ->withRestaurant($this->restaurant)
            ->create(['beg_hour' => 11, 'end_hour' => 21]);
    }

    public function testHolidays()
    {
        $holidays = $this->restaurant
            ->relevantHolidays()
            ->get();

        $this->assertCount(3, $holidays);
    }

    public function testSchedules()
    {
        $schedules = $this->restaurant->schedules;

        $this->assertCount(7, $schedules);
    }
}
