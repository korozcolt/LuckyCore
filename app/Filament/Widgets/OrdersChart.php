<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\ChartWidget;

/**
 * Orders trend chart widget.
 *
 * Shows orders and revenue over the last 30 days.
 */
class OrdersChart extends ChartWidget
{
    protected ?string $heading = 'Tendencia de Órdenes';

    protected ?string $pollingInterval = '60s';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $ordersData = [];
        $revenueData = [];
        $labels = [];

        // Get data for last 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateString = $date->toDateString();

            $ordersCount = Order::where('status', OrderStatus::Paid)
                ->whereDate('paid_at', $dateString)
                ->count();

            $revenue = Order::where('status', OrderStatus::Paid)
                ->whereDate('paid_at', $dateString)
                ->sum('total');

            $ordersData[] = $ordersCount;
            $revenueData[] = round($revenue / 1000); // In thousands
            $labels[] = $date->format('d M');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Órdenes Pagadas',
                    'data' => $ordersData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Ingresos (miles $)',
                    'data' => $revenueData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $labels,
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
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Órdenes',
                    ],
                    'beginAtZero' => true,
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Ingresos (miles $)',
                    ],
                    'beginAtZero' => true,
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}
