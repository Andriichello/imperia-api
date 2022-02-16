<?php

namespace App\Http\Resources\Ticket;

use App\Http\Resources\Category\CategoryCollection;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class TicketResource.
 *
 * @mixin Ticket
 */
class TicketResource extends JsonResource
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
        $categoryIds = $this->resource->categories()->pluck('id');
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'archived' => $this->archived,
            'categories' => new CategoryCollection($this->whenLoaded('categories')),
            'category_ids' => $categoryIds,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Ticket",
     *   description="Ticket resource object",
     *   required = {"id", "type", "title", "description", "price", "category_ids"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="tickets"),
     *   @OA\Property(property="title", type="string", example="Child ticket"),
     *   @OA\Property(property="description", type="string", example="Some text..."),
     *   @OA\Property(property="price", type="float", example=19.60),
     *   @OA\Property(property="archived", type="boolean", example="false"),
     *   @OA\Property(property="categories", type="array", @OA\Items(ref ="#/components/schemas/Category")),
     *   @OA\Property(property="category_ids", type="array", @OA\Items(type="integer", example=1)),
     * )
     */
}
