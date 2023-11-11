<?php

namespace App\Providers;

use App\Guards\SignatureGuard;
use App\Helpers\SignatureHelper;
use App\Models as Models;
use App\Models\Morphs as Morphs;
use App\Policies as Policies;
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
        Models\ProductVariant::class => Policies\ProductVariantPolicy::class,
        Models\Service::class => Policies\ServicePolicy::class,
        Models\Space::class => Policies\SpacePolicy::class,
        Models\Ticket::class => Policies\TicketPolicy::class,
        /** Morphs */
        Morphs\Log::class => Policies\LogPolicy::class,
        Morphs\Category::class => Policies\CategoryPolicy::class,
        Morphs\Comment::class => Policies\CommentPolicy::class,
        Morphs\Discount::class => Policies\DiscountPolicy::class,
        \Spatie\Permission\Models\Role::class => Policies\RolePolicy::class,
        /** Other */
        Models\Notification::class => Policies\NotificationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPolicies();

        $this->app->bind(SignatureGuard::class, function () {
            $provider = config('auth.guards.signature.provider', 'users');

            return new SignatureGuard(
                Auth::createUserProvider($provider),
                app('request'),
                app(SignatureHelper::class),
            );
        });

        Auth::extend('signature', function () {
            return app(SignatureGuard::class);
        });
    }

    /**
     * Boot any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
