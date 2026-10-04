<?php

namespace App\Models;

use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use Carbon\Carbon;
use Database\Factories\MenuVersionChangeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MenuVersionChange.
 *
 * The change of one item in a scheduled version. Each changed field keeps its live value from
 * when the change was planned and its new one: `{"price": {"live": 185, "new": 195}}`. A new
 * item has no target yet: it's created in its parent, when the version goes live.
 *
 * @property int $id
 * @property int $version_id
 * @property string $target_type
 * @property int|null $target_id
 * @property int|null $parent_id
 * @property array<string, array{live: mixed, new: mixed}> $fields
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property MenuVersion $version
 * @property BaseModel|null $target
 *
 * @method static MenuVersionChangeFactory factory(...$parameters)
 */
class MenuVersionChange extends BaseModel
{
    use HasFactory;

    /**
     * Texts in every language (`{"en": "Soups", "uk": "Супи"}`).
     */
    public const KIND_TEXT = 'text';

    /**
     * A plain value (a string or null).
     */
    public const KIND_VALUE = 'value';

    /**
     * A price (a number).
     */
    public const KIND_PRICE = 'price';

    /**
     * Weight or volume of a size (stored as text, e.g. "0.5").
     */
    public const KIND_WEIGHT = 'weight';

    /**
     * A whole number or null (calories, preparation time).
     */
    public const KIND_NUMBER = 'number';

    /**
     * Hidden from guests (`is_hidden`).
     */
    public const KIND_HIDDEN = 'hidden';

    /**
     * Archived (moved out of the lists) or restored.
     */
    public const KIND_ARCHIVED = 'archived';

    /**
     * Diet tags and allergens.
     */
    public const KIND_FLAGS = 'flags';

    /**
     * Photos in their order, each shown or hidden: `[{"id": 4, "is_hidden": false}]`.
     */
    public const KIND_MEDIA = 'media';

    /**
     * Sizes of a new dish (only a new one: sizes of a dish are items of their own).
     */
    public const KIND_SIZES = 'sizes';

    /**
     * Items, which versions can change: their models, fields (with their kinds), and the type of
     * the parent a new one is created in (none, when they can't be new).
     *
     * @var array<string, array{class: class-string<BaseModel>, fields: array<string, string>, parent: string|null}>
     */
    public const TARGETS = [
        'restaurants' => [
            'class' => Restaurant::class,
            'fields' => [
                'name' => self::KIND_TEXT,
                'address' => self::KIND_TEXT,
                'establishment' => self::KIND_VALUE,
                'phone' => self::KIND_VALUE,
                'brand_primary' => self::KIND_VALUE,
                'brand_primary_content' => self::KIND_VALUE,
                'brand_accent' => self::KIND_VALUE,
                'media' => self::KIND_MEDIA,
            ],
            'parent' => null,
        ],
        'restaurant-notes' => [
            'class' => RestaurantNote::class,
            'fields' => [
                'text' => self::KIND_TEXT,
                'is_hidden' => self::KIND_HIDDEN,
            ],
            'parent' => 'restaurants',
        ],
        'dish-menus' => [
            'class' => DishMenu::class,
            'fields' => [
                'title' => self::KIND_TEXT,
                'description' => self::KIND_TEXT,
                'is_hidden' => self::KIND_HIDDEN,
                'archived' => self::KIND_ARCHIVED,
            ],
            'parent' => null,
        ],
        'dish-categories' => [
            'class' => DishCategory::class,
            'fields' => [
                'title' => self::KIND_TEXT,
                'description' => self::KIND_TEXT,
                'is_hidden' => self::KIND_HIDDEN,
                'archived' => self::KIND_ARCHIVED,
            ],
            'parent' => null,
        ],
        'dishes' => [
            'class' => Dish::class,
            'fields' => [
                'title' => self::KIND_TEXT,
                'description' => self::KIND_TEXT,
                'badge' => self::KIND_TEXT,
                'is_hidden' => self::KIND_HIDDEN,
                'archived' => self::KIND_ARCHIVED,
                'flags' => self::KIND_FLAGS,
                'media' => self::KIND_MEDIA,
                'sizes' => self::KIND_SIZES,
            ],
            'parent' => 'dish-categories',
        ],
        'dish-variants' => [
            'class' => DishVariant::class,
            'fields' => [
                'price' => self::KIND_PRICE,
                'weight' => self::KIND_WEIGHT,
                'weight_unit' => self::KIND_VALUE,
                'calories' => self::KIND_NUMBER,
                'preparation_time' => self::KIND_NUMBER,
                'is_hidden' => self::KIND_HIDDEN,
                'archived' => self::KIND_ARCHIVED,
            ],
            'parent' => 'dishes',
        ],
    ];

