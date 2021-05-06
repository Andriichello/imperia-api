<?php

namespace App\Models;

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
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_templatable' => 'boolean',
    ];
}
