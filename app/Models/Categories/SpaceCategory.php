<?php

namespace App\Models\Categories;

use App\Models\BaseModel;
use App\Models\Space;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SpaceCategory extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'space_categories';

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
     * Get spaces associated with the model.
     */
    public function spaces()
    {
        return $this->hasMany(Space::class, 'category_id', 'id');
    }
}
