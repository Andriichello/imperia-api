<?php

namespace App\Repositories;

use App\Models\Morphs\Comment;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CommentRepository.
 */
class CommentRepository extends CrudRepository
{
    /**
     * Repository's target model class.
     *
     * @var Model|string
     */
    protected Model|string $model = Comment::class;
}
