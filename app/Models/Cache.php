<?php

namespace App\Models;

use App\Queries\CacheQueryBuilder;
use Database\Factories\CacheFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache as CacheFacade;
use Illuminate\Support\Str;

/**
 * Class Cache.
 *
 * @property string $key
 * @property string $value
 * @property Carbon $expiration
 *
 * @property string $plain_key
 * @property string[] $groups
 *
 * @method static CacheQueryBuilder query()
 * @method static CacheFactory factory(...$parameters)
 */
class Cache extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cache';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'key';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'key',
        'value',
        'expiration',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expiration' => 'datetime',
    ];

    /**
     * Mutator for the expiration datetime.
     *
     * @return Attribute
     */
    public function expiration(): Attribute
    {
        return Attribute::set(
            function (mixed $expiration) {
                if ($expiration instanceof DateTimeInterface) {
                    return $expiration->getTimestamp();
                }

                return $expiration;
            },
        );
    }

    /**
     * Accessor for the plain key (without cache prefix).
     *
     * @return Attribute
     */
    public function plainKey(): Attribute
    {
        return Attribute::get(
            function () {
                $key = $this->key;
                $prefix = CacheFacade::getPrefix();

                if (str_starts_with($key, $prefix)) {
                    $key = Str::after($key, $prefix);
                }

                return $key;
            }
        );
    }

    /**
     * Accessor for the groups, to which the record belongs.
     *
     * @return Attribute
     */
    public function groups(): Attribute
    {
        return Attribute::get(
            function () {
                $matches = [];

                preg_match('/.*\[(?<groups>(.*))]:\{.*}.*/', $this->plain_key, $matches);

                return array_filter(explode(',', $matches['groups'] ?? ''));
            }
        );
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return CacheQueryBuilder
     */
    public function newEloquentBuilder($query): CacheQueryBuilder
    {
        return new CacheQueryBuilder($query);
    }
}
