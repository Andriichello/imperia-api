<?php

namespace App\Models;

use App\Queries\ScheduleQueryBuilder;
use Carbon\Carbon;
use Database\Factories\ScheduleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Schedule.
 *
 * @property int $id
 * @property string $weekday
 * @property int $beg_hour
 * @property int $end_hour
 * @property int|null $restaurant_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Restaurant|null $restaurant
 *
 * @method static ScheduleQueryBuilder query()
 * @method static ScheduleFactory factory(...$parameters)
 */
class Schedule extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'weekday',
        'beg_hour',
        'end_hour',
        'restaurant_id',
    ];

    /**
     * Restaurant associated with the model.
     *
     * @return BelongsTo
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id', 'id');
    }
}
