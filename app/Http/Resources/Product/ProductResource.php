<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Menu\MenuResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ProductResource.
 *
 * @mixin Product
 */
class ProductResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'weight' => $this->weight,
            'menu_id' => $this->menu_id,
            'menu' => new MenuResource($this->whenLoaded('menu')),
        ];
    }
}
