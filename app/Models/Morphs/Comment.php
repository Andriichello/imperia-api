<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use Carbon\Carbon;
use Database\Factories\Morphs\CommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Comment.
 *
 * @property string $text
 * @property int $commentable_id
 * @property string $commentable_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property BaseModel $commentable
 *
 * @method static CommentFactory factory(...$parameters)
 */
class Comment extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'text',
        'commentable_id',
        'commentable_type',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'commentable',
    ];

    /**
     * Model, which was commented.
     *
     * @return MorphTo
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo('commentable', 'commentable_type', 'commentable_id', 'id');
    }
}
