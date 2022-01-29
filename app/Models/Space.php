<?php

namespace App\Models;

use App\Models\Orders\SpaceOrderField;
use App\Models\Traits\SoftDeletableTrait;
use Carbon\Carbon;
use Database\Factories\SpaceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

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
 * @property SpaceOrderField[]|Collection $intervals
 *
 * @method static SpaceFactory factory(...$parameters)
 */
class Space extends BaseModel
{
    use HasFactory;
    use SoftDeletableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
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

    /**
     * Get all intervals for which space is reserved.
     *
     * @return HasMany
     */
    public function intervals(): HasMany
    {
        return $this->hasMany(SpaceOrderField::class, 'space_id', 'id')
            ->without('space')
            ->with('banquet')
            ->select(['order_id', 'space_id', 'beg_datetime', 'end_datetime']);
    }
}
