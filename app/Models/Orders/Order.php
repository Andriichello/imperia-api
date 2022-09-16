<?php

namespace App\Models\Orders;

use App\Models\Banquet;
use App\Models\BaseModel;
use App\Models\Interfaces\CommentableInterface;
use App\Models\Interfaces\DiscountableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Traits\CommentableTrait;
use App\Models\Traits\DiscountableTrait;
use App\Models\Traits\SoftDeletableTrait;
use App\Models\User;
use App\Queries\OrderQueryBuilder;
use Carbon\Carbon;
use Database\Factories\Orders\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Class Order.
 *
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property array|null $totals
 * @property Banquet|null $banquet
 * @property Banquet[]|Collection $banquets
 * @property SpaceOrderField[]|Collection $spaces
 * @property TicketOrderField[]|Collection $tickets
 * @property ServiceOrderField[]|Collection $services
 * @property ProductOrderField[]|Collection $products
 *
 * @method static OrderQueryBuilder query()
 * @method static OrderFactory factory(...$parameters)
 */
class Order extends BaseModel implements
    SoftDeletableInterface,
    CommentableInterface,
    DiscountableInterface
{
    use HasFactory;
    use SoftDeletableTrait;
    use CommentableTrait;
    use DiscountableTrait;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'metadata' => '{}',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'banquet',
        'spaces',
        'tickets',
        'services',
        'products',
    ];

    /**
     * Array of relation names that should be deleted with the current model.
     *
     * @var array
     */
    protected array $cascadeDeletes = [
        'spaces',
        'tickets',
        'services',
        'products',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var string[]
     */
    protected $appends = [
        'type',
        'totals',
    ];

    /**
     * Banquets associated with the model.
     *
     * @return BelongsToMany
     */
    public function banquets(): BelongsToMany
    {
        return $this->belongsToMany(Banquet::class, 'banquet_order');
    }

    /**
     * Get banquet associated with the model.
     *
     * @return Order|null
     */
    public function getBanquetAttribute(): ?Order
    {
        return $this->banquets->first();
    }

    /**
     * Spaces associated with the model.
     *
     * @return HasMany
     */
    public function spaces(): HasMany
    {
        return $this->hasMany(SpaceOrderField::class, 'order_id', 'id');
    }

    /**
     * Tickets associated with the model.
     *
     * @return HasMany
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(TicketOrderField::class, 'order_id', 'id');
    }

    /**
     * Services associated with the model.
     *
     * @return HasMany
     */
    public function services(): HasMany
    {
        return $this->hasMany(ServiceOrderField::class, 'order_id', 'id');
    }

    /**
     * Products associated with the model.
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(ProductOrderField::class, 'order_id', 'id');
    }

    /**
     * Calculate total prices of ordered items per type.
     *
     * @return array = [
     *   'all' => 'float',
     *   'spaces' => 'float',
     *   'tickets' => 'float',
     *   'products' => 'float',
     *   'services' => 'float',
     *   'timestamp' => 'int'
     * ]
     */
    public function calculateTotals(): array
    {
        $totals = collect(['spaces', 'tickets', 'products', 'services'])
            ->mapWithKeys(function ($relation) {
                return [$relation => round($this->$relation->sum('total'), 2)];
            });
        $totals->put('all', round($totals->sum(), 2));
        $totals->put('timestamp', time());

        return $totals->all();
    }

    /**
     * Determine if given and stored totals are different.
     *
     * @param ?array $totals
     * @param bool $timestamp
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function areTotalsDifferent(?array $totals, bool $timestamp = false): bool
    {
        $new = $totals ?? [];
        $old = $this->totals ?? [];

        if (!$timestamp) {
            Arr::forget($new, 'timestamp');
            Arr::forget($old, 'timestamp');
        }

        return $new !== $old;
    }

    /**
     * Accessor for total prices of ordered items per type.
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
     * Mutator for total prices of ordered items per type.
     *
     * @param array|null $totals
     *
     * @return void
     */
    public function setTotalsAttribute(?array $totals): void
    {
        $this->setToJson('metadata', 'totals', $totals);
    }

    /**
     * Determine if order can be edited.
     *
     * @return bool
     */
    public function isEditable(): bool
    {
        return $this->banquet->isEditable();
    }

    /**
     * Determine if order can be edited by the given user.
     *
     * @param User|null $user
     *
     * @return bool
     */
    public function canBeEditedBy(?User $user): bool
    {
        return $this->banquet->canBeEditedBy($user);
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return OrderQueryBuilder
     */
    public function newEloquentBuilder($query): OrderQueryBuilder
    {
        return new OrderQueryBuilder($query);
    }
}
