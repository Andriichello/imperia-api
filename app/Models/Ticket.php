<?php

namespace App\Models;

use App\Constrainters\Implementations\DescriptionConstrainter;
use App\Constrainters\Implementations\IdentifierConstrainter;
use App\Constrainters\Implementations\NameConstrainter;
use App\Constrainters\Implementations\PriceConstrainter;
use App\Models\Categories\ServiceCategory;
use App\Models\Categories\TicketCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Validation\Rule;

class Ticket extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tickets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
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
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
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
        return $this->belongsTo(TicketCategory::class, 'category_id', 'id');
    }
}
