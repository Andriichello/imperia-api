<?php

namespace App\Providers;

use App\Models\Banquet;
use App\Models\BanquetState;
use App\Models\Customer;
use App\Models\FamilyMember;
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
use App\Models\Service;
use App\Models\Space;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;
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
    protected array $models = [
        /** People */
        User::class,
        Customer::class,
        FamilyMember::class,
        /** Banquet */
        Banquet::class,
        BanquetState::class,
        /** Items */
        Menu::class,
        Space::class,
        Ticket::class,
        Service::class,
        Product::class,
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
        Relation::morphMap($this->getMorphMap());
    }

    /**
     * Get array of model classes.
     *
     * @return array
     */
    public function getModelClasses(): array
    {
        return $this->models;
    }

    /**
     * Get morph map for models.
     *
     * @return array
     */
    public function getMorphMap(): array
    {
        $morphMap = [];
        foreach ($this->getModelClasses() as $model) {
            $morphMap[slugClass($model)] = $model;
        }
        return $morphMap;
    }
}
