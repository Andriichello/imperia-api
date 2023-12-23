<?php

namespace App\Models\Orders;

use App\Models\BaseModel;
use App\Models\Interfaces\CommentableInterface;
use App\Models\Interfaces\DiscountableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use App\Models\Ticket;
use App\Models\Traits\CommentableTrait;
use App\Models\Traits\DiscountableTrait;
use App\Models\Traits\SoftDeletableTrait;
use Carbon\Carbon;
use Database\Factories\Orders\TicketOrderFieldFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ServiceOrderField.
 *
 * @property int $order_id
 * @property int $ticket_id
 * @property int $amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property float $total
 * @property Order $order
 * @property Ticket $ticket
 *
 * @method static TicketOrderFieldFactory factory(...$parameters)
 */
class TicketOrderField extends BaseModel implements
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
        'ticket_id',
        'amount',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var string[]
     */
    protected $appends = [
        'type',
        'total',
    ];

    /**
     * Ticket associated with the model.
     *
     * @return BelongsTo
     */
    public function ticket(): BelongsTo
    {
        /* @phpstan-ignore-next-line */
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id')
            ->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class]);
    }

    /**
     * Order associated with the model.
     *
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        /* @phpstan-ignore-next-line */
        return $this->belongsTo(Order::class, 'order_id', 'id')
            ->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class]);
    }

    /**
     * Accessor for total price of all fields within the model.
     *
     * @return float
     */
    public function getTotalAttribute(): float
    {
        return round($this->ticket->price * $this->amount, 2);
    }
}
