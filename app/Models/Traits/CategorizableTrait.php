<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\Morphs\Categorizable as CategorizableModel;
use App\Models\Morphs\Category;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

/**
 * Trait Categorizable.
 *
 * @mixin BaseModel
 */
trait Categorizable
{
    /**
     * Categories related to the model.
     *
     * @return MorphMany
     */
    public function categories(): MorphMany
    {
        return $this->morphMany(Category::class, 'categorizable', 'categorizable_type', 'categorizable_id', 'id');
    }

    /**
     * Attach given categories to the model.
     *
     * @param mixed $categories
     *
     * @return bool
     */
    public function attachCategory(mixed $categories): bool
    {
        $slugs = $this->extractCategorySlugs($categories);
        if (empty($slugs)) {
            return true;
        }

        return DB::transaction(function () use ($slugs) {
            $categories = Category::query()->whereIn('slug', $slugs)
                ->each(function (Category $category) {
                    CategorizableModel::query()
                        ->firstOrCreate([
                            'categorizable_id' => $
                        ])
                });

        });
    }

    /**
     * Determines if model has all categories attached.
     *
     * @param mixed $categories
     *
     * @return bool
     */
    public function hasAllCategories(mixed $categories): bool
    {
        $slugs = $this->extractCategorySlugs($categories);
        if (empty($slugs)) {
            return true;
        }
        return count($slugs) === $this->categories()->whereIn('slug', $slugs)->count();
    }

    /**
     * Determines if model has any of categories attached.
     *
     * @param mixed $categories
     *
     * @return bool
     */
    public function hasAnyCategories(mixed $categories): bool
    {
        $slugs = $this->extractCategorySlugs($categories);
        if (empty($slugs)) {
            return true;
        }
        return $this->categories()->whereIn('slug', $slugs)->exists();
    }

    /**
     * Extract given in argument category ids.
     *
     * @param mixed $categories
     *
     * @return array
     */
    protected function extractCategoryIds(mixed $categories): array
    {
        $models = array_filter($categories, fn($category) => $category instanceof Category);
        return array_merge(
            array_filter($categories, fn($category) => is_integer($category)),
            array_map(fn(Category $category) => $category->id, $models),
        );
    }

    /**
     * Extract given in argument category slugs.
     *
     * @param mixed $categories
     *
     * @return array
     */
    protected function extractCategorySlugs(mixed $categories): array
    {
        $slugs = array_filter($categories, fn($category) => is_string($category));

        $ids = $this->extractCategoryIds($categories);
        if (!empty($ids)) {
            $plucked = $slugs = Category::query()
                ->whereIn('id', $ids)
                ->pluck('slug')
                ->all();

            $slugs = array_merge($slugs, $plucked);
        }

        return array_unique(array_filter($slugs));
    }
}
