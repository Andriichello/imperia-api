<?php

namespace App\Models\Orders;

use App\Models\Banquet;
use App\Models\BaseModel;
use App\Models\Interfaces\CommentableInterface;
use App\Models\Interfaces\DiscountableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Space;
use App\Models\Traits\CommentableTrait;
use App\Models\Traits\DiscountableTrait;
use App\Models\Traits\SoftDeletableTrait;
use App\Queries\SpaceOrderFieldQueryBuilder;
use Carbon\Carbon;
use Database\Factories\Orders\SpaceOrderFieldFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

/**
 * Class SpaceOrderField.
 *
 * @property int $order_id
 * @property int $space_id
 * @property int $duration
 * @property Carbon|null $start_at
 * @property Carbon|null $end_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property float $total
 * @property Order $order
 * @property Space $space
 *
 * @method static SpaceOrderFieldQueryBuilder query()
 * @method static SpaceOrderFieldFactory factory(...$parameters)
 */
class SpaceOrderField extends BaseModel implements
    SoftDeletableInterface,
    CommentableInterface,
    DiscountableInterface
{
    use HasFactory;
    use SoftDeletableTrait;
    use CommentableTrait;
    use DiscountableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'order_id',
        'space_id',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var string[]
     */
    protected $appends = [
        'type',
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
        return $this->belongsTo(Space::class, 'space_id', 'id');
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
     * Accessor for the start date and time of the rental.
     *
     * @return Carbon|null
     */
    public function getStartAtAttribute(): ?Carbon
    {
        $this->loadBanquetDatesIfMissing();
        return $this->attributes['start_at'];
    }

    /**
     * Accessor for the end date and time of the rental.
     *
     * @return Carbon|null
     */
    public function getEndAtAttribute(): ?Carbon
    {
        $this->loadBanquetDatesIfMissing();
        return $this->attributes['end_at'];
    }

    /**
     * Accessor for duration of the rental.
     *
     * @return int
     */
    public function getDurationAttribute(): int
    {
        $beg = $this->start_at;
        $end = $this->end_at;

        return $beg && $end ? $end->diffInMinutes($beg) : 0;
    }

    /**
     * Load banquet start and end dates.
     *
     * @return void
     */
    public function loadBanquetDatesIfMissing()
    {
        if (empty($this->attributes)) {
            return;
        }

        $this->loadBanquetDates();
    }


    /**
     * Load banquet start and end dates.
     *
     * @return void
     */
    public function loadBanquetDates()
    {
        $dates = Banquet::query()
            ->whereHas('order', fn(Builder $query) => $query->where('id', $this->order_id))
            ->first(['start_at', 'end_at']);

        $startAt = data_get($dates, 'start_at');
        $endAt = data_get($dates, 'end_at');

        $this->attributes['start_at'] = $startAt ? new Carbon($startAt) : $startAt;
        $this->attributes['end_at'] = $endAt ? new Carbon($endAt) : $endAt;
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return SpaceOrderFieldQueryBuilder
     */
    public function newEloquentBuilder($query): SpaceOrderFieldQueryBuilder
    {
        return new SpaceOrderFieldQueryBuilder($query);
    }
}
