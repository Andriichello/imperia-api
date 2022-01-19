<?php

namespace App\Models;

use App\Events\BanquetCreated;
use App\Events\BanquetUpdated;
use App\Models\Orders\Order;
use App\Models\Orders\ProductOrder;
use App\Models\Orders\ServiceOrder;
use App\Models\Orders\SpaceOrder;
use App\Models\Orders\TicketOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class Banquet extends BaseDeletableModel
{
    use HasFactory;

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
    ];

    protected $cascadeDeletes = ['ticketOrder', 'spaceOrder', 'productOrder', 'serviceOrder'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    public $appends = ['comments', 'total'];

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
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'total' => 'float',
        'advance_amount' => 'float',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $dispatchesEvents = [
        'saved' => BanquetCreated::class,
        'updated' => BanquetUpdated::class,
    ];

    protected static function boot()
    {
        parent::boot();
    }


    /**
     * Get total price of all orders.
     *
     * @return float
     */
    public function getTotalAttribute()
    {
        $total = 0.0;
        foreach (self::getOrderRelationshipNames() as $orderRelationship) {
            if (empty($this->$orderRelationship)) {
                continue;
            }
            $total += $this->$orderRelationship->total ?? 0.0;
        }
        return round($total, 2);
    }

    /**
     * Get comments that are pinned to the banquet.
     *
     * @return Collection
     */
    public function getCommentsAttribute()
    {
        return $this->containerComments;
    }

    /**
     * Get array of model's order column names.
     *
     * @return array
     */
    public static function getOrderColumnNames() {
        $orderColumnNames = [];
        foreach (Order::getModelTypes() as $orderType) {
            $orderColumnNames[$orderType] = $orderType . '_order';
        }
        return $orderColumnNames;
    }

    /**
     * Get array of model's order column names.
     *
     * @return array
     */
    public static function getOrderRelationshipNames() {
        $orderRelationshipNames = [];
        foreach (Order::getModelTypes() as $orderType) {
            $orderRelationshipNames[$orderType] = $orderType . 'Order';
        }
        return $orderRelationshipNames;
    }


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
        return $this->belongsTo(ImperiaUser::class, 'creator_id', 'id')
            ->without('role')
            ->select(['id', 'name']);
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
