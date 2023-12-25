<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\Morphs\Tip;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * Trait TippableTrait.
 *
 * @mixin BaseModel
 *
 * @property Tip[]|Collection $tips
 */
trait TippableTrait
{
    /**
     * Tips related to the model.
     *
     * @return MorphMany
     */
    public function tips(): MorphMany
    {
        return $this->morphMany(Tip::class, 'tippable');
    }

    /**
     * Attach given tips to the model.
     *
     * @param Tip ...$tips
     *
     * @return void
     */
    public function attachTips(Tip ...$tips): void
    {
        $this->tips()->attach(extractValues('id', ...$tips));
    }

    /**
     * Detach given tips from the model.
     *
     * @param Tip ...$tips
     *
     * @return void
     */
    public function detachTips(Tip ...$tips): void
    {
        $this->tips()->detach(extractValues('id', ...$tips));
    }

    /**
     * Determines if model has tips attached.
     *
     * @return bool
     */
    public function hasTips(): bool
    {
        return $this->tips()->exists();
    }

    /**
     * Determines if model has all tips attached.
     *
     * @param Tip ...$tips
     *
     * @return bool
     */
    public function hasAllOfTips(Tip ...$tips): bool
    {
        $ids = array_map(fn(Tip $tip) => $tip->id, $tips);
        $count = $this->tips()->whereIn('id', $ids)->count();
        return count($tips) === $count;
    }

    /**
     * Determines if model has any of tips attached.
     *
     * @param Tip ...$tips
     *
     * @return bool
     */
    public function hasAnyOfTips(Tip ...$tips): bool
    {
        $ids = array_map(fn(Tip $tip) => $tip->id, $tips);
        return empty($ids) || $this->tips()->whereIn('id', $ids)->exists();
    }
}
