<?php

namespace App\Http\Resources\Menu;

use App\Http\Resources\Category\CategoryCollection;
use App\Http\Resources\Media\MediaCollection;
use App\Http\Resources\Product\ProductCollection;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class MenuResource.
 *
 * @mixin Menu
 */
class MenuResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'popularity' => $this->popularity,
            'archived' => $this->archived,
            'products' => new ProductCollection($this->whenLoaded('products')),
            'categories' => new CategoryCollection($this->categories),
            /* @phpstan-ignore-next-line */
            'media' => new MediaCollection($this->media->load('variants')),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Menu",
     *   description="Menu resource object",
     *   required = {"id", "type", "title", "description", "categories", "archived",
     *     "popularity", "media"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="menus"),
     *   @OA\Property(property="title", type="string", example="Kitchen"),
     *   @OA\Property(property="description", type="string", example="Some text..."),
     *   @OA\Property(property="archived", type="boolean", example=false),
     *   @OA\Property(property="popularity", type="integer", nullable=true, example=1),
     *   @OA\Property(property="products", type="array", @OA\Items(ref ="#/components/schemas/Product")),
     *   @OA\Property(property="categories", type="array", @OA\Items(ref ="#/components/schemas/Category"),
     *     description="Categories that are attached directly to the menu."),
     *   @OA\Property(property="media", type="array", @OA\Items(ref ="#/components/schemas/Media")),
     * )
     */
}
