<?php

namespace App\Http\Resources\Editor;

use App\Http\Resources\Media\MediaCollection;
use App\Models\Dish;
use App\Models\DishVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class EditorDishResource.
 *
 * @mixin Dish
 */
class EditorDishResource extends JsonResource
{
    use FormatsTranslations;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'menu_id' => $this->menu_id,
            'category_id' => $this->category_id,
            'slug' => $this->slug,
            'title' => $this->translations($this->resource, 'title'),
            'description' => $this->translations($this->resource, 'description'),
            'badge' => $this->translations($this->resource, 'badge'),
            'is_hidden' => $this->is_hidden,
            'archived' => (bool) $this->archived,
            'archived_at' => $this->archived_at?->toIso8601String(),
            'popularity' => $this->popularity,
            'flags' => $this->flags,
            'sizes' => $this->sizes(),
            'photos' => new MediaCollection($this->whenLoaded('media')),
        ];
    }

    /**
     * Sizes of the dish (the dish itself, with no id, and its variants), from the cheapest,
     * like the website lists them.
     *
     * @return array
     */
    protected function sizes(): array
    {
        $size = fn (?int $id, Dish|DishVariant $model) => [
            'id' => $id,
            'price' => (float) $model->price,
            'weight' => $model->weight,
            'weight_unit' => $model->weight_unit,
            'calories' => $model->calories,
            'preparation_time' => $model->preparation_time,
        ];

        return collect([$size(null, $this->resource)])
            ->merge($this->variants->map(fn (DishVariant $variant) => $size($variant->id, $variant)))
            ->sortBy('price')
            ->values()
            ->all();
    }

    /**
     * @OA\Schema(
     *   schema="EditorSize",
     *   description="Size of a dish: the dish itself (with no id) or one of its variants.",
     *   required={"id", "price", "weight", "weight_unit", "calories", "preparation_time"},
     *   @OA\Property(property="id", type="integer", nullable=true, example=null,
     *     description="Id of the variant, `null` for the dish itself."),
     *   @OA\Property(property="price", type="number", example=185),
     *   @OA\Property(property="weight", type="string", nullable=true, example="300"),
     *   @OA\Property(property="weight_unit", type="string", nullable=true, example="g",
     *     enum={"g", "kg", "ml", "l", "cm", "pc"}),
     *   @OA\Property(property="calories", type="integer", nullable=true, example=380),
     *   @OA\Property(property="preparation_time", type="integer", nullable=true, example=15,
     *     description="In minutes."),
     * ),
     * @OA\Schema(
     *   schema="EditorDish",
     *   description="Dish with its sizes (from the cheapest) and photos.",
     *   required={"id", "menu_id", "category_id", "slug", "title", "description", "badge", "is_hidden",
     *     "archived", "archived_at", "popularity", "flags", "sizes"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="menu_id", type="integer", example=1),
     *   @OA\Property(property="category_id", type="integer", nullable=true, example=1),
     *   @OA\Property(property="slug", type="string", nullable=true, example="borscht"),
     *   @OA\Property(property="title", ref="#/components/schemas/EditorTranslations"),
     *   @OA\Property(property="description", ref="#/components/schemas/EditorTranslations"),
     *   @OA\Property(property="badge", ref="#/components/schemas/EditorTranslations"),
     *   @OA\Property(property="is_hidden", type="boolean", example=false),
     *   @OA\Property(property="archived", type="boolean", example=false),
     *   @OA\Property(property="archived_at", type="string", format="date-time", nullable=true),
     *   @OA\Property(property="popularity", type="integer", nullable=true, example=5,
     *     description="Order of the dishes in the category, from the highest."),
     *   @OA\Property(property="flags", type="array", @OA\Items(type="string"),
     *     example={"vegetarian", "alg-milk"}, description="Diet tags and allergens."),
     *   @OA\Property(property="sizes", type="array", @OA\Items(ref="#/components/schemas/EditorSize")),
     *   @OA\Property(property="photos", type="array", @OA\Items(ref="#/components/schemas/Media")),
     * ),
     */
}
