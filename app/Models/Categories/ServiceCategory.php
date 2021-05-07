<?php

namespace App\Models\Categories;

use App\Constrainters\Implementations\DescriptionConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use App\Models\BaseModel;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceCategory extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_categories';

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
        return Category::getValidationRules($forInsert, 'service');
    }

    /**
     * Get services associated with the model.
     */
    public function services()
    {
        return $this->hasMany(Service::class, 'category_id', 'id');
    }
}
