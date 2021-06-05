<?php

namespace App\Models\Orders;

use App\Models\BaseDeletableModel;
use App\Models\Product;
use App\Models\Service;
use App\Models\Space;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends BaseDeletableModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'banquet_id',
        'discount_id',
    ];

    /**
     * Get available types of categories.
     *
     * @return string[]
     */
    public static function getTypes()
    {
        return array_keys(self::getTypeModels());
    }

    /**
     * Get available types of categories.
     *
     * @return string[]
     */
    public static function getTypeModels()
    {
        return [
            'space' => SpaceOrder::class,
            'ticket' => TicketOrder::class,
            'service' => ServiceOrder::class,
            'product' => ProductOrder::class,
        ];
    }

    /**
     * Get available types of categories.
     *
     * @return string[]
     */
    public static function getTypeFields()
    {
        return [
            'space' => SpaceOrderField::class,
            'ticket' => TicketOrderField::class,
            'service' => ServiceOrderField::class,
            'product' => ProductOrderField::class,
        ];
    }

    /**
     * Get table name for specified type.
     *
     * @return string|null
     * @var string $type
     */
    public static function getTypeModelTableName($type)
    {
        if (in_array($type, self::getTypes())) {
            return "{$type}_orders";
        }
        return null;
    }

    /**
     * Get table name for specified type.
     *
     * @return string|null
     * @var string $type
     */
    public static function getTypeFieldTableName($type)
    {
        if (in_array($type, self::getTypes())) {
            return "{$type}_order_fields";
        }
        return null;
    }
}
