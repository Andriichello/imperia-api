<?php

namespace App\Http\Resources\Category;

use App\Http\Resources\Media\MediaCollection;
use App\Http\Resources\Tag\TagCollection;
use App\Models\Morphs\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

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
            'popularity' => $this->popularity,
            'description' => $this->description,
            'tags' => new TagCollection($this->whenLoaded('tags')),
            /* @phpstan-ignore-next-line */
            'media' => new MediaCollection($this->media->load('variants')),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Category",
     *   description="Category resource object",
     *   required = {"id", "type", "slug", "target", "title", "description", "media"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="slug", type="string", example="pizza"),
     *   @OA\Property(property="type", type="string", example="categories"),
     *   @OA\Property(property="target", type="string", example="products", nullable=true),
     *   @OA\Property(property="title", type="string", example="Pizza"),
     *   @OA\Property(property="description", example="Some text...", nullable=true),
     *   @OA\Property(property="tags", type="array", @OA\Items(ref ="#/components/schemas/Tag")),
     *   @OA\Property(property="media", type="array", @OA\Items(ref ="#/components/schemas/Media")),
     * )
     */
}
