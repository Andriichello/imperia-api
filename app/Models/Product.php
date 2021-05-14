<?php

namespace App\Models;

use App\Constrainters\Implementations\AmountConstrainter;
use App\Constrainters\Implementations\DescriptionConstrainter;
use App\Constrainters\Implementations\IdentifierConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use App\Constrainters\Implementations\PriceConstrainter;
use App\Models\Categories\ProductCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'weight',
        'menu_id',
        'category_id',
    ];


    /**
     * Get array of model's validation rules.
     *
     * @return array
     * @var bool $forInsert
     */
    public static function getValidationRules($forInsert = false)
    {
        return [
            'name' => NameConstrainter::getRules($forInsert),
            'description' => DescriptionConstrainter::getRules(false),
            'price' => PriceConstrainter::getRules($forInsert),
            'weight' => AmountConstrainter::getRules($forInsert),
            'menu_id' => IdentifierConstrainter::getRules($forInsert),
            'category_id' => IdentifierConstrainter::getRules($forInsert),
        ];
    }

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'category',
    ];

    /**
     * Get the menu associated with the model.
     */
    public function menu()
    {
        return $this->belongsTo(ImperiaMenu::class, 'menu_id', 'id');
    }

    /**
     * Get the category associated with the model.
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id', 'id');
    }
}
