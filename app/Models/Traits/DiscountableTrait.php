<?php

namespace App\Models\Traits;

use App\Models\BaseModel;
use App\Models\Morphs\Discount;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait DiscountableTrait.
 *
 * @mixin BaseModel
 */
trait DiscountableTrait
{
    /**
     * Discounts related to the model.
     *
     * @return MorphMany
     */
    public function discounts(): MorphMany
    {
        return $this->morphMany(Discount::class, 'discountable', 'discountable_type', 'discountable_id', 'id');
    }
}
