<?php

namespace Tests\Queries;

use App\Models\Holiday;
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

        $dates = [
            now()->subWeek(),
            now(),
            now()->addWeek(),
            now()->addYear(),
        ];

        $this->holidays = collect();
        foreach ($dates as $date) {
            $holiday = Holiday::factory()
                ->withDate($date)
                ->create();

            $this->holidays->push($holiday);
        }
    }

    /**
     * @return array
     */
    public function relevantDates(): array
    {
        return [
            [now()->setTime(0, 0)],
            [now()->addWeek()->setTime(0, 0)],
            [now()->addYear()->setTime(0, 0)],
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
        $this->assertTrue($date->isSameDay($holiday->date));
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

        $this->assertTrue($relevants->isNotEmpty());

        foreach ($relevants as $relevant) {
            /** @var Holiday $relevant */
            $this->assertTrue($date->lessThanOrEqualTo($relevant->date));
        }
    }

    /**
     * Test relevantUntil method.
     *
     * @param CarbonInterface $date
     *
     * @return void
     * @dataProvider relevantDates
     */
    public function testRelevantUntil(CarbonInterface $date)
    {
        $relevants = Holiday::query()
            ->relevantUntil($date)
            ->get();

        $this->assertTrue($relevants->isNotEmpty());

        foreach ($relevants as $relevant) {
            /** @var Holiday $relevant */
            $this->assertTrue($date->greaterThanOrEqualTo($relevant->date));
        }
    }
}
