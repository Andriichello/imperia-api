<?php

namespace App\Models\Morphs;

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
     * @var string[]
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
}
