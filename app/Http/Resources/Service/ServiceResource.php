<?php

namespace App\Http\Resources\Service;

use App\Http\Resources\Category\CategoryCollection;
use App\Http\Resources\Media\MediaCollection;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ServiceResource.
 *
 * @mixin Service
 */
class ServiceResource extends JsonResource
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
            'once_paid_price' => $this->once_paid_price,
            'hourly_paid_price' => $this->hourly_paid_price,
            'archived' => $this->archived,
            'categories' => new CategoryCollection($this->whenLoaded('categories')),
            'category_ids' => $categoryIds,
//            'media' => new MediaCollection($this->media),
//            'default_media' => new MediaCollection($this->default_media),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Service",
     *   description="Service resource object",
     *   required = {"id", "type", "title", "description", "once_paid_price", "hourly_paid_price",
     *      "category_ids", "media", "default_media"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="services"),
     *   @OA\Property(property="title", type="string", example="Clown Show"),
     *   @OA\Property(property="description", type="string", example="Some text..."),
     *   @OA\Property(property="once_paid_price", type="float", example=350),
     *   @OA\Property(property="hourly_paid_price", type="float", example=200),
     *   @OA\Property(property="archived", type="boolean", example="false"),
     *   @OA\Property(property="categories", type="array", @OA\Items(ref ="#/components/schemas/Category")),
     *   @OA\Property(property="category_ids", type="array", @OA\Items(type="integer", example=1)),
     *   @OA\Property(property="media", type="array", @OA\Items(ref ="#/components/schemas/Media")),
     *   @OA\Property(property="default_media", type="array", @OA\Items(ref ="#/components/schemas/Media")),
     * )
     */
}
