<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;

class ActiveBookingsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $activeBookings = Booking::whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->count();

        $todayBookings = Booking::whereDate('scheduled_date', today())
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->count();

        $completedThisMonth = Booking::where('status', 'completed')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        $totalCustomers = User::where('role', 'customer')->count();

        return [
            Stat::make('Active Bookings', $activeBookings)
                ->description('Currently pending or in progress')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info')
                ->chart([1, 2, 3, 4, 5, 6, 7]) // Mock data
                ->icon('heroicon-o-calendar'),

            Stat::make('Today\'s Schedule', $todayBookings)
                ->description('Bookings scheduled for today')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->icon('heroicon-o-clock'),

            Stat::make('Completed This Month', $completedThisMonth)
                ->description(Carbon::now()->format('F'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Total Customers', $totalCustomers)
                ->description('Active registered customers')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->icon('heroicon-o-users'),
        ];
    }
}
