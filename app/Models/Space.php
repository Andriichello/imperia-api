<?php

namespace App\Models;

use App\Constrainters\Constrainter;
use App\Constrainters\Implementations\AmountConstrainter;
use App\Constrainters\Implementations\DescriptionConstrainter;
use App\Constrainters\Implementations\IdentifierConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use App\Constrainters\Implementations\PriceConstrainter;
use App\Models\Orders\SpaceOrderField;
use Symfony\Component\Validator\Constraints as Assert;
use App\Models\Categories\SpaceCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Space extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'spaces';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'number',
        'floor',
        'price',
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
            'number' => AmountConstrainter::getRules($forInsert),
            'floor' => Constrainter::getRules($forInsert),
            'price' => PriceConstrainter::getRules($forInsert),
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
        return $this->belongsTo(SpaceCategory::class, 'category_id', 'id');
    }

    public function intervals()
    {
        return $this->hasMany(SpaceOrderField::class, 'space_id', 'id')
            ->without('space')
            ->select(['space_id', 'beg_datetime', 'end_datetime']);
    }
}
