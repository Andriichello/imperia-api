<?php

namespace App\Http\Resources\Comment;

use App\Models\Morphs\Category;
use App\Models\Morphs\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
     * @param  Request  $request
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {

        return [
            'id' => $this->id,
            'text' => $this->text,
            'commentable_id' => $this->commentable_id,
            'commentable_type' => $this->commentable_type,
        ];
    }
}
