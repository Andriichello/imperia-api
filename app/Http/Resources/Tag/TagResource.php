<?php

namespace App\Http\Resources\Tag;

use App\Models\Morphs\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class TagResource.
 *
 * @mixin Tag
 */
class TagResource extends JsonResource
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
            'target' => $this->target,
            'title' => $this->title,
            'description' => $this->description,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Tag",
     *   description="Tag resource object",
     *   required = {"id", "type", "target", "title", "description"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="categories"),
     *   @OA\Property(property="target", type="string", example="products", nullable="true"),
     *   @OA\Property(property="title", type="string", example="Pizza"),
     *   @OA\Property(property="description", example="Some text...", nullable="true"),
     * )
     */
}
