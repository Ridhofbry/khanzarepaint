<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Booking;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ServiceTrendsWidget extends ChartWidget
{
    protected static ?string $heading = 'Service Trends';
    protected static ?string $description = 'Repaint vs General Services (Last 30 Days)';
    protected static ?int $columnSpan = 2;

    protected function getData(): array
    {
        $last30Days = now()->subDays(30);

        // Get data grouped by service type and date
        $repaintData = Booking::where('status', 'completed')
            ->where('created_at', '>=', $last30Days)
            ->whereHas('service', function ($query) {
                $query->where('type', 'repaint');
            })
            ->groupBy(DB::raw('DATE(created_at)'))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->orderBy('date')
            ->get()
            ->pluck('count')
            ->toArray();

        $generalData = Booking::where('status', 'completed')
            ->where('created_at', '>=', $last30Days)
            ->whereHas('service', function ($query) {
                $query->where('type', 'general');
            })
            ->groupBy(DB::raw('DATE(created_at)'))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->orderBy('date')
            ->get()
            ->pluck('count')
            ->toArray();

        // Generate date labels
        $dates = [];
        $current = $last30Days->copy();
        while ($current <= now()) {
            $dates[] = $current->format('d M');
            $current->addDay();
        }

        // Pad arrays to match date count
        $repaintData = array_pad($repaintData, count($dates), 0);
        $generalData = array_pad($generalData, count($dates), 0);

        return [
            'datasets' => [
                [
                    'label' => 'Repaint Services',
                    'data' => array_slice($repaintData, -30),
                    'borderColor' => '#FF0000',
                    'backgroundColor' => 'rgba(255, 0, 0, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'General Services',
                    'data' => array_slice($generalData, -30),
                    'borderColor' => '#0EA5E9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => array_slice($dates, -30),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 10,
                ],
            ],
        ];
    }
}
