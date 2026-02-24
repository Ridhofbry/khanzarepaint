<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Booking;
use Carbon\Carbon;

class MonthlRevenueWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $currentMonthRevenue = Booking::where('status', 'completed')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->sum('total_price');

        $lastMonthRevenue = Booking::where('status', 'completed')
            ->whereMonth('completed_at', now()->subMonth()->month)
            ->whereYear('completed_at', now()->subMonth()->year)
            ->sum('total_price');

        $totalRevenue = Booking::where('status', 'completed')
            ->sum('total_price');

        $averageBookingValue = Booking::where('status', 'completed')->avg('total_price') ?? 0;

        $percentageChange = $lastMonthRevenue > 0
            ? (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue * 100)
            : 0;

        return [
            Stat::make('Monthly Revenue', 'Rp ' . number_format($currentMonthRevenue, 0, '.', ','))
                ->description('This month (' . now()->format('F') . ')')
                ->descriptionIcon('heroicon-m-currency-rupiah')
                ->color($percentageChange >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-currency-rupiah'),

            Stat::make('Revenue Change', abs($percentageChange) . '%')
                ->description('Compared to last month')
                ->descriptionIcon($percentageChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($percentageChange >= 0 ? 'success' : 'danger')
                ->icon($percentageChange >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down'),

            Stat::make('Total Revenue', 'Rp ' . number_format($totalRevenue, 0, '.', ','))
                ->description('All-time revenue')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary')
                ->icon('heroicon-o-chart-bar'),

            Stat::make('Avg Booking Value', 'Rp ' . number_format($averageBookingValue, 0, '.', ','))
                ->description('Average per completed booking')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info')
                ->icon('heroicon-o-calculator'),
        ];
    }
}
