<?php

namespace App\Models\Orders;

use App\Models\BaseModel;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketOrderField extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ticket_order_fields';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'ticket_id',
        'amount',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'ticket',
    ];

    /**
     * Get ticket associated with the model.
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id');
    }

    /**
     * Get order associated with the model.
     */
    public function order()
    {
        return $this->belongsTo(TicketOrder::class, 'order_id', 'id');
    }
}
