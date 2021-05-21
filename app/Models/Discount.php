<?php

namespace App\Models;

use App\Constrainters\Implementations\AmountConstrainter;
use App\Constrainters\Implementations\DescriptionConstrainter;
use App\Constrainters\Implementations\IdentifierConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use App\Models\Categories\DiscountCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Discount extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'discounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'amount',
        'percent',
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
            'amount' => AmountConstrainter::getRules(false,
                $forInsert ? ['required_without:percent'] : []
            ),
            'percent' => AmountConstrainter::getRules(false,
                $forInsert ? ['required_without:percent'] : []
            ),
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
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'float',
        'percent' => 'float',
        'created_at' => 'datetime:d-m-Y H:i:s',
        'updated_at' => 'datetime:d-m-Y H:i:s',
        'deleted_at' => 'datetime:d-m-Y H:i:s',
    ];

    /**
     * Get period associated with the model.
     */
    public function period()
    {
        return $this->belongsTo(Period::class, 'period_id', 'id');
    }

    /**
     * Get category associated with the model.
     */
    public function category()
    {
        return $this->belongsTo(DiscountCategory::class, 'category_id', 'id');
    }
}
