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
     * @var string[]
     */
    protected $fillable = [
        'order_id',
        'banquet_id',
    ];

    /**
     * List of relationship names.
     *
     * @var array
     */
    protected array $relationships = [
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
}
