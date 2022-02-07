<?php

namespace Tests\Models\Traits;

use App\Models\Morphs\Categorizable;
use App\Models\Morphs\Category;
use Tests\Models\Stubs\CategorizableStub;
use Tests\StubsTestCase;

/**
 * Class CategorizableTraitTest.
 */
class CategorizableTraitTest extends StubsTestCase
{
    /**
     * Instance of the tested class.
     *
     * @var CategorizableStub
     */
    protected CategorizableStub $instance;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = new CategorizableStub();
        $this->instance->save();
    }

    /**
     * Test attachCategories and detachCategories methods.
     *
     * @return void
     */
    public function testAttachAndDetachCategories()
    {
        $categoryOne = Category::factory()->create(['slug' => 'one']);
        $categoryTwo = Category::factory()->create(['slug' => 'two']);

        $this->instance->attachCategories($categoryOne);
        $this->assertTrue($this->instance->hasAllOfCategories($categoryOne));
        $this->assertEquals(1, $this->instance->categories()->count());

        $this->instance->attachCategories($categoryTwo);
        $this->assertTrue($this->instance->hasAllOfCategories($categoryOne, $categoryTwo));
        $this->assertEquals(2, $this->instance->categories()->count());

        $this->instance->detachCategories($categoryOne);
        $this->assertTrue($this->instance->hasAllOfCategories($categoryTwo));
        $this->assertEquals(1, $this->instance->categories()->count());

        $this->instance->detachCategories($categoryTwo);
        $this->assertFalse($this->instance->hasAnyOfCategories($categoryOne, $categoryTwo));
        $this->assertEquals(0, $this->instance->categories()->count());
    }

    /**
     * Test hasAllOfCategories method.
     *
     * @return void
     */
    public function testHasAllCategories()
    {
        $categoryOne = Category::factory()->create(['slug' => 'one']);
        $categoryTwo = Category::factory()->create(['slug' => 'two']);

        $this->assertTrue($this->instance->hasAllOfCategories());
        $this->assertFalse($this->instance->hasAllOfCategories($categoryOne));
        $this->assertFalse($this->instance->hasAllOfCategories($categoryTwo));
        $this->assertFalse($this->instance->hasAllOfCategories($categoryOne, $categoryTwo));

        Categorizable::factory()->withCategory($categoryOne)->withModel($this->instance)->create();

        $this->assertTrue($this->instance->hasAllOfCategories());
        $this->assertTrue($this->instance->hasAllOfCategories($categoryOne));
        $this->assertFalse($this->instance->hasAllOfCategories($categoryTwo));
        $this->assertFalse($this->instance->hasAllOfCategories($categoryOne, $categoryTwo));

        Categorizable::factory()->withCategory($categoryTwo)->withModel($this->instance)->create();

        $this->assertTrue($this->instance->hasAllOfCategories());
        $this->assertTrue($this->instance->hasAllOfCategories($categoryOne));
        $this->assertTrue($this->instance->hasAllOfCategories($categoryTwo));
        $this->assertTrue($this->instance->hasAllOfCategories($categoryOne, $categoryTwo));
    }

    /**
     * Test hasAnyOfCategories method.
     *
     * @return void
     */
    public function testHasAnyCategories()
    {
        $categoryOne = Category::factory()->create(['slug' => 'one']);
        $categoryTwo = Category::factory()->create(['slug' => 'two']);

        $this->assertTrue($this->instance->hasAnyOfCategories());
        $this->assertFalse($this->instance->hasAnyOfCategories($categoryOne));
        $this->assertFalse($this->instance->hasAnyOfCategories($categoryTwo));
        $this->assertFalse($this->instance->hasAnyOfCategories($categoryOne, $categoryTwo));

        Categorizable::factory()->withCategory($categoryOne)->withModel($this->instance)->create();

        $this->assertTrue($this->instance->hasAnyOfCategories());
        $this->assertTrue($this->instance->hasAnyOfCategories($categoryOne));
        $this->assertFalse($this->instance->hasAnyOfCategories($categoryTwo));
        $this->assertTrue($this->instance->hasAnyOfCategories($categoryOne, $categoryTwo));

        Categorizable::factory()->withCategory($categoryTwo)->withModel($this->instance)->create();

        $this->assertTrue($this->instance->hasAnyOfCategories());
        $this->assertTrue($this->instance->hasAnyOfCategories($categoryOne));
        $this->assertTrue($this->instance->hasAnyOfCategories($categoryTwo));
        $this->assertTrue($this->instance->hasAnyOfCategories($categoryOne, $categoryTwo));
    }
}
