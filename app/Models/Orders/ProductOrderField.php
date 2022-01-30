<?php

namespace App\Models\Orders;

use App\Models\BaseModel;
use App\Models\Product;
use App\Models\Traits\CommentableTrait;
use App\Models\Traits\SoftDeletableTrait;
use Carbon\Carbon;
use Database\Factories\ProductOrderFieldFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProductOrderField.
 *
 * @property int $order_id
 * @property int $product_id
 * @property int $amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property float $total
 * @property Order $order
 * @property Product $product
 *
 * @method static ProductOrderFieldFactory factory(...$parameters)
 */
class ProductOrderField extends BaseModel
{
    use HasFactory;
    use SoftDeletableTrait;
    use CommentableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'amount',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var string[]
     */
    protected $appends = [
        'total',
    ];

    /**
     * Product associated with the model.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Order associated with the model.
     *
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * Accessor for total price of all fields within the model.
     *
     * @return float
     */
    public function getTotalAttribute(): float
    {
        return round($this->product->price * $this->amount, 2);
    }
}
