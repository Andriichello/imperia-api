<?php

namespace App\Models\Categories;

use App\Models\BaseDeletableModel;
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
        return array_keys(self::getTypeModels());
    }

    /**
     * Get available type models.
     *
     * @return string[]
     */
    public static function getTypeModels()
    {
        return [
            'menu' => MenuCategory::class,
            'product' => ProductCategory::class,
            'space' => SpaceCategory::class,
            'ticket' => TicketCategory::class,
            'service' => ServiceCategory::class,
            'discount' => DiscountCategory::class,
        ];
    }

    /**
     * Get table name for specified type.
     *
     * @return string|null
     * @var string $type
     */
    public static function getTypeTableName($type)
    {
        if (in_array($type, self::getTypes())) {
            return "{$type}_categories";
        }
        return null;
    }
}
