<?php

namespace App\Http\Resources\Comment;

use App\Models\Morphs\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class CommentResource.
 *
 * @mixin Comment
 */
class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {

        return [
            'id' => $this->id,
            'type' => $this->type,
            'text' => $this->text,
            'commentable_id' => $this->commentable_id,
            'commentable_type' => $this->commentable_type,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Comment",
     *   description="Comment resource object",
     *   required = {"id", "type", "text", "commentable_id", "commentable_type"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="comments"),
     *   @OA\Property(property="text", type="string", example="This is a comment."),
     *   @OA\Property(property="commentable_id", type="integer", example=1),
     *   @OA\Property(property="commentable_type", type="string", example="products"),
     * )
     */
}
