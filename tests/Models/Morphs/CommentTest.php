<?php

namespace Tests\Models\Morphs;

use App\Models\Morphs\Comment;
use Tests\Models\Stubs\CommentableStub;
use Tests\StubsTestCase;

/**
 * Class CommentTest.
 */
class CommentTest extends StubsTestCase
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
     * Test commentable relation.
     *
     * @return void
     */
    public function testCommentable()
    {
        $comment = Comment::factory()->withText('Just a comment')
            ->withModel($this->instance)->create();

        $this->assertEquals(1, $this->instance->comments()->count());

        $this->assertNotEmpty($comment->commentable);
        $this->assertEquals($this->instance->id, $comment->commentable->id);
        $this->assertEquals($this->instance->type, $comment->commentable->type);
        $this->assertTrue($comment->commentable instanceof $this->instance);
    }
}
