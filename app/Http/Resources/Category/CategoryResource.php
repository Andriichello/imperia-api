<?php

namespace App\Http\Resources\Category;

use App\Models\Morphs\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class CategoryResource.
 *
 * @mixin Category
 */
class CategoryResource extends JsonResource
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
            'slug' => $this->slug,
            'target' => $this->target,
            'title' => $this->title,
            'description' => $this->description,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Category",
     *   description="Category resource object",
     *   required = {"id", "type", "slug", "target", "title", "description"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="slug", type="string", example="pizza"),
     *   @OA\Property(property="type", type="string", example="categories"),
     *   @OA\Property(property="target", type="string", example="products", nullable="true"),
     *   @OA\Property(property="title", type="string", example="Pizza"),
     *   @OA\Property(property="description", example="Some text...", nullable="true"),
     * )
     */
}
