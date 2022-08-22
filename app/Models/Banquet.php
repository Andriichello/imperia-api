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
use App\Queries\BanquetQueryBuilder;
use Carbon\Carbon;
use Database\Factories\BanquetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
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
 * @property int|null $restaurant_id
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property array|null $totals
 *
 * @property Order|null $order
 * @property User|null $creator
 * @property Customer|null $customer
 * @property Comment[]|Collection $comments
 * @property Restaurant|null $restaurant
 *
 * @method static BanquetQueryBuilder query()
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
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'metadata' => '{}',
    ];

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
        'restaurant_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'paid_at' => 'datetime',
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
        'comments',
        'restaurant',
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
     * Get the restaurant associated with the model.
     *
     * @return BelongsTo
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id', 'id');
    }

    /**
     * Accessor for the array of last stored order totals.
     *
     * @return ?array = [
     *   'all' => 'float',
     *   'spaces' => 'float',
     *   'tickets' => 'float',
     *   'products' => 'float',
     *   'services' => 'float',
     *   'timestamp' => 'int'
     * ]
     */
    public function getTotalsAttribute(): ?array
    {
        return $this->getFromJson('metadata', 'totals');
    }

    /**
     * Mutator for the array of last stored order totals.
     *
     * @param ?array $totals = [
     *   'all' => 'float',
     *   'spaces' => 'float',
     *   'tickets' => 'float',
     *   'products' => 'float',
     *   'services' => 'float',
     *   'timestamp' => 'int'
     * ]
     *
     * @return void
     */
    public function setTotalsAttribute(?array $totals): void
    {
        $this->setToJson('metadata', 'totals', $totals);
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
     * Determine if banquet is in one of given states.
     *
     * @param string ...$states
     *
     * @return bool
     */
    public function isInState(string ...$states): bool
    {
        return in_array($this->state, $states);
    }

    /**
     * Determine if banquet can be edited.
     *
     * @return bool
     */
    public function isEditable(): bool
    {
        return $this->state !== BanquetState::Completed;
    }

    /**
     * Determine if user is a creator of the banquet.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isCreator(User $user): bool
    {
        return $user->id === $this->creator_id;
    }

    /**
     * Determine if user is a customer for the banquet.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isCustomer(User $user): bool
    {
        return $user->customer_id === $this->customer_id;
    }

    /**
     * Determine if banquet can be edited by the given user.
     *
     * @param User|null $user
     *
     * @return bool
     */
    public function canBeEditedBy(?User $user): bool
    {
        if ($user === null || !$this->isEditable()) {
            return false;
        }

        if ($user->isStaff()) {
            return true;
        }

        if ($user->isCustomer()) {
            return $this->isCustomer($user)
                && $this->isInState(BanquetState::Draft, BanquetState::New);
        }

        return false;
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return BanquetQueryBuilder
     */
    public function newEloquentBuilder($query): BanquetQueryBuilder
    {
        return new BanquetQueryBuilder($query);
    }
}
