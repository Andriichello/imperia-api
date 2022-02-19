<?php

namespace Tests\Models\Morphs;

use App\Models\Morphs\Period;
use App\Models\Morphs\Periodical;
use Tests\Models\Stubs\PeriodicalStub;
use Tests\StubsTestCase;

/**
 * Class PeriodTest.
 */
class PeriodTest extends StubsTestCase
{
    /**
     * Instance of the tested class.
     *
     * @var Period
     */
    protected Period $instance;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Period::factory()->create();
    }

    /**
     * Test periodical and periodic relations.
     *
     * @return void
     */
    public function testPeriodicals()
    {
        /** @var PeriodicalStub $stubOne */
        $stubOne = PeriodicalStub::query()->create();
        /** @var PeriodicalStub $stubTwo */
        $stubTwo = PeriodicalStub::query()->create();

        Periodical::factory()->withPeriod($this->instance)->withModel($stubOne)->create();
        $this->assertEquals(1, $this->instance->periodicals()->count());

        /** @var Periodical $periodical */
        $periodical = $this->instance->periodicals()->first();
        $this->assertNotEmpty($periodical->periodic);
        $this->assertEquals($periodical->periodic->id, $stubOne->id);
        $this->assertEquals($periodical->periodic->type, $stubOne->type);
        $this->assertTrue($periodical->periodic instanceof $stubOne);

        Periodical::factory()->withPeriod($this->instance)->withModel($stubTwo)->create();
        $this->assertEquals(2, $this->instance->periodicals()->count());

        /** @var Periodical $periodical */
        $periodical = $this->instance->periodicals()->offset(1)->first();
        $this->assertNotEmpty($periodical->periodic);
        $this->assertEquals($periodical->periodic->id, $stubTwo->id);
        $this->assertEquals($periodical->periodic->type, $stubTwo->type);
        $this->assertTrue($periodical->periodic instanceof $stubTwo);
    }
}
