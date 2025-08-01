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
 * @property float|null $paid_amount
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
 * @property float $tickets_total
 * @property float|null $actual_total
 * @property string|null $advance_amount_payment_method
 * @property bool|null $is_birthday_club
 * @property bool|null $with_photographer
 * @property int|null $adults_amount
 * @property float|null $adult_ticket_price
 * @property int|null $children_amount
 * @property float|null $child_ticket_price
 * @property int[]|null $children_amounts
 * @property float[]|null $child_ticket_prices
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
        'advance_amount' => 0,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'advance_amount',
        'paid_amount',
        'start_at',
        'end_at',
        'paid_at',
        'state',
        'creator_id',
        'customer_id',
        'restaurant_id',
        /** Dynamic */
        'actual_total',
        'is_birthday_club',
        'with_photographer',
        'advance_amount_payment_method',
        'adults_amount',
        'adult_ticket_price',
        'children_amount',
        'child_ticket_price',
        'children_amounts',
        'child_ticket_prices',
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
        'totals',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var string[]
     */
    protected $appends = [
        'order_id',
        'totals',
    ];

    /**
     * Order associated with the model.
     *
     * @return HasOne
     */
    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    /**
     * Get id of order associated with the model.
     *
     * @return int|null
     */
    public function getOrderIdAttribute(): ?int
    {
        return $this->order?->id;
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
     * @return null|array{
     *   all: float,
     *   spaces: float,
     *   tickets: float,
     *   products: float,
     *   services: float,
     *   timestamp: int
     * }
     */
    public function getTotalsAttribute(): ?array
    {
        return $this->getFromJson('metadata', 'totals');
    }

    /**
     * Mutator for the array of last stored order totals.
     *
     * @param null|array{
     *   all: float,
     *   spaces: float,
     *   tickets: float,
     *   products: float,
     *   services: float,
     *   timestamp: int
     * } $totals
     *
     * @return void
     */
    public function setTotalsAttribute(?array $totals): void
    {
        $this->setToJson('metadata', 'totals', $totals);
    }

    /**
     * Accessor for the total of the tickets, which were
     * set to the banquet's metadata.
     *
     * @return float
     */
    public function getTicketsTotalAttribute(): float
    {
        $total = 0.0;

        if ($this->adults_amount && $this->adult_ticket_price) {
            $total += $this->adults_amount * $this->adult_ticket_price;
        }

        if ($this->children_amount && $this->child_ticket_price) {
            $total += $this->children_amount * $this->child_ticket_price;
        }

        return $total;
    }

    /**
     * Accessor for the actual total amount.
     *
     * @return ?float
     */
    public function getActualTotalAttribute(): ?float
    {
        $value = $this->getFromJson('metadata', 'actual_total');

        return $value === null ? null : (float) $value;
    }

    /**
     * Mutator for the actual total amount.
     *
     * @param float|null $actualTotal
     *
     * @return void
     */
    public function setActualTotalAttribute(?float $actualTotal): void
    {
        $this->setToJson('metadata', 'actual_total', $actualTotal);
    }

    /**
     * Accessor for the advance amount payment method.
     *
     * @return ?string
     */
    public function getAdvanceAmountPaymentMethodAttribute(): ?string
    {
        return $this->getFromJson('metadata', 'advance_amount_payment_method');
    }

    /**
     * Mutator for the advance amount payment method.
     *
     * @param string|null $paymentType
     *
     * @return void
     */
    public function setAdvanceAmountPaymentMethodAttribute(?string $paymentType): void
    {
        $this->setToJson('metadata', 'advance_amount_payment_method', $paymentType);
    }

    /**
     * Accessor for the is birthday club attribute.
     *
     * @return ?bool
     */
    public function getIsBirthdayClubAttribute(): ?bool
    {
        $value = $this->getFromJson('metadata', 'is_birthday_club');

        return $value === null ? null : (bool) $value;
    }

    /**
     * Mutator for the is birthday club attribute.
     *
     * @param bool|null $isBirthdayClub
     *
     * @return void
     */
    public function setIsBirthdayClubAttribute(?bool $isBirthdayClub): void
    {
        $this->setToJson('metadata', 'is_birthday_club', $isBirthdayClub);
    }

    /**
     * Accessor for the with photographer attribute.
     *
     * @return ?bool
     */
    public function getWithPhotographerAttribute(): ?bool
    {
        $value = $this->getFromJson('metadata', 'with_photographer');

        return $value === null ? null : (bool) $value;
    }

    /**
     * Mutator for the with photographer attribute.
     *
     * @param bool|null $withPhotographer
     *
     * @return void
     */
    public function setWithPhotographerAttribute(?bool $withPhotographer): void
    {
        $this->setToJson('metadata', 'with_photographer', $withPhotographer);
    }

    /**
     * Accessor for the adults amount attribute.
     *
     * @return ?int
     */
    public function getAdultsAmountAttribute(): ?int
    {
        $value = $this->getFromJson('metadata', 'adults_amount');

        return $value === null ? null : (int) $value;
    }

    /**
     * Mutator for the adults amount attribute.
     *
     * @param int|null $adultsAmount
     *
     * @return void
     */
    public function setAdultsAmountAttribute(?int $adultsAmount): void
    {
        $this->setToJson('metadata', 'adults_amount', $adultsAmount);
    }

    /**
     * Accessor for the adult ticket price attribute.
     *
     * @return ?float
     */
    public function getAdultTicketPriceAttribute(): ?float
    {
        $value = $this->getFromJson('metadata', 'adult_ticket_price');

        return $value === null ? null : (float) $value;
    }

    /**
     * Mutator for the adult ticket price attribute.
     *
     * @param float|null $adultTicketPrice
     *
     * @return void
     */
    public function setAdultTicketPriceAttribute(?float $adultTicketPrice): void
    {
        $this->setToJson('metadata', 'adult_ticket_price', $adultTicketPrice);
    }

    /**
     * Accessor for the children amount attribute.
     *
     * @return ?int
     */
    public function getChildrenAmountAttribute(): ?int
    {
        $value = $this->getFromJson('metadata', 'children_amount');

        return $value === null ? null : (int) $value;
    }

    /**
     * Mutator for the children amount attribute.
     *
     * @param int|null $childrenAmount
     *
     * @return void
     */
    public function setChildrenAmountAttribute(?int $childrenAmount): void
    {
        $this->setToJson('metadata', 'children_amount', $childrenAmount);
    }

    /**
     * Accessor for the child ticket price attribute.
     *
     * @return ?float
     */
    public function getChildTicketPriceAttribute(): ?float
    {
        $value = $this->getFromJson('metadata', 'child_ticket_price');

        return $value === null ? null : (float) $value;
    }

    /**
     * Mutator for the child ticket price attribute.
     *
     * @param float|null $childTicketPrice
     *
     * @return void
     */
    public function setChildTicketPriceAttribute(?float $childTicketPrice): void
    {
        $this->setToJson('metadata', 'child_ticket_price', $childTicketPrice);
    }

    /**
     * Accessor for the children amounts attribute.
     *
     * @return int[]|null
     */
    public function getChildrenAmountsAttribute(): ?array
    {
        $value = $this->getFromJson('metadata', 'children_amounts');

        return $value === null ? null : (array) $value;
    }

    /**
     * Mutator for the children amounts attribute.
     *
     * @param int[]|null $childrenAmounts
     *
     * @return void
     */
    public function setChildrenAmountsAttribute(?array $childrenAmounts): void
    {
        $this->setToJson('metadata', 'children_amounts', $childrenAmounts);
    }

    /**
     * Accessor for the child ticket price attribute.
     *
     * @return float[]|null
     */
    public function getChildTicketPricesAttribute(): ?array
    {
        $value = $this->getFromJson('metadata', 'child_ticket_prices');

        return $value === null ? null : (array) $value;
    }

    /**
     * Mutator for the child ticket price attribute.
     *
     * @param float[]|null $childTicketPrices
     *
     * @return void
     */
    public function setChildTicketPricesAttribute(?array $childTicketPrices): void
    {
        $this->setToJson('metadata', 'child_ticket_prices', $childTicketPrices);
    }

    /**
     * Accessor for the banquet's order invoice url.
     *
     * @param User $asUser
     * @return string|null
     */
    public function getInvoiceUrl(User $asUser): ?string
    {
        return $this->order?->getInvoiceUrl($asUser);
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
        // return $this->state !== BanquetState::Completed;
        return true;
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
                && $this->isInState(BanquetState::New);
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
