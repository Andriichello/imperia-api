<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Period extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'periods';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'beg_datetime_id',
        'end_datetime_id',
        'weekdays',
        'is_templatable',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'begDatetime',
        'endDatetime',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_templatable' => 'boolean',
    ];

    /**
     * Get the period's beginning datetime.
     */
    public function begDatetime()
    {
        return $this->belongsTo(Datetime::class, 'beg_datetime_id', 'id');
    }

    /**
     * Get the period's ending datetime.
     */
    public function endDatetime()
    {
        return $this->belongsTo(Datetime::class, 'end_datetime_id', 'id');
    }
}
