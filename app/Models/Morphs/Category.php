<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use Carbon\Carbon;
use Database\Factories\Morphs\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use MigrationsGenerator\Models\Model;

/**
 * Class Category.
 *
 * @property string $slug
 * @property string $title
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Categorizable[]|Collection $categorizables
 *
 * @method static CategoryFactory factory(...$parameters)
 */
class Category extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'morph_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slug',
        'title',
        'description',
    ];

    /**
     * Related categorizables.
     *
     * @return HasMany
     */
    public function categorizables(): HasMany
    {
        return $this->hasMany(Categorizable::class, 'category_id', 'id');
    }
}
