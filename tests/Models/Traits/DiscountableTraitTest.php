<?php

namespace Tests\Models\Traits;

use App\Models\Morphs\Discount;
use App\Models\Morphs\Discountable;
use Tests\Models\Stubs\DiscountableStub;
use Tests\StubsTestCase;

/**
 * Class DiscountableTraitTest.
 */
class DiscountableTraitTest extends StubsTestCase
{
    /**
     * Instance of the tested class.
     *
     * @var DiscountableStub
     */
    protected DiscountableStub $instance;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = new DiscountableStub();
        $this->instance->save();
    }

    /**
     * Test attachDiscounts and detachDiscounts methods.
     *
     * @return void
     */
    public function testAttachAndDetachDiscounts()
    {
        $discountOne = Discount::factory()->create([
            'title' => 'One',
            'amount' => 1,
        ]);
        $discountTwo = Discount::factory()->create([
            'title' => 'Two',
            'amount' => 2,
        ]);

        $this->instance->attachDiscounts($discountOne);
        $this->assertTrue($this->instance->hasAllOfDiscounts($discountOne));
        $this->assertEquals(1, $this->instance->discounts()->count());

        $this->instance->attachDiscounts($discountTwo);
        $this->assertTrue($this->instance->hasAllOfDiscounts($discountOne, $discountTwo));
        $this->assertEquals(2, $this->instance->discounts()->count());

        $this->instance->detachDiscounts($discountOne);
        $this->assertTrue($this->instance->hasAllOfDiscounts($discountTwo));
        $this->assertEquals(1, $this->instance->discounts()->count());

        $this->instance->detachDiscounts($discountTwo);
        $this->assertFalse($this->instance->hasAnyOfDiscounts($discountOne, $discountTwo));
        $this->assertEquals(0, $this->instance->discounts()->count());
    }

    /**
     * Test hasAllOfDiscounts method.
     *
     * @return void
     */
    public function testHasAllDiscounts()
    {
        $discountOne = Discount::factory()->create([
            'title' => 'One',
            'amount' => 1,
        ]);
        $discountTwo = Discount::factory()->create([
            'title' => 'Two',
            'amount' => 2,
        ]);

        $this->assertTrue($this->instance->hasAllOfDiscounts());
        $this->assertFalse($this->instance->hasAllOfDiscounts($discountOne));
        $this->assertFalse($this->instance->hasAllOfDiscounts($discountTwo));
        $this->assertFalse($this->instance->hasAllOfDiscounts($discountOne, $discountTwo));

        Discountable::factory()->withDiscount($discountOne)->withModel($this->instance)->create();

        $this->assertTrue($this->instance->hasAllOfDiscounts());
        $this->assertTrue($this->instance->hasAllOfDiscounts($discountOne));
        $this->assertFalse($this->instance->hasAllOfDiscounts($discountTwo));
        $this->assertFalse($this->instance->hasAllOfDiscounts($discountOne, $discountTwo));

        Discountable::factory()->withDiscount($discountTwo)->withModel($this->instance)->create();

        $this->assertTrue($this->instance->hasAllOfDiscounts());
        $this->assertTrue($this->instance->hasAllOfDiscounts($discountOne));
        $this->assertTrue($this->instance->hasAllOfDiscounts($discountTwo));
        $this->assertTrue($this->instance->hasAllOfDiscounts($discountOne, $discountTwo));
    }

    /**
     * Test hasAnyOfDiscounts method.
     *
     * @return void
     */
    public function testHasAnyDiscounts()
    {
        $discountOne = Discount::factory()->create([
            'title' => 'One',
            'amount' => 1,
        ]);
        $discountTwo = Discount::factory()->create([
            'title' => 'Two',
            'amount' => 2,
        ]);

        $this->assertTrue($this->instance->hasAnyOfDiscounts());
        $this->assertFalse($this->instance->hasAnyOfDiscounts($discountOne));
        $this->assertFalse($this->instance->hasAnyOfDiscounts($discountTwo));
        $this->assertFalse($this->instance->hasAnyOfDiscounts($discountOne, $discountTwo));

        Discountable::factory()->withDiscount($discountOne)->withModel($this->instance)->create();

        $this->assertTrue($this->instance->hasAnyOfDiscounts());
        $this->assertTrue($this->instance->hasAnyOfDiscounts($discountOne));
        $this->assertFalse($this->instance->hasAnyOfDiscounts($discountTwo));
        $this->assertTrue($this->instance->hasAnyOfDiscounts($discountOne, $discountTwo));

        Discountable::factory()->withDiscount($discountTwo)->withModel($this->instance)->create();

        $this->assertTrue($this->instance->hasAnyOfDiscounts());
        $this->assertTrue($this->instance->hasAnyOfDiscounts($discountOne));
        $this->assertTrue($this->instance->hasAnyOfDiscounts($discountTwo));
        $this->assertTrue($this->instance->hasAnyOfDiscounts($discountOne, $discountTwo));
    }
}
