<?php

namespace Tests\Models\Traits;

use App\Models\Morphs\Period;
use App\Models\Morphs\Periodical;
use Carbon\Carbon;
use Faker\Generator;
use Tests\Models\Stubs\PeriodicalStub;
use Tests\StubsTestCase;

/**
 * Class PeriodicalTraitTest.
 */
class PeriodicalTraitTest extends StubsTestCase
{
    /**
     * Instance of the tested class.
     *
     * @var PeriodicalStub
     */
    protected PeriodicalStub $instance;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = new PeriodicalStub();
        $this->instance->save();
    }

    /**
     * Test attachPeriods and detachPeriods methods.
     *
     * @return void
     */
    public function testAttachAndDetachPeriods()
    {
        $categoryOne = Period::factory()->create(['title' => 'one']);
        $categoryTwo = Period::factory()->create(['title' => 'two']);

        $this->instance->attachPeriods($categoryOne);
        $this->assertTrue($this->instance->hasAllOfPeriods($categoryOne));
        $this->assertEquals(1, $this->instance->periods()->count());

        $this->instance->attachPeriods($categoryTwo);
        $this->assertTrue($this->instance->hasAllOfPeriods($categoryOne, $categoryTwo));
        $this->assertEquals(2, $this->instance->periods()->count());

        $this->instance->detachPeriods($categoryOne);
        $this->assertTrue($this->instance->hasAllOfPeriods($categoryTwo));
        $this->assertEquals(1, $this->instance->periods()->count());

        $this->instance->detachPeriods($categoryTwo);
        $this->assertFalse($this->instance->hasAnyOfPeriods($categoryOne, $categoryTwo));
        $this->assertEquals(0, $this->instance->periods()->count());
    }

    /**
     * Test hasAllOfPeriods method.
     *
     * @return void
     */
    public function testHasAllPeriods()
    {
        $categoryOne = Period::factory()->create(['title' => 'one']);
        $categoryTwo = Period::factory()->create(['title' => 'two']);

        $this->assertTrue($this->instance->hasAllOfPeriods());
        $this->assertFalse($this->instance->hasAllOfPeriods($categoryOne));
        $this->assertFalse($this->instance->hasAllOfPeriods($categoryTwo));
        $this->assertFalse($this->instance->hasAllOfPeriods($categoryOne, $categoryTwo));

        Periodical::factory()->withPeriod($categoryOne)->withModel($this->instance)->create();

        $this->assertTrue($this->instance->hasAllOfPeriods());
        $this->assertTrue($this->instance->hasAllOfPeriods($categoryOne));
        $this->assertFalse($this->instance->hasAllOfPeriods($categoryTwo));
        $this->assertFalse($this->instance->hasAllOfPeriods($categoryOne, $categoryTwo));

        Periodical::factory()->withPeriod($categoryTwo)->withModel($this->instance)->create();

        $this->assertTrue($this->instance->hasAllOfPeriods());
        $this->assertTrue($this->instance->hasAllOfPeriods($categoryOne));
        $this->assertTrue($this->instance->hasAllOfPeriods($categoryTwo));
        $this->assertTrue($this->instance->hasAllOfPeriods($categoryOne, $categoryTwo));
    }

    /**
     * Test hasAnyOfPeriods method.
     *
     * @return void
     */
    public function testHasAnyPeriods()
    {
        $categoryOne = Period::factory()->create(['title' => 'one']);
        $categoryTwo = Period::factory()->create(['title' => 'two']);

        $this->assertTrue($this->instance->hasAnyOfPeriods());
        $this->assertFalse($this->instance->hasAnyOfPeriods($categoryOne));
        $this->assertFalse($this->instance->hasAnyOfPeriods($categoryTwo));
        $this->assertFalse($this->instance->hasAnyOfPeriods($categoryOne, $categoryTwo));

        Periodical::factory()->withPeriod($categoryOne)->withModel($this->instance)->create();

        $this->assertTrue($this->instance->hasAnyOfPeriods());
        $this->assertTrue($this->instance->hasAnyOfPeriods($categoryOne));
        $this->assertFalse($this->instance->hasAnyOfPeriods($categoryTwo));
        $this->assertTrue($this->instance->hasAnyOfPeriods($categoryOne, $categoryTwo));

        Periodical::factory()->withPeriod($categoryTwo)->withModel($this->instance)->create();

        $this->assertTrue($this->instance->hasAnyOfPeriods());
        $this->assertTrue($this->instance->hasAnyOfPeriods($categoryOne));
        $this->assertTrue($this->instance->hasAnyOfPeriods($categoryTwo));
        $this->assertTrue($this->instance->hasAnyOfPeriods($categoryOne, $categoryTwo));
    }

    /**
     * Test hasAffectingPeriods method.
     *
     * @return void
     */
    public function testHasAffectingPeriods()
    {
        /** @var Generator $faker */
        $faker = app(Generator::class);

        $passedPeriod = Period::factory()->create([
            'title' => 'Passed',
            'start_at' => $faker->dateTimeBetween('-5 days', '-2 days'),
            'end_at' => Carbon::yesterday(),
        ]);
        $currentPeriod = Period::factory()->create([
            'title' => 'Current',
            'start_at' => Carbon::yesterday(),
            'end_at' => Carbon::tomorrow(),
        ]);
        $futurePeriod = Period::factory()->create([
            'title' => 'Future',
            'start_at' => Carbon::tomorrow(),
            'end_at' => $faker->dateTimeBetween('+2 days', '+2 days'),
        ]);

        $this->instance->attachPeriods($passedPeriod);
        $this->assertFalse($this->instance->hasAffectingPeriods());
        $this->assertTrue($this->instance->hasAffectingPeriods($passedPeriod->end_at, $passedPeriod->end_at));

        $this->instance->attachPeriods($futurePeriod);
        $this->assertFalse($this->instance->hasAffectingPeriods());
        $this->assertTrue($this->instance->hasAffectingPeriods($futurePeriod->end_at, $futurePeriod->end_at));

        $this->instance->attachPeriods($currentPeriod);
        $this->assertTrue($this->instance->hasAffectingPeriods());
        $this->assertTrue($this->instance->hasAffectingPeriods($futurePeriod->end_at, $futurePeriod->end_at));
    }
}
