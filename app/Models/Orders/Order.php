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
use Carbon\Carbon;
use Database\Factories\Orders\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class Order.
 *
 * @property int $banquet_id
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property array $totals
 * @property Banquet $banquet
 * @property SpaceOrderField[]|Collection $spaces
 * @property TicketOrderField[]|Collection $tickets
 * @property ServiceOrderField[]|Collection $services
 * @property ProductOrderField[]|Collection $products
 *
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
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'banquet_id',
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
        'totals',
    ];

    /**
     * Banquet associated with the model.
     *
     * @return BelongsTo
     */
    public function banquet(): BelongsTo
    {
        return $this->belongsTo(Banquet::class, 'banquet_id', 'id');
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
     * Accessor for total prices of ordered items per type.
     *
     * @return array = [
     *   'all' => 'float',
     *   'spaces' => 'float',
     *   'tickets' => 'float',
     *   'products' => 'float',
     *   'services' => 'float'
     * ]
     */
    public function getTotalsAttribute(): array
    {
        $totals = collect(['spaces', 'tickets', 'products', 'services'])
            ->mapWithKeys(function ($relation) {
                return [$relation => round($this->$relation->sum('total'), 2)];
            });

        return $totals->put('all', round($totals->sum(), 2))->all();
    }

    /**
     * Determine if order can be edited.
     *
     * @return bool
     */
    public function canBeEdited(): bool
    {
        return $this->banquet->canBeEdited();
    }
}
