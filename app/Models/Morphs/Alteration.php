<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use App\Queries\AlterationQueryBuilder;
use Carbon\Carbon;
use Database\Factories\Morphs\AlterationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

/**
 * Class Alteration.
 *
 * @property object $values
 * @property int $alterable_id
 * @property string $alterable_type
 * @property Carbon|null $perform_at
 * @property Carbon|null $performed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property BaseModel $alterable
 *
 * @method static AlterationQueryBuilder query()
 * @method static AlterationFactory factory(...$parameters)
 */
class Alteration extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'values',
        'alterable_id',
        'alterable_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'values' => 'object',
        'perform_at' => 'datetime',
        'performed_at' => 'datetime',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'alterable',
    ];

    /**
     * Model, which was or should be changed.
     *
     * @return MorphTo
     */
    public function alterable(): MorphTo
    {
        return $this->morphTo('alterable', 'alterable_type', 'alterable_id', 'id');
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
            $prefix . 'alterations.*.values' => [
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
}
