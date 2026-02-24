<?php

namespace App\Filament\Panels;

use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Auth\Middleware\AuthorizeRequests;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\BookingResource;
use App\Filament\Resources\CarGarageResource;
use App\Filament\Resources\VoucherResource;
use App\Filament\Resources\TestimonialResource;
use App\Filament\Resources\FAQResource;
use App\Filament\Widgets\ActiveBookingsWidget;
use App\Filament\Widgets\MonthlRevenueWidget;
use App\Filament\Widgets\ServiceTrendsWidget;

class AdminPanel extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'danger' => Color::Red,
                'gray' => Color::Slate,
                'info' => Color::Blue,
                'primary' => Color::Red,
                'success' => Color::Green,
                'warning' => Color::Amber,
            ])
            ->font('Inter')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                ActiveBookingsWidget::class,
                MonthlRevenueWidget::class,
                ServiceTrendsWidget::class,
            ])
            ->middleware([
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
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Core Management')
                    ->items([
                        NavigationItem::make('Dashboard')
                            ->icon('heroicon-o-home')
                            ->isActiveWhen(fn () => request()->routeIs('filament.admin.pages.dashboard'))
                            ->url(fn () => route('filament.admin.pages.dashboard')),
                    ]),
                NavigationGroup::make()
                    ->label('Bookings & Services')
                    ->items([
                        NavigationItem::make('Bookings')
                            ->icon('heroicon-o-calendar')
                            ->resource(BookingResource::class),
                    ]),
                NavigationGroup::make()
                    ->label('User Management')
                    ->items([
                        NavigationItem::make('Users')
                            ->icon('heroicon-o-users')
                            ->resource(UserResource::class),
                    ]),
                NavigationGroup::make()
                    ->label('Garage & Inventory')
                    ->items([
                        NavigationItem::make('Cars')
                            ->icon('heroicon-o-truck')
                            ->resource(CarGarageResource::class),
                    ]),
                NavigationGroup::make()
                    ->label('Marketing & Promotions')
                    ->items([
                        NavigationItem::make('Vouchers')
                            ->icon('heroicon-o-ticket')
                            ->resource(VoucherResource::class),
                    ]),
                NavigationGroup::make()
                    ->label('Content Management')
                    ->items([
                        NavigationItem::make('Testimonials')
                            ->icon('heroicon-o-star')
                            ->resource(TestimonialResource::class),
                        NavigationItem::make('FAQs')
                            ->icon('heroicon-o-question-mark-circle')
                            ->resource(FAQResource::class),
                    ]),
            ])
            ->brandName('Khanza Repaint')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('2.5rem')
            ->darkMode(true)
            ->databaseNotifications()
            ->databaseTransactions();
    }
}
