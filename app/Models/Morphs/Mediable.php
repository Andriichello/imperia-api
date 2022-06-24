<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use Carbon\Carbon;
use Database\Factories\Morphs\MediableFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Mediable.
 *
 * @property int $media_id
 * @property int $mediable_id
 * @property string $mediable_type
 * @property int|null $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Media $media
 * @property BaseModel $mediable
 *
 * @method static MediableFactory factory(...$parameters)
 */
class Mediable extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'media_id',
        'mediable_id',
        'mediable_type',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'media',
        'mediable',
    ];

    /**
     * Related media.
     *
     * @return BelongsTo
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /**
     * Related mediable model.
     *
     * @return MorphTo
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo('mediable', 'mediable_type', 'mediable_id', 'id');
    }
}
