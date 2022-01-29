<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use Carbon\Carbon;
use Database\Factories\Morphs\CategorizableFactory;
use Faker\Provider\Base;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Categorizable.
 *
 * @property int $category_id
 * @property int $categorizable_id
 * @property string $categorizable_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Category $category
 * @property BaseModel $categorized
 *
 * @method static CategorizableFactory factory(...$parameters)
 */
class Categorizable extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'morph_categorizables';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'categorizable_id',
        'categorizable_type',
    ];

    /**
     * The categorizable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'category',
        'categorized',
    ];

    /**
     * Related category.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Related categorized model.
     *
     * @return MorphTo
     */
    public function categorized(): MorphTo
    {
        return $this->morphTo('categorized', 'categorizable_type', 'categorizable_id', 'id');
    }
}
