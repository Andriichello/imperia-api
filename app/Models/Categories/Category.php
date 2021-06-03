<?php

namespace App\Models\Categories;

use App\Constrainters\Implementations\DescriptionConstrainter;
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

class Category extends BaseDeletableModel
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
            'space',
            'ticket',
            'service',
            'discount',
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
            case 'menu':
                return ImperiaMenu::class;
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
     * Get model class for specified type.
     *
     * @return string|null
     * @var string $type
     */
    public static function getTypeCategoryClass($type)
    {
        switch ($type) {
            case 'space':
                return SpaceCategory::class;
            case 'menu':
                return MenuCategory::class;
            case 'product':
                return ProductCategory::class;
            case 'ticket':
                return TicketCategory::class;
            case 'service':
                return ServiceCategory::class;
            case 'discount':
                return DiscountCategory::class;
        }
        return null;
    }

    /**
     * Get table name for specified type.
     *
     * @return string|null
     * @var string $type
     */
    public static function getTypedTableName($type)
    {
        if (in_array($type, self::getTypes())) {
            return "{$type}_categories";
        }
        return null;
    }
}
