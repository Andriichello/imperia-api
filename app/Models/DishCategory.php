<?php

namespace App\Models;

use App\Models\Interfaces\MediableInterface;
use App\Models\Scopes\ArchivedScope;
use App\Models\Scopes\SoftDeletableScope;
use App\Models\Traits\ArchivableTrait;
use App\Models\Traits\MediableTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class DishCategory.
 *
 * @property int $menu_id
 * @property string $slug
 * @property string|null $target
 * @property string $title
 * @property string|null $description
 * @property bool|null $archived
 * @property int|null $popularity
 * @property string|null $metadata
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property DishMenu $menu
 */
class DishCategory extends BaseModel implements
    MediableInterface
{
    use ArchivableTrait;
    use MediableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'menu_id',
        'slug',
        'target',
        'title',
        'description',
        'archived',
        'popularity',
        'metadata',
    ];

    /**
     * The loadable relationships for the model.
     *
     * @var array
     */
    protected $relations = [
        'menu',
        'restaurant',
    ];

    /**
     * Menu associated with the model.
     *
     * @return BelongsTo
     */
    public function menu(): BelongsTo
    {
        // @phpstan-ignore-next-line
        return $this->belongsTo(DishMenu::class, 'menu_id')
            ->withoutGlobalScopes([ArchivedScope::class, SoftDeletableScope::class]);
    }

    /**
     * Get the corresponding restaurant id.
     *
     * @return int|null
     */
    public function getRestaurantId(): ?int
    {
        return $this->menu->getRestaurantId();
    }
}
