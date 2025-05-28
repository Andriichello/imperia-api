<?php

namespace App\Models;

use App\Models\Interfaces\AlterableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use App\Models\Traits\AlterableTrait;
use App\Models\Traits\SoftDeletableTrait;
use App\Queries\ProductVariantQueryBuilder;
use Carbon\Carbon;
use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

/**
 * Class ProductVariant.
 *
 * @property int $product_id
 * @property int|null $restaurant_id
 * @property float $price
 * @property string|null $weight
 * @property string|null $weight_unit
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Product $product
 *
 * @method static ProductVariantQueryBuilder query()
 * @method static ProductVariantFactory factory(...$parameters)
 */
class ProductVariant extends BaseModel implements
    AlterableInterface,
    SoftDeletableInterface
{
    use HasFactory;
    use AlterableTrait;
    use SoftDeletableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'price',
        'weight',
        'weight_unit',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'product',
        'alterations',
        'pendingAlterations',
        'performedAlterations',
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
     * Get the product associated with the model.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        /* @phpstan-ignore-next-line */
        return $this->belongsTo(Product::class)
            ->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class]);
    }

    /**
     * Accessor for the related customer id.
     *
     * @return int|null
     */
    public function getRestaurantIdAttribute(): ?int
    {
        return $this->product->restaurant_id;
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return ProductVariantQueryBuilder
     */
    public function newEloquentBuilder($query): ProductVariantQueryBuilder
    {
        return new ProductVariantQueryBuilder($query);
    }
}
