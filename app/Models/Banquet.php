<?php

namespace App\Models;

use App\Models\Morphs\Comment;
use App\Models\Orders\Order;
use App\Models\Traits\CommentableTrait;
use App\Models\Traits\SoftDeletableTrait;
use Carbon\Carbon;
use Database\Factories\BanquetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

/**
 * Class Banquet.
 *
 * @property string $title
 * @property string|null $description
 * @property string $advance_amount
 * @property Carbon $start_at
 * @property Carbon $end_at
 * @property int $state_id
 * @property int $creator_id
 * @property int $customer_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property int $total
 *
 * @property Order|null $order
 * @property User|null $creator
 * @property Customer|null $customer
 * @property BanquetState|null $state
 * @property Comment[]|Collection $comments
 *
 * @method static BanquetFactory factory(...$parameters)
 */
class Banquet extends BaseModel
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
        'title',
        'description',
        'advance_amount',
        'start_at',
        'end_at',
        'state_id',
        'creator_id',
        'customer_id',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'order',
        'state',
        'creator',
        'customer',
    ];

    /**
     * Array of relation names that should be deleted with the current model.
     *
     * @var array
     */
    protected array $cascadeDeletes = [
        'order',
    ];

    /**
     * Get the state associated with the model.
     *
     * @return BelongsTo
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(BanquetState::class, 'state_id', 'id');
    }

    /**
     * Order associated with the model.
     *
     * @return HasOne
     */
    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'banquet_id', 'id');
    }

    /**
     * Get the user associated with the model.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    /**
     * Get the customer associated with the model.
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * Accessor for total price of an order.
     *
     * @return float
     */
    public function getTotalAttribute(): float
    {
        return data_get($this->order, 'total', 0.0);
    }
}
