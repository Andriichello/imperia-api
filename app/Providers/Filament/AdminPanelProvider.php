<?php

namespace App\Providers\Filament;

use App\Http\Controllers\Editor\EditorPageController;
use App\Http\Middleware\UseDefaultContentLocale;
use App\Http\Middleware\UsePanelAuthGuard;
use Filament\Forms\Components\DateTimePicker;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Tables\Table;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Class AdminPanelProvider.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdminPanelProvider extends PanelProvider
{
    /**
     * Dates and times across the admin: readable dates ("Mon, 5 Oct 2026") and 24-hour times,
     * picked with Filament's own picker instead of the browser's, which follows the browser's
     * locale (e.g. 10/05/2026, 9:00 AM).
     *
     * @return void
     */
    public function boot(): void
    {
        DateTimePicker::$defaultDateDisplayFormat = 'D, j M Y';
        DateTimePicker::$defaultDateTimeDisplayFormat = 'D, j M Y H:i';
        DateTimePicker::$defaultDateTimeWithSecondsDisplayFormat = 'D, j M Y H:i:s';
        DateTimePicker::$defaultTimeDisplayFormat = 'H:i';
        DateTimePicker::$defaultTimeWithSecondsDisplayFormat = 'H:i:s';

        // also applies to DatePicker and TimePicker, which extend it
        DateTimePicker::configureUsing(fn (DateTimePicker $picker) => $picker
            ->native(false)
            ->firstDayOfWeek(1)
            ->closeOnDateSelection(fn (DateTimePicker $component) => !$component->hasTime()));

        Table::$defaultDateDisplayFormat = 'j M Y';
        Table::$defaultDateTimeDisplayFormat = 'j M Y, H:i';
        Table::$defaultTimeDisplayFormat = 'H:i';
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            // the editor of the public page is a Vue app of its own, signed in like the panel
            ->authenticatedRoutes(function () {
                Route::get('/editor', [EditorPageController::class, 'index'])
                    ->name('editor.index');
                Route::get('/editor/{id}', [EditorPageController::class, 'show'])
                    ->whereNumber('id')
                    ->name('editor');
            })
            ->navigationItems([
                NavigationItem::make('Page editor')
                    ->icon('heroicon-o-paint-brush')
                    ->url(fn () => route('filament.admin.editor.index'))
                    ->visible(fn () => (bool) request()->user()?->isAdmin())
                    ->sort(-1),
            ])
            ->middleware([
                // first, so that the session middleware uses the panel's guard
                UsePanelAuthGuard::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            // also on Livewire's requests, which save the forms
            ->middleware([
                UseDefaultContentLocale::class,
            ], isPersistent: true)
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
