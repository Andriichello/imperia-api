<?php

namespace Tests\Models\Morphs;

use App\Models\Morphs\Taggable;
use App\Models\Morphs\Tag;
use Tests\Models\Stubs\TaggableStub;
use Tests\StubsTestCase;

/**
 * Class TagTest.
 */
class TagTest extends StubsTestCase
{
    /**
     * Instance of the tested class.
     *
     * @var Tag
     */
    protected Tag $instance;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = Tag::factory()->create();
    }

    /**
     * Test taggable and tagged relations.
     *
     * @return void
     */
    public function testTaggables()
    {
        /** @var TaggableStub $stubOne */
        $stubOne = TaggableStub::query()->create();
        /** @var TaggableStub $stubTwo */
        $stubTwo = TaggableStub::query()->create();

        Taggable::factory()->withTag($this->instance)->withModel($stubOne)->create();
        $this->assertEquals(1, $this->instance->taggables()->count());

        /** @var Taggable $taggable */
        $taggable = $this->instance->taggables()->first();
        $this->assertNotEmpty($taggable->tagged);
        $this->assertEquals($taggable->tagged->id, $stubOne->id);
        $this->assertEquals($taggable->tagged->type, $stubOne->type);
        $this->assertTrue($taggable->tagged instanceof $stubOne);

        Taggable::factory()->withTag($this->instance)->withModel($stubTwo)->create();
        $this->assertEquals(2, $this->instance->taggables()->count());

        /** @var Taggable $taggable */
        $taggable = $this->instance->taggables()->offset(1)->first();
        $this->assertNotEmpty($taggable->tagged);
        $this->assertEquals($taggable->tagged->id, $stubTwo->id);
        $this->assertEquals($taggable->tagged->type, $stubTwo->type);
        $this->assertTrue($taggable->tagged instanceof $stubTwo);
    }
}
