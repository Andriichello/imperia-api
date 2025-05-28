<?php

namespace App\Models\Orders;

use App\Models\Banquet;
use App\Traits\StaticMethodsAccess;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class BanquetOrder.
 *
 * @property int $order_id
 * @property int $banquet_id
 *
 * @property Order $order
 * @property Banquet $banquet
 */
class BanquetOrder extends Pivot
{
    use StaticMethodsAccess;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'banquet_id',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'order',
        'banquet',
    ];

    /**
     * Products associated with the model.
     *
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Products associated with the model.
     *
     * @return BelongsTo
     */
    public function banquet(): BelongsTo
    {
        return $this->belongsTo(Banquet::class);
    }


    /**
     * Get the corresponding restaurant id.
     *
     * @return int|null
     */
    public function getRestaurantId(): ?int
    {
        return data_get($this->banquet, 'restaurant_id');
    }
}
