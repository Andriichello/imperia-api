<?php

namespace App\Models\Orders;

use App\Models\BaseModel;
use App\Models\Service;
use App\Models\Traits\SoftDeletableTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ServiceOrderField.
 *
 * @property int $order_id
 * @property int $service_id
 * @property int $amount
 * @property int $duration
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property float $total
 * @property Order $order
 * @property Service $service
 *
 * @method static ServiceOrderField factory(...$parameters)
 */
class ServiceOrderField extends BaseModel
{
    use HasFactory;
    use SoftDeletableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'service_id',
        'amount',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'total',
    ];

    /**
     * Service associated with the model.
     *
     * @return BelongsTo
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'product_id', 'id');
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
        $total = $this->service->once_paid_price +
            $this->service->hourly_paid_price * $this->duration;
        return round($total * $this->amount, 2);
    }
}
