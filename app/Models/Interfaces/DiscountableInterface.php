<?php

namespace App\Models\Interfaces;

use App\Models\Morphs\Discount;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * Interface DiscountableInterface.
 *
 * @property Discount[]|Collection $discounts
 */
interface DiscountableInterface
{
    /**
     * Discounts related to the model.
     *
     * @return MorphMany
     */
    public function discounts(): MorphMany;
}
