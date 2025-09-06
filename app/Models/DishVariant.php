<?php

namespace App\Models;

use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use App\Models\Traits\SoftDeletableTrait;
use App\Queries\DishVariantQueryBuilder;
use Carbon\Carbon;
use Database\Factories\DishVariantFactory;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProductVariant.
 *
 * @property int $dish_id
 * @property float $price
 * @property string|null $weight
 * @property string|null $weight_unit
 * @property integer|null $calories
 * @property integer|null $preparation_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Dish $dish
 *
 * @method static DishVariantQueryBuilder query()
 * @method static DishVariantFactory factory(...$parameters)
 */
class DishVariant extends BaseModel implements
    SoftDeletableInterface
{
    use HasFactory;
    use SoftDeletableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dish_id',
        'price',
        'weight',
        'weight_unit',
        'calories',
        'preparation_time',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'dish',
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
     * Get the dish associated with the model.
     *
     * @return BelongsTo
     */
    public function dish(): BelongsTo
    {
        /* @phpstan-ignore-next-line */
        return $this->belongsTo(Dish::class)
            ->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class]);
    }

    /**
     * Get the corresponding restaurant id.
     *
     * @return int|null
     */
    public function getRestaurantId(): ?int
    {
        return $this->dish->getRestaurantId();
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return DishVariantQueryBuilder
     */
    public function newEloquentBuilder($query): DishVariantQueryBuilder
    {
        return new DishVariantQueryBuilder($query);
    }
}
