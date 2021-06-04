<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Datetime extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

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
