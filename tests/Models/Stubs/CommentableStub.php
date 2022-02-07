<?php

namespace Tests\Models\Stubs;

use App\Models\Interfaces\CommentableInterface;
use App\Models\Traits\CommentableTrait;

/**
 * Class CommentableStub.
 */
class CommentableStub extends BaseStub implements CommentableInterface
{
    use CommentableTrait;
}
