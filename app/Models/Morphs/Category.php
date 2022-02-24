<?php

namespace App\Models\Morphs;

use App\Models\BaseModel;
use Carbon\Carbon;
use Database\Factories\Morphs\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class Category.
 *
 * @property string $slug
 * @property string|null $target
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
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'slug',
        'target',
        'title',
        'description',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'categorizables',
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

    /**
     * Target property mutator.
     *
     * @param string|null $target
     *
     * @return void
     */
    public function setTargetAttribute(?string $target): void
    {
        $this->attributes['target'] = $target ? slugClass($target) : null;
    }
}
