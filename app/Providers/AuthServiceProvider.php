<?php

namespace App\Providers;

use App\Guards\SignatureGuard;
use App\Helpers\SignatureHelper;
use App\Models as Models;
use App\Models\Morphs as Morphs;
use App\Policies as Policies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

/**
 * Class AuthServiceProvider.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        /** People */
        Models\User::class => Policies\UserPolicy::class,
        Models\Customer::class => Policies\CustomerPolicy::class,
        Models\FamilyMember::class => Policies\FamilyMemberPolicy::class,
        /** Orders */
        Models\Banquet::class => Policies\BanquetPolicy::class,
        Models\Orders\Order::class => Policies\OrderPolicy::class,
        /** Items */
        Models\Menu::class => Policies\MenuPolicy::class,
        Models\Product::class => Policies\ProductPolicy::class,
        Models\Service::class => Policies\ServicePolicy::class,
        Models\Space::class => Policies\SpacePolicy::class,
        Models\Ticket::class => Policies\TicketPolicy::class,
        /** Morphs */
        Morphs\Category::class => Policies\CategoryPolicy::class,
        Morphs\Comment::class => Policies\CommentPolicy::class,
        Morphs\Discount::class => Policies\DiscountPolicy::class,
        /** Other */
        \Spatie\Permission\Models\Role::class => Policies\RolePolicy::class,
        Models\Notification::class => Policies\NotificationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::extend('signature', function (Application $app, $name, $config) {
            return new SignatureGuard(
                Auth::createUserProvider($config['provider']),
                $app->make('request'),
                $app->make(SignatureHelper::class),
            );
        });
    }
}
