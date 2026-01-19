<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

/**
 * Today's sales widget.
 *
 * Shows daily sales metrics for quick overview.
 */
class TodaySales extends StatsOverviewWidget
{
    protected ?string $heading = 'Ventas de Hoy';

    protected ?string $pollingInterval = '30s';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = now()->startOfDay();

        // Today's paid orders
        $ordersToday = Order::where('status', OrderStatus::Paid)
            ->where('paid_at', '>=', $today)
            ->count();

        // Today's revenue
        $revenueToday = (float) Order::where('status', OrderStatus::Paid)
            ->where('paid_at', '>=', $today)
            ->sum('total');

        // Today's tickets sold (count from orders paid today)
        $ticketsToday = Ticket::whereHas('order', function ($query) use ($today) {
            $query->where('status', OrderStatus::Paid)
                ->where('paid_at', '>=', $today);
        })->count();

        // Pending orders today
        $pendingToday = Order::where('status', OrderStatus::Pending)
            ->where('created_at', '>=', $today)
            ->count();

        // Compare with yesterday
        $yesterday = now()->subDay()->startOfDay();
        $yesterdayEnd = now()->startOfDay();

        $revenueYesterday = (float) Order::where('status', OrderStatus::Paid)
            ->whereBetween('paid_at', [$yesterday, $yesterdayEnd])
            ->sum('total');

        $ordersYesterday = Order::where('status', OrderStatus::Paid)
            ->whereBetween('paid_at', [$yesterday, $yesterdayEnd])
            ->count();

        // Calculate trends
        $revenueTrend = $this->calculateTrend($revenueToday, $revenueYesterday);
        $ordersTrend = $this->calculateTrend($ordersToday, $ordersYesterday);

        return [
            Stat::make('Órdenes Hoy', Number::format($ordersToday))
                ->description($this->formatTrend($ordersTrend))
                ->descriptionIcon($ordersTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ordersTrend >= 0 ? 'success' : 'danger'),

            Stat::make('Ingresos Hoy', '$'.Number::format($revenueToday, locale: 'es_CO'))
                ->description($this->formatTrend($revenueTrend))
                ->descriptionIcon($revenueTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueTrend >= 0 ? 'success' : 'danger'),

            Stat::make('Boletos Hoy', Number::format($ticketsToday))
                ->description('Vendidos hoy')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('info'),

            Stat::make('Pendientes Hoy', Number::format($pendingToday))
                ->description('Creados hoy')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingToday > 0 ? 'warning' : 'gray'),
        ];
    }

    private function calculateTrend(float $today, float $yesterday): float
    {
        if ($yesterday === 0.0) {
            return $today > 0 ? 100.0 : 0.0;
        }

        return (($today - $yesterday) / $yesterday) * 100;
    }

    private function formatTrend(float $percentage): string
    {
        $sign = $percentage >= 0 ? '+' : '';

        return $sign.number_format($percentage, 0).'% vs ayer';
    }
}
