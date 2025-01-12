<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use App\Models\Restaurant;
use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use App\Queries\AlterationQueryBuilder;
use Carbon\Carbon;
use Database\Factories\Morphs\AlterationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

/**
 * Class Alteration.
 *
 * @property string|null $metadata
 * @property int $alterable_id
 * @property string $alterable_type
 * @property Carbon|null $perform_at
 * @property Carbon|null $performed_at
 * @property string|null $exception
 * @property Carbon|null $failed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Restaurant|null $restaurant
 * @property BaseModel|null $alterable
 *
 * @method static AlterationQueryBuilder query()
 * @method static AlterationFactory factory(...$parameters)
 */
class Alteration extends BaseModel
{
    use HasFactory;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'metadata' => '{}',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'metadata',
        'alterable_id',
        'alterable_type',
        'perform_at',
        'performed_at',
        'exception',
        'failed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'perform_at' => 'datetime',
        'performed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'restaurant',
        'alterable',
    ];

    /**
     * Restaurant associated with the model.
     *
     * @return BelongsTo
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Model, which was or should be changed.
     *
     * @return MorphTo
     */
    public function alterable(): MorphTo
    {
        /** @var Builder|MorphTo $morphTo */
        $morphTo = $this->morphTo('alterable', 'alterable_type', 'alterable_id', 'id');
        $morphTo->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class]);

        return $morphTo;
    }

    /**
     * Get request validation rules for creating or updating alterations.
     *
     * @param string|null $prefix
     *
     * @return array
     */
    public static function rulesForAttaching(?string $prefix = null): array
    {
        return [
            $prefix . 'alterations' => [
                'sometimes',
                'array',
            ],
            $prefix . 'alterations.*.id' => [
                'sometimes',
                'integer',
            ],
            $prefix . 'alterations.*.alterable_id' => [
                'sometimes',
                'integer',
            ],
            $prefix . 'alterations.*.alterable_type' => [
                'sometimes',
                'string',
            ],
            $prefix . 'alterations.*.metadata' => [
                'required',
                'array',
                'min:1',
            ],
        ];
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return AlterationQueryBuilder
     */
    public function newEloquentBuilder($query): AlterationQueryBuilder
    {
        return new AlterationQueryBuilder($query);
    }

    /**
     * Get the corresponding restaurant id.
     *
     * @return int|null
     */
    public function getRestaurantId(): ?int
    {
        return data_get($this->alterable, 'restaurant_id');
    }
}
