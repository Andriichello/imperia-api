<?php

namespace App\Models;

use App\Constrainters\Implementations\DescriptionConstrainter;
use App\Constrainters\Implementations\IdentifierConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use App\Models\Categories\MenuCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImperiaMenu extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'imperia_menus';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'period_id',
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
            'period_id' => IdentifierConstrainter::getRules(false),
            'category_id' => IdentifierConstrainter::getRules($forInsert),
        ];
    }

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'period',
        'category',
        'products',
    ];

    /**
     * Get the period associated with the model.
     */
    public function period()
    {
        return $this->belongsTo(Period::class, 'period_id', 'id');
    }

    /**
     * Get the category associated with the model.
     */
    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'category_id', 'id');
    }

    /**
     * Get the products associated with the model.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'menu_id', 'id');
    }

}
