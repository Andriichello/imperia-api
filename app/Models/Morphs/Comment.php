<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use App\Queries\CommentQueryBuilder;
use Carbon\Carbon;
use Database\Factories\Morphs\CommentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Query\Builder as DatabaseBuilder;

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
 * @method static CommentQueryBuilder query()
 * @method static CommentFactory factory(...$parameters)
 */
class Comment extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
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
        /** @var Builder|MorphTo $morphTo */
        $morphTo = $this->morphTo('commentable', 'commentable_type', 'commentable_id', 'id');
        $morphTo->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class]);

        return $morphTo;
    }

    /**
     * Get request validation rules for creating or updating comments.
     *
     * @param string|null $prefix
     *
     * @return array
     */
    public static function rulesForAttaching(?string $prefix = null): array
    {
        return [
            $prefix . 'comments' => [
                'sometimes',
                'array',
            ],
            $prefix . 'comments.*.id' => [
                'sometimes',
                'integer',
            ],
            $prefix . 'comments.*.commentable_id' => [
                'sometimes',
                'integer',
            ],
            $prefix . 'comments.*.commentable_type' => [
                'sometimes',
                'string',
            ],
            $prefix . 'comments.*.text' => [
                'required',
                'string',
                'min:0',
                'max:255',
            ],
        ];
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return CommentQueryBuilder
     */
    public function newEloquentBuilder($query): CommentQueryBuilder
    {
        return new CommentQueryBuilder($query);
    }

    /**
     * Get the corresponding restaurant id.
     *
     * @return int|null
     */
    public function getRestaurantId(): ?int
    {
        return data_get($this->commentable, 'restaurant_id');
    }
}
