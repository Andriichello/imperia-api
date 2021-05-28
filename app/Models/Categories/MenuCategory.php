<?php

namespace App\Models\Categories;

use App\Constrainters\Constrainter;
use App\Constrainters\Implementations\DescriptionConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use App\Models\BaseDeletableModel;
use App\Models\BaseModel;
use App\Models\ImperiaMenu;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MenuCategory extends BaseDeletableModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menu_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];

    protected $cascadeDeletes = ['menus'];

    /**
     * Get array of model's validation rules.
     *
     * @var bool $forInsert
     * @return array
     */
    public static function getValidationRules($forInsert = false) {
        return Category::getValidationRules($forInsert, 'menu');
    }

    /**
     * Get menus associated with the model.
     */
    public function menus()
    {
        return $this->hasMany(ImperiaMenu::class, 'menu_id', 'id');
    }
}
