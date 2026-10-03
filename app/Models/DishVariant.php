<?php

namespace App\Models;

use App\Models\Interfaces\ArchivableInterface;
use App\Models\Interfaces\HideableInterface;
use App\Models\Interfaces\SchedulableInterface;
use App\Models\Interfaces\SoftDeletableInterface;
use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use App\Models\Traits\ArchivableTrait;
use App\Models\Traits\HideableTrait;
use App\Models\Traits\SchedulableTrait;
use App\Models\Traits\SoftDeletableTrait;
use App\Queries\DishVariantQueryBuilder;
use Carbon\Carbon;
use Database\Factories\DishVariantFactory;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

/**
 * Class DishVariant.
 *
 * A size of a dish. Every dish has at least one, shown to guests (see `booted()`); the dish's
 * own size columns mirror its first one (see `Dish::mirrorFirstSize()`).
 *
 * @property int $dish_id
 * @property float $price
 * @property string|null $weight
 * @property string|null $weight_unit
 * @property integer|null $calories
 * @property integer|null $preparation_time
 * @property bool $archived
 * @property bool $is_hidden
 * @property Carbon|null $archived_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Dish $dish
 *
 * @method static DishVariantQueryBuilder query()
 * @method static DishVariantFactory factory(...$parameters)
 */
class DishVariant extends BaseModel implements
    SoftDeletableInterface,
    ArchivableInterface,
    HideableInterface,
    SchedulableInterface
{
    use HasFactory;
    use SoftDeletableTrait;
    use ArchivableTrait;
    use HideableTrait;
    use SchedulableTrait;

    /**
     * Message of the rule, which keeps a size of each dish shown to guests.
     */
    public const LAST_SIZE_MESSAGE = 'A dish needs at least one size shown to guests.';

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'archived' => false,
        'is_hidden' => false,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dish_id',
        'price',
        'weight',
        'weight_unit',
        'calories',
        'preparation_time',
        'archived',
        'is_hidden',
        'archived_at',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'dish',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'float',
        'archived' => 'boolean',
        'is_hidden' => 'boolean',
        'archived_at' => 'datetime',
    ];

    /**
     * Keep a size of the dish shown to guests, and the dish's own size columns
     * in line with its first size.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::saving(function (DishVariant $variant) {
            $wasShown = $variant->exists
                && !$variant->getOriginal('is_hidden')
                && !$variant->getOriginal('archived');

            if (($wasShown || !$variant->exists) && !$variant->isShown()) {
                $variant->assertOtherSizeIsShown();
            }
        });

        static::deleting(function (DishVariant $variant) {
            if (!$variant->trashed() && $variant->isShown()) {
                $variant->assertOtherSizeIsShown();
            }
        });

        foreach (['saved', 'deleted', 'restored'] as $event) {
            static::$event(fn (DishVariant $variant) => $variant->dish->mirrorFirstSize());
        }
    }

    /**
     * Get the dish associated with the model.
     *
     * @return BelongsTo
     */
    public function dish(): BelongsTo
    {
        /* @phpstan-ignore-next-line */
        return $this->belongsTo(Dish::class)
            ->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class]);
    }

    /**
     * Whether guests see the size (on a dish they see).
     *
     * @return bool
     */
    public function isShown(): bool
    {
        return !$this->is_hidden && !$this->archived && !$this->trashed();
    }

    /**
     * Whether the dish has another size shown to guests.
     *
     * @return bool
     */
    public function hasShownSibling(): bool
    {
        return static::query()
            ->withoutGlobalScopes()
            ->where('dish_id', $this->dish_id)
            ->when($this->exists, fn (DishVariantQueryBuilder $query) => $query->whereKeyNot($this->getKey()))
            ->shownToGuests()
            ->exists();
    }

    /**
     * Whether it's the only size of the dish, which guests see (so it can't be hidden).
     *
     * @return bool
     */
    public function isLastShown(): bool
    {
        return $this->isShown() && !$this->hasShownSibling();
    }

    /**
     * Make sure the dish has another size shown to guests, before this one stops being shown.
     *
     * @return void
     * @throws ValidationException
     */
    public function assertOtherSizeIsShown(): void
    {
        if (!$this->hasShownSibling()) {
            throw ValidationException::withMessages(['sizes' => static::LAST_SIZE_MESSAGE]);
        }
    }

    /**
     * Get the corresponding restaurant id.
     *
     * @return int|null
     */
    public function getRestaurantId(): ?int
    {
        return $this->dish->getRestaurantId();
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return DishVariantQueryBuilder
     */
    public function newEloquentBuilder($query): DishVariantQueryBuilder
    {
        return new DishVariantQueryBuilder($query);
    }
}
