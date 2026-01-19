<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\RaffleStatus;
use App\Models\Order;
use App\Models\Raffle;
use App\Models\Ticket;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

/**
 * Dashboard stats overview widget.
 *
 * Shows key business metrics for administrators.
 */
class StatsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Get totals for current month
        $thisMonth = now()->startOfMonth();

        // Orders stats
        $totalOrders = Order::count();
        $paidOrders = Order::where('status', OrderStatus::Paid)->count();
        $ordersThisMonth = Order::where('created_at', '>=', $thisMonth)->count();

        // Revenue stats
        $totalRevenue = (float) Order::where('status', OrderStatus::Paid)->sum('total');
        $revenueThisMonth = (float) Order::where('status', OrderStatus::Paid)
            ->where('paid_at', '>=', $thisMonth)
            ->sum('total');

        // Tickets stats (tickets with order_id are sold)
        $ticketsSold = Ticket::whereNotNull('order_id')->count();

        // Raffles stats
        $activeRaffles = Raffle::where('status', RaffleStatus::Active)->count();

        // Pending payments
        $pendingPayments = Order::where('status', OrderStatus::Pending)->count();

        // Users stats
        $totalUsers = User::count();
        $usersThisMonth = User::where('created_at', '>=', $thisMonth)->count();

        return [
            Stat::make('Órdenes Pagadas', Number::format($paidOrders))
                ->description($ordersThisMonth.' este mes')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success')
                ->chart($this->getOrdersChartData()),

            Stat::make('Ingresos Totales', '$'.Number::format($totalRevenue, locale: 'es_CO'))
                ->description('$'.Number::format($revenueThisMonth, locale: 'es_CO').' este mes')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($this->getRevenueChartData()),

            Stat::make('Boletos Vendidos', Number::format($ticketsSold))
                ->description($activeRaffles.' sorteos activos')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('info'),

            Stat::make('Pagos Pendientes', Number::format($pendingPayments))
                ->description('Esperando confirmación')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingPayments > 0 ? 'warning' : 'gray'),

            Stat::make('Usuarios Registrados', Number::format($totalUsers))
                ->description($usersThisMonth.' nuevos este mes')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Sorteos Activos', Number::format($activeRaffles))
                ->description('En venta actualmente')
                ->descriptionIcon('heroicon-m-gift')
                ->color('info'),
        ];
    }

    /**
     * Get chart data for orders trend (last 7 days).
     */
    protected function getOrdersChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $data[] = Order::where('status', OrderStatus::Paid)
                ->whereDate('paid_at', $date)
                ->count();
        }

        return $data;
    }

    /**
     * Get chart data for revenue trend (last 7 days).
     */
    protected function getRevenueChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $data[] = (int) Order::where('status', OrderStatus::Paid)
                ->whereDate('paid_at', $date)
                ->sum('total') / 1000; // Scale down for chart
        }

        return $data;
    }
}
