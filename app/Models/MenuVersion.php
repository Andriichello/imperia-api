<?php

namespace App\Models;

use App\Queries\MenuVersionQueryBuilder;
use Carbon\Carbon;
use Database\Factories\MenuVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Support\Collection;

/**
 * Class MenuVersion.
 *
 * A scheduled version: a named set of changes of a restaurant's page, which go live together
 * at one date and time. A change scheduled on its own is a version of one change, without a name.
 *
 * @property int $id
 * @property int $restaurant_id
 * @property string|null $name
 * @property string $status
 * @property Carbon|null $goes_live_at
 * @property int|null $created_by
 * @property Carbon|null $applied_at
 * @property Carbon|null $failed_at
 * @property string|null $failure_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Restaurant $restaurant
 * @property User|null $creator
 * @property MenuVersionChange[]|Collection $itemChanges
 *
 * @method static MenuVersionQueryBuilder query()
 * @method static MenuVersionFactory factory(...$parameters)
 */
class MenuVersion extends BaseModel
{
    use HasFactory;

    /**
     * Status of a version, which isn't scheduled yet (it may have no date).
     */
    public const STATUS_DRAFT = 'draft';

    /**
     * Status of a version, which goes live at its date.
     */
    public const STATUS_SCHEDULED = 'scheduled';

    /**
     * Status of a deactivated version: it keeps its date, but doesn't go live.
     */
    public const STATUS_INACTIVE = 'inactive';

    /**
     * Status of a version, which went live.
     */
    public const STATUS_APPLIED = 'applied';

    /**
     * Status of a version, which couldn't go live (nothing of it was applied).
     */
    public const STATUS_FAILED = 'failed';

    /**
     * Statuses of versions, which haven't gone live: they can still be changed.
     *
     * @var string[]
     */
    public const PENDING = [
        self::STATUS_DRAFT,
        self::STATUS_SCHEDULED,
        self::STATUS_INACTIVE,
        self::STATUS_FAILED,
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menu_versions';

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => self::STATUS_DRAFT,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'restaurant_id',
        'name',
        'status',
        'goes_live_at',
        'created_by',
        'applied_at',
        'failed_at',
        'failure_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'goes_live_at' => 'datetime',
        'applied_at' => 'datetime',
        'failed_at' => 'datetime',
    ];


    /**
     * The restaurant, whose page the version changes.
     *
     * @return BelongsTo
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * The user, who created the version.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Changes of the version, one per changed item.
     *
     * @return HasMany
     */
    public function itemChanges(): HasMany
    {
        // @phpstan-ignore-next-line
        return $this->hasMany(MenuVersionChange::class, 'version_id')
            ->orderBy('id');
    }

    /**
     * Whether the version hasn't gone live yet, so it can still be changed.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return in_array($this->status, static::PENDING, true);
    }

    /**
     * Number of changed fields (a new item counts as one change).
     *
     * @return int
     */
    public function countChanges(): int
    {
        return (int) $this->itemChanges->sum(fn (MenuVersionChange $change) => $change->countChanges());
    }

    /**
     * Number of changed items.
     *
     * @return int
     */
    public function countItems(): int
    {
        return $this->itemChanges->count();
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return MenuVersionQueryBuilder
     */
    public function newEloquentBuilder($query): MenuVersionQueryBuilder
    {
        return new MenuVersionQueryBuilder($query);
    }
}
