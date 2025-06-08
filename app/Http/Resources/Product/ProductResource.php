<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Alternation\AlternationCollection;
use App\Http\Resources\Category\CategoryCollection;
use App\Http\Resources\Media\MediaCollection;
use App\Http\Resources\Tag\TagCollection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class ProductResource.
 *
 * @mixin Product
 *
 * @property Product $resource
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
        $categoryIds = $this->resource->relationLoaded('categories')
            ? $this->resource->categories->pluck('id')
            : $this->resource->categories()->pluck('id'); // @phpstan-ignore-line

        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'weight' => $this->weight,
            'weight_unit' => $this->weight_unit,
            'badge' => $this->badge,
            'archived' => $this->archived,
            'popularity' => $this->popularity,
            'preparation_time' => $this->preparation_time,
            'calories' => $this->calories,
            'is_vegan' => $this->is_vegan,
            'is_vegetarian' => $this->is_vegetarian,
            'is_low_calorie' => $this->is_low_calorie,
            'has_eggs' => $this->has_eggs,
            'has_nuts' => $this->has_nuts,
            'hotness' => $this->hotness,
            'variants' => new ProductVariantCollection($this->variants),
            'menu_ids' => $this->menuIds(),
            'categories' => new CategoryCollection($this->whenLoaded('categories')),
            'category_ids' => $categoryIds,
            'flags' => $this->flags,
            'tags' => new TagCollection($this->whenLoaded('tags')),
            /* @phpstan-ignore-next-line */
            'media' => new MediaCollection($this->media->load('variants')),
            'alterations' => new AlternationCollection($this->whenLoaded('alterations')),
            'pendingAlterations' => new AlternationCollection($this->whenLoaded('pendingAlterations')),
            'performedAlterations' => new AlternationCollection($this->whenLoaded('performedAlterations')),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Product",
     *   description="Product resource object",
     *   required = {"id", "type", "title", "description", "price", "weight", "weight_unit",
     *     "badge", "archived", "popularity", "calories", "is_vegan", "is_vegetarian", "is_low_calorie",
     *     "has_eggs", "has_nuts", "hotness", "preparation_time",
     *     "tags", "flags", "categories", "media", "variants", "menu_ids", "category_ids", "media"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="type", type="string", example="products"),
     *   @OA\Property(property="title", type="string", example="Margarita"),
     *   @OA\Property(property="description", type="string", nullable=true, example="Some text..."),
     *   @OA\Property(property="price", type="float", example=15.99),
     *   @OA\Property(property="weight", type="float", nullable=true, example=120),
     *   @OA\Property(property="weight_unit", type="string", nullable=true, example="g",
     *     enum={"g", "kg", "ml", "l", "cm"}),
     *   @OA\Property(property="badge", type="string", nullable=true, example="New"),
     *   @OA\Property(property="archived", type="boolean", example=false),
     *   @OA\Property(property="popularity", type="integer", nullable=true, example=1),
     *   @OA\Property(property="preparation_time", type="integer", nullable=true, example=30),
     *   @OA\Property(property="calories", type="integer", nullable=true, example=500),
     *   @OA\Property(property="is_vegan", type="boolean", nullable=true, example=true),
     *   @OA\Property(property="is_vegetarian", type="boolean", nullable=true, example=true),
     *   @OA\Property(property="is_low_calorie", type="boolean", nullable=true, example=true),
     *   @OA\Property(property="has_eggs", type="boolean", nullable=true, example=false),
     *   @OA\Property(property="has_nuts", type="boolean", nullable=true, example=false),
     *   @OA\Property(property="hotness", type="string", nullable=true, example="Medium",
     *     enum={"Unknown", "Low", "Medium", "High", "Ultra"}),
     *   @OA\Property(property="variants", type="array", @OA\Items(ref ="#/components/schemas/ProductVariant")),
     *   @OA\Property(property="menu_ids", type="array", @OA\Items(type="integer", example=1)),
     *   @OA\Property(property="categories", type="array", @OA\Items(ref ="#/components/schemas/Category")),
     *   @OA\Property(property="category_ids", type="array", @OA\Items(type="integer", example=1)),
     *   @OA\Property(property="flags", type="array", @OA\Items(type="string")),
     *   @OA\Property(property="tags", type="array", @OA\Items(ref ="#/components/schemas/Tag")),
     *   @OA\Property(property="media", type="array", @OA\Items(ref ="#/components/schemas/Media")),
     *   @OA\Property(property="alterations", type="array",
     *     @OA\Items(ref ="#/components/schemas/Alternation")),
     *   @OA\Property(property="pendingAlterations", type="array",
     *     @OA\Items(ref ="#/components/schemas/Alternation")),
     *   @OA\Property(property="performedAlterations", type="array",
     *     @OA\Items(ref ="#/components/schemas/Alternation")),
     * )
     */
}
