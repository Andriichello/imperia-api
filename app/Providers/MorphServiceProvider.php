<?php

namespace App\Providers;

use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\DishMenu;
use App\Models\DishVariant;
use App\Models\Holiday;
use App\Models\Menu;
use App\Models\Morphs\Categorizable;
use App\Models\Morphs\Category;
use App\Models\Morphs\Comment;
use App\Models\Morphs\Log;
use App\Models\Morphs\Period;
use App\Models\Morphs\Periodical;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Restaurant;
use App\Models\RestaurantNote;
use App\Models\RestaurantReview;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Tests\Models\Stubs\BaseStub;
use Tests\Models\Stubs\CategorizableStub;
use Tests\Models\Stubs\CommentableStub;
use Tests\Models\Stubs\LoggableStub;
use Tests\Models\Stubs\PeriodicalStub;
use Tests\Models\Stubs\TaggableStub;

/**
 * Class MorphServiceProvider.
 */
class MorphServiceProvider extends ServiceProvider
{
    /**
     * Array of model classes.
     *
     * @var array
     */
    protected static array $models = [
        /** People */
        User::class,
        /** Restaurants */
        Restaurant::class,
        Schedule::class,
        Holiday::class,
        RestaurantReview::class,
        /** Items */
        Menu::class,
        Product::class,
        /** Items (additional) */
        ProductVariant::class,
        /** Morphs */
        Log::class,
        Comment::class,
        Category::class,
        Categorizable::class,
        Period::class,
        Periodical::class,
        /** Stubs */
        BaseStub::class,
        CategorizableStub::class,
        CommentableStub::class,
        LoggableStub::class,
        PeriodicalStub::class,
        TaggableStub::class,

        /** Items */
        Dish::class,
        DishMenu::class,
        DishCategory::class,
        /** Items (additional) */
        DishVariant::class,
        RestaurantNote::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap(static::getMorphMap());
    }

    /**
     * Get array of model classes.
     *
     * @return array
     */
    public static function getModelClasses(): array
    {
        return static::$models;
    }

    /**
     * Get morph map for models.
     *
     * @param array|null $models
     * @return array
     */
    public static function getMorphMap(?array $models = null): array
    {
        $morphMap = [];
        foreach ($models ?? static::getModelClasses() as $model) {
            $morphMap[slugClass($model)] = $model;
        }
        return $morphMap;
    }
}
