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
     * @var array<class-string, class-string>
     */
    protected $policies = [
        /** People */
        Models\User::class => Policies\UserPolicy::class,
        /** Items */
        Models\Menu::class => Policies\MenuPolicy::class,
        Models\Product::class => Policies\ProductPolicy::class,
        Models\ProductVariant::class => Policies\ProductVariantPolicy::class,
        /** Morphs */
        Morphs\Log::class => Policies\LogPolicy::class,
        Morphs\Alteration::class => Policies\AlterationPolicy::class,
        Morphs\Category::class => Policies\CategoryPolicy::class,
        Morphs\Comment::class => Policies\CommentPolicy::class,
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
