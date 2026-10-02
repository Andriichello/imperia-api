<?php

namespace App\Models;

use App\Helpers\ContentLocale;
use App\Models\Interfaces\TranslatableInterface;
use App\Models\Traits\TranslatableTrait;
use Carbon\Carbon;
use Database\Factories\ScheduleExceptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ScheduleException.
 *
 * A special day (or several days in a row), e.g. a holiday or a short day,
 * which overrides the restaurant's weekly schedules: it's closed then, or open
 * from `beg_hour`:`beg_minute` till `end_hour`:`end_minute` (after midnight,
 * if it's earlier).
 *
 * @property int $restaurant_id
 * @property Carbon $starts_on
 * @property Carbon $ends_on
 * @property bool $is_closed
 * @property int|null $beg_hour
 * @property int|null $beg_minute
 * @property int|null $end_hour
 * @property int|null $end_minute
 * @property string|null $reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Restaurant $restaurant
 *
 * @method static ScheduleExceptionFactory factory(...$parameters)
 */
class ScheduleException extends BaseModel implements TranslatableInterface
{
    use HasFactory;
    use TranslatableTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'schedule_exceptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'restaurant_id',
        'starts_on',
        'ends_on',
        'is_closed',
        'beg_hour',
        'beg_minute',
        'end_hour',
        'end_minute',
        'reason',
    ];

    /**
     * The attributes that have translations.
     *
     * @var string[]
     */
    protected array $translatable = [
        'reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
        'is_closed' => 'boolean',
        'beg_hour' => 'integer',
        'beg_minute' => 'integer',
        'end_hour' => 'integer',
        'end_minute' => 'integer',
    ];

    /**
     * Get the restaurant associated with the model.
     *
     * @return BelongsTo
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Default language of the reason (its restaurant's one).
     *
     * @return string
     */
    public function getDefaultLocale(): string
    {
        return ContentLocale::instance()->ofRestaurant($this->restaurant_id);
    }
}
