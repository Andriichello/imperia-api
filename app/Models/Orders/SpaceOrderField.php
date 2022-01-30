<?php

namespace App\Models\Orders;

use App\Models\BaseModel;
use App\Models\Space;
use App\Models\Traits\CommentableTrait;
use App\Models\Traits\SoftDeletableTrait;
use Carbon\Carbon;
use Database\Factories\SpaceOrderFieldFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class SpaceOrderField.
 *
 * @property int $order_id
 * @property int $space_id
 * @property Carbon $start_at
 * @property Carbon $end_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property float $total
 * @property Order $order
 * @property Space $space
 *
 * @method static SpaceOrderFieldFactory factory(...$parameters)
 */
class SpaceOrderField extends BaseModel
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
        'space_id',
        'start_at',
        'end_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var string[]
     */
    protected $appends = [
        'total',
        'duration',
    ];

    /**
     * Space associated with the model.
     *
     * @return BelongsTo
     */
    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class, 'product_id', 'id');
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
        return round($this->space->price, 2);
    }

    /**
     * Accessor for duration of the rental.
     *
     * @return int
     */
    public function getDurationAttribute(): int
    {
        return $this->end_at->diffInMinutes($this->start_at);
    }
}
