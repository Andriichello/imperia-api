<?php

namespace App\Models;

use App\Enums\BanquetState;
use App\Models\Interfaces\CommentableInterface;
use App\Models\Interfaces\LoggableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Morphs\Comment;
use App\Models\Orders\Order;
use App\Models\Traits\CommentableTrait;
use App\Models\Traits\LoggableTrait;
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
 * @property float $advance_amount
 * @property Carbon $start_at
 * @property Carbon $end_at
 * @property Carbon|null $paid_at
 * @property string $state
 * @property int|null $order_id
 * @property int $creator_id
 * @property int $customer_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property float $total
 *
 * @property Order|null $order
 * @property User|null $creator
 * @property Customer|null $customer
 * @property Comment[]|Collection $comments
 *
 * @method static BanquetFactory factory(...$parameters)
 */
class Banquet extends BaseModel implements
    SoftDeletableInterface,
    CommentableInterface,
    LoggableInterface
{
    use HasFactory;
    use SoftDeletableTrait;
    use CommentableTrait;
    use LoggableTrait;

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
        'paid_at',
        'state',
        'creator_id',
        'customer_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'advance_amount' => 'float',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'order',
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
     * Array of column names changes of which should be logged.
     *
     * @var array
     */
    protected array $logFields = [
        'state',
    ];

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
     * Accessor for total price of banquet.
     *
     * @return int|null
     */
    public function getTotalAttribute(): ?int
    {
        return data_get($this->order, 'total', 0.0);
    }

    /**
     * Accessor for id of the order.
     *
     * @return int|null
     */
    public function getOrderIdAttribute(): ?int
    {
        return $this->order()->pluck('id')->first();
    }

    /**
     * Determine if banquet can be edited.
     *
     * @return bool
     */
    public function canBeEdited(): bool
    {
        return $this->state !== BanquetState::Completed
            && $this->state !== BanquetState::Cancelled;
    }
}
