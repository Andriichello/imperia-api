<?php

namespace App\Models\Morphs;

use App\Events\DiscountCreated;
use App\Events\DiscountUpdated;
use App\Models\BaseModel;
use Carbon\Carbon;

/**
 * Class Discount.
 *
 * @property string $title
 * @property string|null $description
 * @property float|null $amount
 * @property float|null $percent
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Discount extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'amount',
        'percent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'float',
        'percent' => 'float',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'saved' => DiscountCreated::class,
        'updated' => DiscountUpdated::class,
    ];
}
