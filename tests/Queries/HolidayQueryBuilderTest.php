<?php

namespace Tests\Queries;

use App\Enums\UserRole;
use App\Models\Holiday;
use App\Models\Menu;
use App\Models\Morphs\Category;
use App\Models\Product;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Class HolidayQueryBuilderTest.
 */
class HolidayQueryBuilderTest extends TestCase
{
    public Collection $holidays;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->holidays = collect();

        $holiday = Holiday::factory()
            ->withDay(1)
            ->create();

        $this->holidays->push($holiday);

        $holiday = Holiday::factory()
            ->withDay(2)
            ->withMonth(2)
            ->create();

        $this->holidays->push($holiday);

        $holiday = Holiday::factory()
            ->withDay(3)
            ->withYear(2003)
            ->create();

        $this->holidays->push($holiday);

        $holiday = Holiday::factory()
            ->withDay(4)
            ->withMonth(4)
            ->withYear(2004)
            ->create();

        $this->holidays->push($holiday);

        $holiday = Holiday::factory()
            ->withDay(5)
            ->withMonth(5)
            ->withYear(2005)
            ->create();

        $this->holidays->push($holiday);
    }

    /**
     * @return array
     */
    public function relevantDates(): array
    {
        return [
            [now()->setDate(2000, 1, 1)],
            [now()->setDate(2000, 2, 2)],
            [now()->setDate(2003, 3, 3)],
            [now()->setDate(2004, 4, 4)],
            [now()->setDate(2005, 5, 5)],
        ];
    }

    /**
     * Test relevantOn method.
     *
     * @param CarbonInterface $date
     *
     * @return void
     * @dataProvider relevantDates
     */
    public function testRelevantOn(CarbonInterface $date)
    {
        $relevants = Holiday::query()
            ->relevantOn($date)
            ->get();

        $this->assertCount(1, $relevants);

        /** @var Holiday $holiday */
        $holiday = $relevants->first();

        $this->assertTrue(!$holiday->day || $holiday->day === $date->day);
        $this->assertTrue(!$holiday->month || $holiday->month === $date->month);
        $this->assertTrue(!$holiday->year || $holiday->year === $date->year);
    }

    /**
     * Test relevantFrom method.
     *
     * @param CarbonInterface $date
     *
     * @return void
     * @dataProvider relevantDates
     */
    public function testRelevantFrom(CarbonInterface $date)
    {
        $relevants = Holiday::query()
            ->relevantFrom($date)
            ->get();

        $holidays = $this->holidays
            ->filter(function (Holiday $holiday) use ($date) {
                return $holiday->relevantFrom($date);
            });

        $this->assertCount($holidays->count(), $relevants);
    }

    /**
     * Test relevantTo method.
     *
     * @param CarbonInterface $date
     *
     * @return void
     * @dataProvider relevantDates
     */
    public function testRelevantTo(CarbonInterface $date)
    {
        $relevants = Holiday::query()
            ->relevantUntil($date)
            ->get();

        $holidays = $this->holidays
            ->filter(function (Holiday $holiday) use ($date) {
                return $holiday->relevantUntil($date);
            });

        $this->assertCount($holidays->count(), $relevants);
    }
}
