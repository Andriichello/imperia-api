<?php

namespace App\Models\Categories;

use App\Models\BaseModel;
use App\Models\Discount;
use App\Models\Menu;
use App\Models\Product;
use App\Models\Service;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get available types of categories.
     *
     * @return string[]
     */
    public static function getTypes()
    {
        return [
            'menu',
            'product',
            'ticket',
            'service',
            'discount',
        ];
    }

    /**
     * Get model class for specified type.
     *
     * @var string $type
     * @return string|null
     */
    public static function getTypeModelClass($type)
    {
        switch ($type) {
            case 'menu':
                return Menu::class;
            case 'product':
                return Product::class;
            case 'ticket':
                return Ticket::class;
            case 'service':
                return Service::class;
            case 'discount':
                return Discount::class;
        }
        return null;
    }

    /**
     * Get table name for specified type.
     *
     * @var string $type
     * @return string|null
     */
    public static function getTypedTableName($type) {
        if (in_array($type, self::getTypes())) {
            return "{$type}_categories";
        }
        return null;
    }
}
