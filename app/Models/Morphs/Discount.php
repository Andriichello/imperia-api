<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use App\Models\Interfaces\LoggableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Traits\LoggableTrait;
use App\Models\Traits\SoftDeletableTrait;
use Carbon\Carbon;
use Database\Factories\Morphs\DiscountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class Discount.
 *
 * @property string $title
 * @property string|null $description
 * @property float|null $amount
 * @property float|null $percent
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Discountable[]|Collection $discountables
 *
 * @method static DiscountFactory factory(...$parameters)
 */
class Discount extends BaseModel implements
    SoftDeletableInterface,
    LoggableInterface
{
    use HasFactory;
    use SoftDeletableTrait;
    use LoggableTrait;

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

    /**
     * Array of column names changes of which should be logged.
     *
     * @var array
     */
    protected array $logFields = [
        'amount',
        'percent',
    ];

    /**
     * Related discountables.
     *
     * @return HasMany
     */
    public function discountables(): HasMany
    {
        return $this->hasMany(Discountable::class, 'discount_id', 'id');
    }
}
