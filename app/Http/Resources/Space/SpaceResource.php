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

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'floor' => $this->floor,
            'number' => $this->number,
            'price' => $this->price,
            'categories' => new CategoryCollection($this->whenLoaded('categories')),
        ];
    }
}
