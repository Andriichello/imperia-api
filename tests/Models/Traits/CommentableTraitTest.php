<?php

namespace Tests\Models\Traits;

use App\Models\Morphs\Comment;
use Tests\Models\Stubs\CommentableStub;
use Tests\StubsTestCase;

/**
 * Class CommentableTraitTest.
 */
class CommentableTraitTest extends StubsTestCase
{
    /**
     * Instance of the tested class.
     *
     * @var CommentableStub
     */
    protected CommentableStub $instance;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->instance = new CommentableStub();
        $this->instance->save();
    }

    /**
     * Test attachCategories and detachCategories methods.
     *
     * @return void
     */
    public function testAttachAndDetachCategories()
    {
        $texts = ['one', 'two'];

        $this->instance->attachComments($texts[0]);
        $this->assertTrue($this->instance->hasAllOfComments($texts[0]));
        $this->assertEquals(1, $this->instance->comments()->count());

        $this->instance->attachComments($texts[1]);
        $this->assertTrue($this->instance->hasAllOfComments(...$texts));
        $this->assertEquals(2, $this->instance->comments()->count());

        $this->instance->detachComments($texts[0]);
        $this->assertTrue($this->instance->hasAllOfComments($texts[1]));
        $this->assertEquals(1, $this->instance->comments()->count());

        $this->instance->detachComments($texts[1]);
        $this->assertFalse($this->instance->hasAnyOfComments(...$texts));
        $this->assertEquals(0, $this->instance->comments()->count());
    }

    /**
     * Test hasAllOfComments method.
     *
     * @return void
     */
    public function testHasAllComments()
    {
        $texts = ['one', 'two'];

        $this->assertTrue($this->instance->hasAllOfComments());
        $this->assertFalse($this->instance->hasAllOfComments($texts[0]));
        $this->assertFalse($this->instance->hasAllOfComments($texts[1]));
        $this->assertFalse($this->instance->hasAllOfComments(...$texts));

        Comment::factory()->withText($texts[0])->withModel($this->instance)->create();

        $this->assertTrue($this->instance->hasAllOfComments());
        $this->assertTrue($this->instance->hasAllOfComments($texts[0]));
        $this->assertFalse($this->instance->hasAllOfComments($texts[1]));
        $this->assertFalse($this->instance->hasAllOfComments(...$texts));

        Comment::factory()->withText($texts[1])->withModel($this->instance)->create();

        $this->assertTrue($this->instance->hasAllOfComments());
        $this->assertTrue($this->instance->hasAllOfComments($texts[0]));
        $this->assertTrue($this->instance->hasAllOfComments($texts[1]));
        $this->assertTrue($this->instance->hasAllOfComments(...$texts));
    }

    /**
     * Test hasAnyOfComments method.
     *
     * @return void
     */
    public function testHasAnyComments()
    {
        $texts = ['one', 'two'];

        $this->assertTrue($this->instance->hasAnyOfComments());
        $this->assertFalse($this->instance->hasAnyOfComments($texts[0]));
        $this->assertFalse($this->instance->hasAnyOfComments($texts[1]));
        $this->assertFalse($this->instance->hasAnyOfComments(...$texts));

        Comment::factory()->withText($texts[0])->withModel($this->instance)->create();

        $this->assertTrue($this->instance->hasAnyOfComments());
        $this->assertTrue($this->instance->hasAnyOfComments($texts[0]));
        $this->assertFalse($this->instance->hasAnyOfComments($texts[1]));
        $this->assertTrue($this->instance->hasAnyOfComments(...$texts));

        Comment::factory()->withText($texts[1])->withModel($this->instance)->create();

        $this->assertTrue($this->instance->hasAnyOfComments());
        $this->assertTrue($this->instance->hasAnyOfComments($texts[0]));
        $this->assertTrue($this->instance->hasAnyOfComments($texts[1]));
        $this->assertTrue($this->instance->hasAnyOfComments(...$texts));
    }
}
