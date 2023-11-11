<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\Interfaces\CategorizableInterface;
use App\Models\Morphs\Category;
use App\Models\Morphs\Tag;
use App\Models\Morphs\Taggable;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

/**
 * Trait TaggableTrait.
 *
 * @mixin BaseModel
 *
 * @property Tag[]|Collection $tags
 */
trait TaggableTrait
{
    /**
     * Tags related to the model.
     *
     * @return MorphToMany
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(
            Tag::class, // related model
            'taggable', // morph relation name
            Taggable::class, // morph relation table
            'taggable_id', // morph table pivot key to current model
            'tag_id' // morph table pivot key to related model
        )->orderByDesc('id');
    }

    /**
     * Attach given tags to the model.
     *
     * @param Tag ...$tags
     *
     * @return void
     */
    public function attachTags(Tag ...$tags): void
    {
        $this->tags()->attach(extractValues('id', ...$tags));
    }

    /**
     * Detach given tags from the model.
     *
     * @param Tag ...$tags
     *
     * @return void
     */
    public function detachTags(Tag ...$tags): void
    {
        $this->tags()->detach(extractValues('id', ...$tags));
    }

    /**
     * Determines if model has tags attached.
     *
     * @return bool
     */
    public function hasTags(): bool
    {
        return $this->tags()->exists();
    }

    /**
     * Determines if model has all tags attached.
     *
     * @param Tag ...$tags
     *
     * @return bool
     */
    public function hasAllOfTags(Tag ...$tags): bool
    {
        $ids = array_map(fn(Tag $tag) => $tag->id, $tags);
        $count = $this->tags()->whereIn('id', $ids)->count();
        return count($tags) === $count;
    }

    /**
     * Determines if model has any of tags attached.
     *
     * @param Tag ...$tags
     *
     * @return bool
     */
    public function hasAnyOfTags(Tag ...$tags): bool
    {
        $ids = array_map(fn(Tag $tag) => $tag->id, $tags);
        return empty($ids) || $this->tags()->whereIn('id', $ids)->exists();
    }

    /**
     * Determines if model is associated with
     * at least one of given tags or tag ids.
     *
     * @param Tag|int ...$tags
     *
     * @return bool
     */
    public function isAssociatedWithAnyOfTags(Tag|int ...$tags): bool
    {
        $ids = extractValues('id', ...$tags);

        // @phpstan-ignore-next-line
        $direct = $this->tags()
            ->whereIn('id', $ids);

        if ($direct->exists()) {
            return true;
        }

        if ($this instanceof CategorizableInterface && get_class($this) !== Category::class) {
            return Taggable::query()
                ->where('taggable_type', slugClass(Category::class))
                ->whereIn('tag_id', $ids)
                ->whereIn('taggable_id', $this->categories->pluck('id')->toArray())
                ->exists();
        }

        return false;
    }
}
