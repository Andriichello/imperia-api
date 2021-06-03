<?php

namespace App\Models\Orders;

use App\Constrainters\Implementations\DescriptionConstrainter;
use App\Constrainters\Implementations\IdentifierConstrainter;
use App\Constrainters\Implementations\ItemTypeConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use App\Models\BaseDeletableModel;
use App\Models\BaseModel;
use App\Models\Discount;
use App\Models\ImperiaMenu;
use App\Models\Product;
use App\Models\Service;
use App\Models\Space;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends BaseDeletableModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'orders';

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
        return [
            'space',
            'ticket',
            'service',
            'product',
        ];
    }

    /**
     * Get model class for specified type.
     *
     * @return string|null
     * @var string $type
     */
    public static function getTypeModelClass($type)
    {
        switch ($type) {
            case 'space':
                return Space::class;
            case 'ticket':
                return Ticket::class;
            case 'service':
                return Service::class;
            case 'product':
                return Product::class;
        }
        return null;
    }

    /**
     * Get model class for specified type.
     *
     * @return string|null
     * @var string $type
     */
    public static function getTypeOrderClass($type)
    {
        switch ($type) {
            case 'space':
                return SpaceOrder::class;
            case 'ticket':
                return TicketOrder::class;
            case 'service':
                return ServiceOrder::class;
            case 'product':
                return ProductOrder::class;
        }
        return null;
    }

    /**
     * Get model class for specified type.
     *
     * @return string|null
     * @var string $type
     */
    public static function getTypeOrderFieldClass($type)
    {
        switch ($type) {
            case 'space':
                return SpaceOrderField::class;
            case 'ticket':
                return TicketOrderField::class;
            case 'service':
                return ServiceOrderField::class;
            case 'product':
                return ProductOrderField::class;
        }
        return null;
    }

    /**
     * Get table name for specified type.
     *
     * @return string|null
     * @var string $type
     */
    public static function getTypedOrderTableName($type)
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
    public static function getTypedOrderFieldTableName($type)
    {
        if (in_array($type, self::getTypes())) {
            return "{$type}_order_fields";
        }
        return null;
    }
}
