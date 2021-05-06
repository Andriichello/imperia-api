<?php

namespace App\Models\Categories;

use App\Models\BaseModel;
use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MenuCategory extends BaseModel
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

    /**
     * Get menus associated with the model.
     */
    public function menus()
    {
        return $this->hasMany(Menu::class, 'menu_id', 'id');
    }
}
