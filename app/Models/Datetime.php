<?php

namespace App\Models;

use App\Constrainters\Constrainter;
use App\Constrainters\Implementations\DatetimeConstrainter;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Datetime extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'datetimes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'minutes',
        'hours',
        'day',
        'month',
        'year',
        'is_templatable',
    ];

    /**
     * Get array of model's validation rules.
     *
     * @var bool $forInsert
     * @return array
     */
    public static function getValidationRules($forInsert = false) {
        return [
            'minutes' => Constrainter::getRules(false, DatetimeConstrainter::getMinutesConstraints(true)),
            'hours' => Constrainter::getRules(false, DatetimeConstrainter::getHoursConstraints(true)),
            'day' => Constrainter::getRules(false, DatetimeConstrainter::getDayConstraints(true)),
            'month' => Constrainter::getRules(false, DatetimeConstrainter::getMonthConstraints(true)),
            'year' => Constrainter::getRules(false, DatetimeConstrainter::getYearConstraints(true)),
            'is_templatable' => Constrainter::getRules(false),
        ];
    }


    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_templatable' => 'boolean',
    ];
}
