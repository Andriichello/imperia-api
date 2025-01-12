<?php

namespace App\Models;

use App\Queries\NotificationQueryBuilder;
use Carbon\Carbon;
use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
 * @property string|null $subject
 * @property string|null $body
 * @property array|null $payload
 * @property Carbon $send_at
 * @property Carbon|null $sent_at
 * @property Carbon|null $seen_at
 *
 * @property User $sender
 * @property User|null $receiver
 *
 * @method static NotificationQueryBuilder query()
 * @method static NotificationFactory factory(...$parameters)
 */
class Notification extends BaseModel
{
    use HasFactory;

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
        'seen_at' => 'datetime',
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
     * Accessor for the data's subject attribute.
     *
     * @return ?string
     */
    public function getSubjectAttribute(): ?string
    {
        return $this->getFromJson('data', 'subject');
    }

    /**
     * Mutator for the data's subject attribute.
     *
     * @param ?string $subject
     *
     * @return void
     */
    public function setSubjectAttribute(?string $subject): void
    {
        $this->setToJson('data', 'subject', $subject);
    }

    /**
     * Accessor for the data's body attribute.
     *
     * @return ?string
     */
    public function getBodyAttribute(): ?string
    {
        return $this->getFromJson('data', 'body');
    }

    /**
     * Mutator for the data's body attribute.
     *
     * @param ?string $body
     *
     * @return void
     */
    public function setBodyAttribute(?string $body): void
    {
        $this->setToJson('data', 'body', $body);
    }

    /**
     * Accessor for the data's payload attribute.
     *
     * @return ?array
     */
    public function getPayloadAttribute(): ?array
    {
        return $this->getFromJson('data', 'payload');
    }

    /**
     * Mutator for the data's payload attribute.
     *
     * @param ?array $payload
     *
     * @return void
     */
    public function setPayloadAttribute(?array $payload): void
    {
        $this->setToJson('data', 'payload', $payload);
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


    /**
     * Get the corresponding restaurant id.
     *
     * @return int|null
     */
    public function getRestaurantId(): ?int
    {
        return data_get($this->receiver, 'restaurant_id');
    }
}
