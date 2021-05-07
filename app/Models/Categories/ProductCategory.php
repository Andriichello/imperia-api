<?php

namespace App\Models\Categories;

use App\Constrainters\Implementations\DescriptionConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use App\Models\BaseModel;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductCategory extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_categories';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

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
     * Get array of model's validation rules.
     *
     * @var bool $forInsert
     * @return array
     */
    public static function getValidationRules($forInsert = false) {
        return Category::getValidationRules($forInsert, 'product');
    }

    /**
     * Get products associated with the model.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }
}
