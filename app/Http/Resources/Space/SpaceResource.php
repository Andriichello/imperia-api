<?php

namespace App\Http\Resources\Space;

use App\Http\Resources\Category\CategoryCollection;
use App\Models\Space;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class SpaceResource.
 *
 * @mixin Space
 */
class SpaceResource extends JsonResource
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
        $categoryIds = $this->categories()->pluck('id');
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'floor' => $this->floor,
            'number' => $this->number,
            'price' => $this->price,
            'categories' => new CategoryCollection($this->whenLoaded('categories')),
            'category_ids' => $this->$categoryIds,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Space",
     *   description="Space resource object",
     *   required = {"id", "type", "title", "description", "floor", "number", "price", "category_ids"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="spaces"),
     *   @OA\Property(property="title", type="string", example="Room #1(1)"),
     *   @OA\Property(property="description", type="string", example="Some text..."),
     *   @OA\Property(property="floor", type="integer", example=1),
     *   @OA\Property(property="number", type="integer", example=1),
     *   @OA\Property(property="price", type="float", example=15.99),
     *   @OA\Property(property="categories", type="array", @OA\Items(ref ="#/components/schemas/Category")),
     *   @OA\Property(property="category_ids", type="array", @OA\Items(type="integer", example=1)),
     * )
     */
}
