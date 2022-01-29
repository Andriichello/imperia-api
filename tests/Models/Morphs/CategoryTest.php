<?php

namespace Tests\Models\Morphs;

use App\Models\Morphs\Categorizable;
use App\Models\Morphs\Category;
use Tests\Models\StubModel;
use Tests\StubsTestCase;

/**
 * Class Category.
 */
class CategoryTest extends StubsTestCase
{
    /**
     * Instance of the tested class.
     *
     * @var Category
     */
    protected Category $instance;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Category::factory()->create();
    }

    /**
     * Test attaching and detaching categories.
     *
     * @return void
     */
    public function testCategorizables()
    {
        /** @var StubModel $stubOne */
        $stubOne = StubModel::query()->create(['name' => 'One']);
        /** @var StubModel $stubTwo */
        $stubTwo = StubModel::query()->create(['name' => 'Two']);

        Categorizable::factory()->withCategory($this->instance)->withModel($stubOne)->create();
        $this->assertEquals(1, $this->instance->categorizables()->count());

        /** @var Categorizable $categorizable */
        $categorizable = $this->instance->categorizables()->first();
        $this->assertNotEmpty($categorizable->categorized);
        $this->assertEquals($categorizable->categorized->id, $stubOne->id);
        $this->assertEquals($categorizable->categorized->type, $stubOne->type);
        $this->assertTrue($categorizable->categorized instanceof $stubOne);

        Categorizable::factory()->withCategory($this->instance)->withModel($stubTwo)->create();
        $this->assertEquals(2, $this->instance->categorizables()->count());

        /** @var Categorizable $categorizable */
        $categorizable = $this->instance->categorizables()->offset(1)->first();
        $this->assertNotEmpty($categorizable->categorized);
        $this->assertEquals($categorizable->categorized->id, $stubTwo->id);
        $this->assertEquals($categorizable->categorized->type, $stubTwo->type);
        $this->assertTrue($categorizable->categorized instanceof $stubTwo);
    }
}
