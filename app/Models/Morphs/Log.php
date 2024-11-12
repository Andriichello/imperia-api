<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use Carbon\Carbon;
use Database\Factories\Morphs\LogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Log.
 *
 * @property string|null $title
 * @property string $metadata
 * @property int $loggable_id
 * @property string $loggable_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property BaseModel $loggable
 *
 * @method static LogFactory factory(...$parameters)
 */
class Log extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title',
        'metadata',
        'loggable_id',
        'loggable_type',
    ];

    /**
     * List of relationship names.
     *
     * @var array
     */
    protected array $relationships = [
        'loggable',
    ];

    /**
     * Model, for which the log was created.
     *
     * @return MorphTo
     */
    public function loggable(): MorphTo
    {
        /** @var Builder|MorphTo $morphTo */
        $morphTo = $this->morphTo('loggable');
        $morphTo->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class]);

        return $morphTo;
    }
}