    /**
     * Label of the changed item, when it's set (see `VersionLabels`).
     *
     * @var array|null
     */
    public ?array $label = null;

    /**
     * Conflicts of the change, when they're checked (see `VersionEditorRepository::conflicts()`).
     *
     * @var array|null
     */
    public ?array $conflicts = null;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menu_version_changes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'version_id',
        'target_type',
        'target_id',
        'parent_id',
        'fields',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'fields' => 'array',
    ];


    /**
     * The version the change belongs to.
     *
     * @return BelongsTo
     */
    public function version(): BelongsTo
    {
        return $this->belongsTo(MenuVersion::class, 'version_id');
    }

    /**
     * The changed item, whatever its state (a deleted one can't be changed: it's a conflict).
     * None for a new item.
     *
     * @return MorphTo
     */
    public function target(): MorphTo
    {
        /** @var MorphTo|Builder $morphTo */
        $morphTo = $this->morphTo('target', 'target_type', 'target_id', 'id');
        $morphTo->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class]);

        return $morphTo;
    }

    /**
     * Whether the item is new: it's created when the version goes live.
     *
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->target_id === null;
    }

    /**
     * Number of changed fields (a new item counts as one change).
     *
     * @return int
     */
    public function countChanges(): int
    {
        return $this->isNew() ? 1 : count($this->fields ?? []);
    }

    /**
     * New values of the changed fields.
     *
     * @return array<string, mixed>
     */
    public function newValues(): array
    {
        return array_map(fn (array $field) => $field['new'] ?? null, $this->fields ?? []);
    }

    /**
     * The item of the type, if it exists (whatever its state, but not deleted, a size not with its
     * dish) and belongs to the restaurant.
     *
     * @param int $restaurantId
     * @param string|null $type
     * @param int|null $id
     *
     * @return BaseModel|null
     */
    public static function findItem(int $restaurantId, ?string $type, ?int $id): ?BaseModel
    {
        /** @var class-string<BaseModel>|null $class */
        $class = static::TARGETS[$type]['class'] ?? null;

        if (!$class || !$id) {
            return null;
        }

        $query = $class::query()->withoutGlobalScopes()->whereKey($id);

        if (in_array(SoftDeletes::class, class_uses_recursive($class))) {
            $query->whereNull($query->qualifyColumn('deleted_at'));
        }

        /** @var BaseModel|null $model */
        $model = $query->first();

        // a size is gone with its dish
        if ($model instanceof DishVariant) {
            $dish = $model->getRelationValue('dish');

            if (!$dish instanceof Dish || $dish->trashed()) {
                return null;
            }
        }

        return $model && $model->getRestaurantId() === $restaurantId ? $model : null;
    }

    /**
     * Fields of an item type, with their kinds.
     *
     * @param string $type
     *
     * @return array<string, string>
     */
    public static function fieldsOf(string $type): array
    {
        return static::TARGETS[$type]['fields'] ?? [];
    }

    /**
     * Get the corresponding restaurant id.
     *
     * @return int|null
     */
    public function getRestaurantId(): ?int
    {
        return $this->version->restaurant_id;
    }
}
