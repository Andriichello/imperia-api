<?php

namespace Tests\Models\Traits;

use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Morphs\Comment;
use App\Models\Traits\SoftDeletableTrait;
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

    /**
     * Test comments deleting.
     *
     * @return void
     */
    public function testCommentsDeleting()
    {
        $this->instance->attachComments(
            'Comment one...',
            'Comment two...',
        );

        $comments = $this->instance->comments;
        $this->assertCount(2, $comments);

        $this->instance->delete();

        $exists = Comment::query()
            ->whereIn('id', $comments->pluck('id')->all())
            ->exists();

        $this->assertFalse($exists);
    }

    /**
     * Test comments deleting on soft-deletable model.
     *
     * @return void
     */
    public function testCommentsDeletingOnSoftDeletable()
    {
        $instance = new class extends CommentableStub implements SoftDeletableInterface {
            use SoftDeletableTrait;
        };
        $instance->save();

        $instance->attachComments(
            'Comment one...',
            'Comment two...',
        );

        $comments = $instance->comments;
        $this->assertCount(2, $comments);

        $instance->delete();
        $this->assertNotNull($instance->deleted_at);

        $exists = Comment::query()
            ->whereIn('id', $comments->pluck('id')->all())
            ->exists();

        $this->assertTrue($exists);

        $instance->forceDelete();

        $exists = Comment::query()
            ->whereIn('id', $comments->pluck('id')->all())
            ->exists();

        $this->assertFalse($exists);
    }
}
