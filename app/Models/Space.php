<?php

namespace App\Models;

use App\Models\Traits\CategorizableTrait;
use App\Models\Traits\SoftDeletableTrait;
use Carbon\Carbon;
use Database\Factories\SpaceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Space.
 *
 * @property string $title
 * @property string|null $description
 * @property float $price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static SpaceFactory factory(...$parameters)
 */
class Space extends BaseModel
{
    use HasFactory;
    use SoftDeletableTrait;
    use CategorizableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title',
        'description',
        'number',
        'floor',
        'price',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $relations = [
        'intervals',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
    ];
}
