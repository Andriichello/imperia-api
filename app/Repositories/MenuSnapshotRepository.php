<?php

namespace App\Repositories;

use App\Http\Resources\Dish\DishResource;
use App\Models\Dish;
use App\Models\MenuSnapshot;
use App\Models\Restaurant;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Class MenuSnapshotRepository.
 *
 * Snapshots of the dishes guests see on a restaurant's pages: a gzipped JSON file of each language,
 * named after its content, so it never changes and is cached for long. A snapshot is of the
 * restaurant's content version (it goes up on every change of the pages, see `WebCacheHelper`):
 * after a change, the first request builds the next one. Building the same content again gives
 * the same file, so a change, which didn't touch dishes, keeps the address browsers have cached.
 */
class MenuSnapshotRepository
{
    /**
     * How long a file is cached: it never changes.
     */
    public const CACHE_CONTROL = 'public, max-age=31536000, immutable';

    /**
     * Seconds a build may take before another request builds again, and seconds the others wait.
     */
    protected const LOCK_SECONDS = 60;
    protected const WAIT_SECONDS = 15;

    /**
     * The restaurant's current snapshot of the language: of its content version.
     *
     * @param Restaurant $restaurant
     * @param string $locale
     *
     * @return MenuSnapshot|null
     */
    public function current(Restaurant $restaurant, string $locale): ?MenuSnapshot
    {
        return $this->ofVersion($restaurant->id, $locale, (int) $restaurant->content_version);
    }

    /**
     * Where a page loads its dishes from: the current snapshot's file (from the bucket itself,
     * when it allows it, see `menu_snapshots.direct`), or the API, which gives it or builds it.
     *
     * @param Restaurant $restaurant
     * @param string $locale
     *
     * @return string
     */
    public function pageUrl(Restaurant $restaurant, string $locale): string
    {
        $snapshot = $this->current($restaurant, $locale);

        if ($snapshot && config('menu_snapshots.direct')) {
            return $this->disk()->url($snapshot->path);
        }

        return route('api.restaurants.dishes', [
            'id' => $restaurant->id,
            'locale' => $locale,
            // the address changes with the content: browsers cache it for long
            ...($snapshot ? ['hash' => $snapshot->hash] : []),
        ], false);
    }

    /**
     * The gzipped JSON of the snapshot, from its file. Cloud Storage unzips a gzipped file for
     * clients, which don't ask for gzip (the storage's client doesn't), so it's zipped again then.
     *
     * @param MenuSnapshot $snapshot
     *
     * @return string|null none, when the file can't be read
     */
    public function read(MenuSnapshot $snapshot): ?string
    {
        try {
            $content = $this->disk()->get($snapshot->path);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }

        if ($content === null) {
            return null;
        }

        return str_starts_with($content, "\x1f\x8b") ? $content : (string) gzencode($content, 6);
    }

    /**
     * Build the restaurant's snapshot of the language, unless another request has built it
     * meanwhile: one request builds it at a time, the others wait for it. When it can't be kept
     * (the storage fails, the other build takes too long), the JSON is still built.
     *
     * @param Restaurant $restaurant
     * @param string $locale
     *
     * @return array{0: MenuSnapshot|null, 1: string|null} the snapshot and its gzipped JSON (none,
     *   when it was built by another request)
     */
    public function build(Restaurant $restaurant, string $locale): array
    {
        try {
            return Cache::lock("menu-snapshot:$restaurant->id:$locale", static::LOCK_SECONDS)
                ->block(static::WAIT_SECONDS, fn () => $this->buildAlone($restaurant, $locale));
        } catch (LockTimeoutException) {
            return [null, $this->render($restaurant, $locale)['gzip']];
        }
    }

