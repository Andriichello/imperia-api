<?php

namespace App\Repositories\Traits;

use App\Models\Interfaces\CommentableInterface;
use App\Models\Morphs\Comment;
use Illuminate\Support\Arr;

/**
 * Trait CommentableRepositoryTrait.
 */
trait CommentableRepositoryTrait
{
    /**
     * Create comments from attributes for model, which is commentable.
     *
     * @param CommentableInterface $commentable
     * @param array $attributes
     *
     * @return bool
     */
    public function createComments(CommentableInterface $commentable, array $attributes): bool
    {
        if (Arr::has($attributes, 'comments')) {
            $texts = $this->extractTextsFromComments($attributes['comments']);
            $commentable->attachComments(...$texts);
        }

        return true;
    }

    /**
     * Update comments from attributes for model, which is commentable.
     *
     * @param CommentableInterface $commentable
     * @param array $attributes
     *
     * @return bool
     */
    public function updateComments(CommentableInterface $commentable, array $attributes): bool
    {
        if (Arr::has($attributes, 'comments')) {
            $texts = $this->extractTextsFromComments($attributes['comments']);
            if (empty($texts)) {
                $commentable->comments()->delete();
            }

            $comments = $commentable->comments;
            // old and new comments are identical, so none should be changed
            if ($texts === $comments->pluck('text')->all()) {
                return true;
            }

            $updatedIds = [];
            foreach ($texts as $index => $text) {
                if ($comments->count() < ($index + 1)) {
                    $remainingTexts = array_slice($attributes['comments'], $index + 1);
                    $commentable->attachComments(...$remainingTexts);
                    break;
                }

                /** @var Comment $comment */
                $comment = $comments->get($index);
                $comment->text = $text;
                $comment->save();

                $updatedIds[] = $comment->id;
            }

            if ($comments->count() > count($texts)) {
                $commentable->comments()
                    ->whereNotIn('id', $updatedIds)
                    ->delete();
            }
        }

        return true;
    }

    /**
     * Extract texts from comments.
     *
     * @param array $comments
     *
     * @return array
     */
    private function extractTextsFromComments(array $comments): array
    {
        return Arr::pluck($comments, 'text');
    }
}
