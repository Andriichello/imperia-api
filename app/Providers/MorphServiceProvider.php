<?php

namespace App\Providers;

use App\Models\Banquet;
use App\Models\Customer;
use App\Models\FamilyMember;
use App\Models\Holiday;
use App\Models\Menu;
use App\Models\Morphs\Categorizable;
use App\Models\Morphs\Category;
use App\Models\Morphs\Comment;
use App\Models\Morphs\Discount;
use App\Models\Morphs\Discountable;
use App\Models\Morphs\Log;
use App\Models\Morphs\Period;
use App\Models\Morphs\Periodical;
use App\Models\Orders\Order;
use App\Models\Orders\ProductOrderField;
use App\Models\Orders\ServiceOrderField;
use App\Models\Orders\SpaceOrderField;
use App\Models\Orders\TicketOrderField;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\RestaurantReview;
use App\Models\Schedule;
use App\Models\Service;
use App\Models\Space;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Tests\Models\Stubs\BaseStub;
use Tests\Models\Stubs\CategorizableStub;
use Tests\Models\Stubs\CommentableStub;
use Tests\Models\Stubs\DiscountableStub;
use Tests\Models\Stubs\LoggableStub;
use Tests\Models\Stubs\PeriodicalStub;

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
        Customer::class,
        FamilyMember::class,
        /** Restaurants */
        Restaurant::class,
        Schedule::class,
        Holiday::class,
        RestaurantReview::class,
        /** Items */
        Menu::class,
        Space::class,
        Ticket::class,
        Service::class,
        Product::class,
        /** Items (additional) */
        ProductVariant::class,
        /** Banquet */
        Banquet::class,
        /** Orders */
        Order::class,
        SpaceOrderField::class,
        TicketOrderField::class,
        ServiceOrderField::class,
        ProductOrderField::class,
        /** Morphs */
        Log::class,
        Comment::class,
        Category::class,
        Categorizable::class,
        Discount::class,
        Discountable::class,
        Period::class,
        Periodical::class,
        /** Stubs */
        BaseStub::class,
        CategorizableStub::class,
        CommentableStub::class,
        DiscountableStub::class,
        LoggableStub::class,
        PeriodicalStub::class,
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
