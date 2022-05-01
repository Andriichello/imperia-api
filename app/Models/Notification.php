<?php

namespace App\Models;

use App\Queries\NotificationQueryBuilder;
use Carbon\Carbon;
use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

/**
 * Class Notification.
 *
 * @property int $id
 * @property int|null $sender_id
 * @property int $receiver_id
 * @property string $channel
 * @property string|null $data
 * @property Carbon $send_at
 * @property Carbon|null $sent_at
 *
 * @property User $sender
 * @property User|null $receiver
 *
 * @method static NotificationQueryBuilder query()
 * @method static NotificationFactory factory(...$parameters)
 */
class Notification extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'data' => '{}',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'channel',
        'data',
        'send_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'send_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var string[]
     */
    protected $appends = [
        'type',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'sender',
        'receiver',
    ];

    /**
     * User, which sends the notification.
     *
     * @return BelongsTo
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    /**
     * User, which receives the notification.
     *
     * @return BelongsTo
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id', 'id');
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return NotificationQueryBuilder
     */
    public function newEloquentBuilder($query): NotificationQueryBuilder
    {
        return new NotificationQueryBuilder($query);
    }
}
