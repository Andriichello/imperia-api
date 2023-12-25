<?php

namespace App\Models\Interfaces;

use App\Models\Morphs\Tip;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * Interface TippableInterface.
 *
 * @property Tip[]|Collection $tips
 */
interface TippableInterface
{
    /**
     * Tips related to the model.
     *
     * @return MorphMany
     */
    public function tips(): MorphMany;

    /**
     * Attach given tips to the model.
     *
     * @param Tip ...$tips
     *
     * @return void
     */
    public function attachTips(Tip ...$tips): void;

    /**
     * Detach given tips from the model.
     *
     * @param Tip ...$tips
     *
     * @return void
     */
    public function detachTips(Tip ...$tips): void;

    /**
     * Determines if model has tips attached.
     *
     * @return bool
     */
    public function hasTips(): bool;

    /**
     * Determines if model has all tips attached.
     *
     * @param Tip ...$tips
     *
     * @return bool
     */
    public function hasAllOfTips(Tip ...$tips): bool;

    /**
     * Determines if model has any of tips attached.
     *
     * @param Tip ...$tips
     *
     * @return bool
     */
    public function hasAnyOfTips(Tip ...$tips): bool;
}
