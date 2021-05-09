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
     * @return array
     * @var bool $forInsert
     */
    public static function getValidationRules($forInsert = false)
    {
        return [
            'minutes' => Constrainter::getRules(false,
                array_merge(
                    DatetimeConstrainter::getMinutesConstraints(true),
                    $forInsert ? ['required_without_all:hours,day,month,year'] : []
                )
            ),
            'hours' => Constrainter::getRules(false,
                array_merge(
                    DatetimeConstrainter::getHoursConstraints(true),
                    $forInsert ? ['required_without_all:minutes,day,month,year'] : []
                )
            ),
            'day' => Constrainter::getRules(false,
                array_merge(
                    DatetimeConstrainter::getDayConstraints(true),
                    $forInsert ? ['required_without_all:minutes,hours,month,year'] : []
                )
            ),
            'month' => Constrainter::getRules(false,
                array_merge(
                    DatetimeConstrainter::getMonthConstraints(true),
                    $forInsert ? ['required_without_all:minutes,hours,day,year'] : []
                )
            ),
            'year' => Constrainter::getRules(false,
                array_merge(
                    DatetimeConstrainter::getYearConstraints(true),
                    $forInsert ? ['required_without_all:minutes,hours,day,month'] : []
                )
            ),
            'is_templatable' => Constrainter::getRules($forInsert),
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
