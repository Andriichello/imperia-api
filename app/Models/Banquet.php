<?php

namespace App\Models;

use App\Models\Orders\ProductOrder;
use App\Models\Orders\ServiceOrder;
use App\Models\Orders\SpaceOrder;
use App\Models\Orders\SpaceOrderField;
use App\Models\Orders\TicketOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Banquet extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'banquets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'advance_amount',
        'beg_datetime',
        'end_datetime',
        'state_id',
        'creator_id',
        'customer_id',
        'spaceOrder',
        'ticketOrder',
        'serviceOrder',
        'productOrder',
        'comments',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    public $appends = ['comments'];

    public function getCommentsAttribute()
    {
        return $this->containerComments;
    }

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'state',
        'creator',
        'customer',
        'spaceOrder',
        'ticketOrder',
        'serviceOrder',
        'productOrder',
    ];

    /**
     * Get the state associated with the model.
     */
    public function state()
    {
        return $this->belongsTo(BanquetState::class, 'state_id', 'id');
    }

    /**
     * Get the user associated with the model.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    /**
     * Get the customer associated with the model.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    /**
     * Get the space order associated with the model.
     */
    public function spaceOrder()
    {
        return $this->hasOne(SpaceOrder::class, 'banquet_id', 'id');
    }

    /**
     * Get the ticket order associated with the model.
     */
    public function ticketOrder()
    {
        return $this->hasOne(TicketOrder::class, 'banquet_id', 'id');
    }

    /**
     * Get the ticket order associated with the model.
     */
    public function serviceOrder()
    {
        return $this->hasOne(ServiceOrder::class, 'banquet_id', 'id');
    }

    /**
     * Get the product order associated with the model.
     */
    public function productOrder()
    {
        return $this->hasOne(ProductOrder::class, 'banquet_id', 'id');
    }
}
