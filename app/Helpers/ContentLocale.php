<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

/**
 * Class ContentLocale.
 *
 * Languages of restaurant content (titles, descriptions, notes...).
 * Each restaurant has a default language, its content is written in it first.
 *
 * Bound as a scoped instance, so the looked up languages are kept
 * only for the current request (or job).
 */
class ContentLocale
{
    /**
     * Whether translatable models read and write their restaurant's default language,
     * instead of the app's current one (e.g. in the admin panel, which isn't translated).
     *
     * @var bool
     */
    protected bool $usesDefaults = false;

    /**
     * Default languages of restaurants, by their ids.
     *
     * @var array<int, string>
     */
    protected array $restaurants = [];

    /**
     * Restaurant ids of menus, by the menus' ids.
     *
     * @var array<int, int|null>
     */
    protected array $menus = [];

    /**
     * Get the instance.
     *
     * @return static
     */
    public static function instance(): static
    {
        return app(static::class);
    }

    /**
     * Languages content can be written in.
     *
     * @return string[]
     */
    public static function supported(): array
    {
        return config('app.supported_locales', [config('app.locale')]);
    }

    /**
     * Make translatable models read and write their restaurant's default language.
     *
     * @return void
     */
    public function useDefaults(): void
    {
        $this->usesDefaults = true;
    }

    /**
     * Whether translatable models read and write their restaurant's default language.
     *
     * @return bool
     */
    public function usesDefaults(): bool
    {
        return $this->usesDefaults;
    }

    /**
     * Default language of the restaurant (the app's one, if it has none).
     *
     * @param int|null $restaurantId
     *
     * @return string
     */
    public function ofRestaurant(?int $restaurantId): string
    {
        if (!$restaurantId) {
            return config('app.locale');
        }

        if (!array_key_exists($restaurantId, $this->restaurants)) {
            $metadata = DB::table('restaurants')
                ->where('id', $restaurantId)
                ->value('metadata');

            $this->restaurants[$restaurantId] = static::fromMetadata($metadata);
        }

        return $this->restaurants[$restaurantId];
    }

    /**
     * Default language of the menu's restaurant.
     *
     * @param int|null $menuId
     *
     * @return string
     */
    public function ofMenu(?int $menuId): string
    {
        if (!$menuId) {
            return config('app.locale');
        }

        if (!array_key_exists($menuId, $this->menus)) {
            $this->menus[$menuId] = DB::table('dish_menus')
                ->where('id', $menuId)
                ->value('restaurant_id');
        }

        return $this->ofRestaurant($this->menus[$menuId]);
    }

    /**
     * Forget the looked up languages (e.g. when a restaurant's default one changes).
     *
     * @return void
     */
    public function forget(): void
    {
        $this->restaurants = [];
        $this->menus = [];
    }

    /**
     * Default language stored in a restaurant's metadata (the app's one, if it has none).
     *
     * @param string|array|null $metadata
     *
     * @return string
     */
    public static function fromMetadata(string|array|null $metadata): string
    {
        $metadata = is_string($metadata) ? json_decode($metadata, true) : $metadata;
        $locale = data_get($metadata, 'locale');

        return in_array($locale, static::supported(), true)
            ? $locale : config('app.locale');
    }
}
