<?php

namespace App\Models;

use App\Helpers\ContentLocale;
use App\Models\Interfaces\TranslatableInterface;
use App\Models\Traits\TranslatableTrait;
use Carbon\Carbon;
use Database\Factories\RestaurantNoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class RestaurantNote.
 *
 * A short message shown under the restaurant's name.
 *
 * @property int $restaurant_id
 * @property string $text
 * @property bool $is_hidden
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Restaurant $restaurant
 *
 * @method static RestaurantNoteFactory factory(...$parameters)
 */
class RestaurantNote extends BaseModel implements TranslatableInterface
{
    use HasFactory;
    use TranslatableTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'restaurant_notes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'restaurant_id',
        'text',
        'is_hidden',
        'order',
    ];

    /**
     * The attributes that have translations.
     *
     * @var string[]
     */
    protected array $translatable = [
        'text',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_hidden' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the restaurant associated with the model.
     *
     * @return BelongsTo
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Default language of the note (its restaurant's one).
     *
     * @return string
     */
    public function getDefaultLocale(): string
    {
        return ContentLocale::instance()->ofRestaurant($this->restaurant_id);
    }
}
