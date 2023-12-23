<?php

namespace App\Models\Orders;

use App\Models\BaseModel;
use App\Models\Interfaces\CommentableInterface;
use App\Models\Interfaces\DiscountableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use App\Models\Traits\CommentableTrait;
use App\Models\Traits\DiscountableTrait;
use App\Models\Traits\SoftDeletableTrait;
use Carbon\Carbon;
use Database\Factories\Orders\ProductOrderFieldFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProductOrderField.
 *
 * @property int $order_id
 * @property int $product_id
 * @property int|null $variant_id
 * @property int $amount
 * @property string|null $batch 4 characters
 * @property string|null $serve_at H:i time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property float $price
 * @property float $total
 * @property Order $order
 * @property Product $product
 * @property ProductVariant|null $variant
 *
 * @method static ProductOrderFieldFactory factory(...$parameters)
 */
class ProductOrderField extends BaseModel implements
    SoftDeletableInterface,
    CommentableInterface,
    DiscountableInterface
{
    use HasFactory;
    use SoftDeletableTrait;
    use CommentableTrait;
    use DiscountableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'amount',
        'serve_at',
        'batch',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var string[]
     */
    protected $appends = [
        'type',
        'total',
    ];

    /**
     * Product associated with the model.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        /* @phpstan-ignore-next-line */
        return $this->belongsTo(Product::class, 'product_id', 'id')
            ->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class]);
    }

    /**
     * Product variant associated with the model.
     *
     * @return BelongsTo
     */
    public function variant(): BelongsTo
    {
        /* @phpstan-ignore-next-line */
        return $this->belongsTo(ProductVariant::class, 'variant_id', 'id')
            ->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class]);
    }

    /**
     * Order associated with the model.
     *
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        /* @phpstan-ignore-next-line */
        return $this->belongsTo(Order::class, 'order_id', 'id')
            ->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class]);
    }

    /**
     * Accessor for price of the field.
     *
     * @return float
     */
    public function getPriceAttribute(): float
    {
        return $this->variant_id && $this->variant
            ? $this->variant->price
            : $this->product->price;
    }

    /**
     * Accessor for total price of the field.
     *
     * @return float
     */
    public function getTotalAttribute(): float
    {
        return round($this->price * $this->amount, 2);
    }

    /**
     * Accessor for the serve at time of the field.
     *
     * @return string|null
     */
    public function getServeAtAttribute(): ?string
    {
        $serveAt = data_get($this->attributes, 'serve_at');

        return empty($serveAt)
            ? null : (new Carbon($serveAt))->format('H:i');
    }
}