    /**
     * Build the snapshot while no other request builds it.
     *
     * @param Restaurant $restaurant
     * @param string $locale
     *
     * @return array{0: MenuSnapshot|null, 1: string|null}
     */
    protected function buildAlone(Restaurant $restaurant, string $locale): array
    {
        // the version may have gone up since the restaurant was loaded
        $version = (int) Restaurant::query()
            ->withoutGlobalScopes()
            ->whereKey($restaurant->id)
            ->value('content_version');

        $built = $this->ofVersion($restaurant->id, $locale, $version);

        if ($built) {
            return [$built, null];
        }

        $start = microtime(true);
        $rendered = $this->render($restaurant, $locale);
        $path = sprintf('menus/%d/%s/%s.json', $restaurant->id, $locale, $rendered['hash']);

        try {
            // a snapshot of the same content has the file already
            if (!MenuSnapshot::query()->where('path', $path)->exists()) {
                $this->disk()->put($path, $rendered['gzip'], ['metadata' => [
                    'contentType' => 'application/json',
                    'contentEncoding' => 'gzip',
                    'cacheControl' => static::CACHE_CONTROL,
                ]]);
            }
        } catch (Throwable $exception) {
            report($exception);

            return [null, $rendered['gzip']];
        }

        /** @var MenuSnapshot $snapshot */
        $snapshot = MenuSnapshot::query()->firstOrCreate([
            'restaurant_id' => $restaurant->id,
            'locale' => $locale,
            'content_version' => $version,
        ], [
            'path' => $path,
            'hash' => $rendered['hash'],
            'dish_count' => $rendered['count'],
            'size' => strlen($rendered['json']),
            'gzip_size' => strlen($rendered['gzip']),
            'built_ms' => (int) round((microtime(true) - $start) * 1000),
        ]);

        $this->prune($restaurant->id, $locale, $version);

        return [$snapshot, $rendered['gzip']];
    }

    /**
     * The JSON of the dishes guests see on the restaurant's pages, in the language, like the API
     * gives them (`{"data": [...]}`, with their sizes and photos), from the most popular one:
     * the same dishes give the same JSON, byte for byte.
     *
     * @param Restaurant $restaurant
     * @param string $locale
     *
     * @return array{json: string, gzip: string, hash: string, count: int}
     */
    public function render(Restaurant $restaurant, string $locale): array
    {
        $previous = App::getLocale();
        App::setLocale($locale);

        try {
            $dishes = Dish::query()
                ->withRestaurant($restaurant->id)
                ->withVisibleParents()
                ->with(['variants' => fn ($query) => $query->orderBy('dish_variants.id'), 'media'])
                ->orderByDesc('dishes.popularity')
                ->orderBy('dishes.id')
                ->get();

            $json = json_encode(
                ['data' => DishResource::collection($dishes)->resolve()],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
            );
        } finally {
            App::setLocale($previous);
        }

        return [
            'json' => $json,
            'gzip' => (string) gzencode($json, 9),
            'hash' => sha1($json),
            'count' => $dishes->count(),
        ];
    }

    /**
     * The snapshot of the restaurant's language of the content version.
     *
     * @param int $restaurantId
     * @param string $locale
     * @param int $version
     *
     * @return MenuSnapshot|null
     */
    protected function ofVersion(int $restaurantId, string $locale, int $version): ?MenuSnapshot
    {
        /** @var MenuSnapshot|null $snapshot */
        $snapshot = MenuSnapshot::query()
            ->where('restaurant_id', $restaurantId)
            ->where('locale', $locale)
            ->where('content_version', $version)
            ->first();

        return $snapshot;
    }

    /**
     * Snapshots of earlier versions go, once pages loaded before the change don't need them, and
     * their files, which no snapshot is of anymore.
     *
     * @param int $restaurantId
     * @param string $locale
     * @param int $version the current one
     *
     * @return void
     */
    protected function prune(int $restaurantId, string $locale, int $version): void
    {
        /** @var Collection<int, MenuSnapshot> $old */
        $old = MenuSnapshot::query()
            ->where('restaurant_id', $restaurantId)
            ->where('locale', $locale)
            ->where('content_version', '<', $version)
            ->where('created_at', '<', Carbon::now()->subHours((int) config('menu_snapshots.keep_hours')))
            ->get();

        foreach ($old as $snapshot) {
            $snapshot->delete();

            if (MenuSnapshot::query()->where('path', $snapshot->path)->exists()) {
                continue;
            }

            try {
                $this->disk()->delete($snapshot->path);
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }

    /**
     * Where the files are kept.
     *
     * @return FilesystemAdapter
     */
    protected function disk(): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(config('menu_snapshots.disk'));

        return $disk;
    }
}
