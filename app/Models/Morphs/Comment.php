<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use App\Models\Traits\SoftDeletable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Comment.
 *
 * @property string $text
 * @property int $target_id
 * @property int $container_id
 * @property string $target_type
 * @property string $container_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property Model|null $target
 * @property Model|null $container
 */
class Comment extends BaseModel
{
    use SoftDeletable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'text',
        'target_id',
        'target_type',
        'container_id',
        'container_type',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'target',
        'container',
    ];

    /**
     * Get the target model.
     *
     * @return MorphTo
     */
    public function target(): MorphTo
    {
        return $this->morphTo('target', 'target_type', 'target_id', 'id');
    }

    /**
     * Get the container model.
     *
     * @return MorphTo
     */
    public function container(): MorphTo
    {
        return $this->morphTo('container', 'container_type', 'container_id', 'id');
    }
}
