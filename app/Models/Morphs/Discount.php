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
 * @property string|null $target
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
        'target',
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
     * List of relationship names.
     *
     * @var array
     */
    protected array $relationships = [
        'discountables',
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

    /**
     * Target property mutator.
     *
     * @param string|null $target
     *
     * @return void
     */
    public function setTargetAttribute(?string $target): void
    {
        $this->attributes['target'] = $target ? slugClass($target) : null;
    }

    /**
     * Get request validation rules for creating or updating discounts.
     *
     * @param string|null $prefix
     *
     * @return array
     */
    public static function rulesForAttaching(?string $prefix = null): array
    {
        return [
            $prefix . 'discounts' => [
                'sometimes',
                'array',
            ],
            $prefix . 'discounts.*.id' => [
                'sometimes',
                'integer',
            ],
            $prefix . 'discounts.*.discount_id' => [
                'required',
                'integer',
                'exists:discounts,id'
            ],
            $prefix . 'discounts.*.discountable_id' => [
                'sometimes',
                'integer',
            ],
            $prefix . 'discounts.*.discountable_type' => [
                'sometimes',
                'string',
            ],
        ];
    }
}
