<?php

namespace Tests\Models\Morphs;

use App\Models\Morphs\Discount;
use App\Models\Morphs\Discountable;
use Tests\Models\Stubs\DiscountableStub;
use Tests\StubsTestCase;

/**
 * Class DiscountTest.
 */
class DiscountTest extends StubsTestCase
{
    /**
     * Instance of the tested class.
     *
     * @var Discount
     */
    protected Discount $instance;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Discount::factory()->create();
    }

    /**
     * Test discountable and discounted relations.
     *
     * @return void
     */
    public function testDiscountables()
    {
        /** @var DiscountableStub $stubOne */
        $stubOne = DiscountableStub::query()->create();
        /** @var DiscountableStub $stubTwo */
        $stubTwo = DiscountableStub::query()->create();

        Discountable::factory()->withDiscount($this->instance)->withModel($stubOne)->create();
        $this->assertEquals(1, $this->instance->discountables()->count());

        /** @var Discountable $discountable */
        $discountable = $this->instance->discountables()->first();
        $this->assertNotEmpty($discountable->discounted);
        $this->assertEquals($discountable->discounted->id, $stubOne->id);
        $this->assertEquals($discountable->discounted->type, $stubOne->type);
        $this->assertTrue($discountable->discounted instanceof $stubOne);

        Discountable::factory()->withDiscount($this->instance)->withModel($stubTwo)->create();
        $this->assertEquals(2, $this->instance->discountables()->count());

        /** @var Discountable $discountable */
        $discountable = $this->instance->discountables()->offset(1)->first();
        $this->assertNotEmpty($discountable->discounted);
        $this->assertEquals($discountable->discounted->id, $stubTwo->id);
        $this->assertEquals($discountable->discounted->type, $stubTwo->type);
        $this->assertTrue($discountable->discounted instanceof $stubTwo);
    }
}
