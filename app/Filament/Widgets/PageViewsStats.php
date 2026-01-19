<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

/**
 * Page views statistics widget.
 *
 * Shows website traffic metrics.
 */
class PageViewsStats extends StatsOverviewWidget
{
    protected ?string $heading = 'Tráfico del Sitio';

    protected ?string $pollingInterval = '60s';

    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        // Today's views
        $viewsToday = PageView::publicPages()
            ->where('created_at', '>=', $today)
            ->count();

        // Today's unique visitors
        $uniqueToday = PageView::publicPages()
            ->where('created_at', '>=', $today)
            ->distinct('session_hash')
            ->count('session_hash');

        // Yesterday comparison
        $viewsYesterday = PageView::publicPages()
            ->whereBetween('created_at', [$yesterday, $today])
            ->count();

        // This week
        $viewsThisWeek = PageView::publicPages()
            ->where('created_at', '>=', $thisWeek)
            ->count();

        // This month
        $viewsThisMonth = PageView::publicPages()
            ->where('created_at', '>=', $thisMonth)
            ->count();

        // Top pages today
        $topPage = PageView::publicPages()
            ->select('path', DB::raw('count(*) as views'))
            ->where('created_at', '>=', $today)
            ->groupBy('path')
            ->orderByDesc('views')
            ->first();

        $topPageLabel = $topPage ? $topPage->path : 'Sin datos';

        // Trend calculation
        $trend = $viewsYesterday > 0
            ? (($viewsToday - $viewsYesterday) / $viewsYesterday) * 100
            : ($viewsToday > 0 ? 100 : 0);

        return [
            Stat::make('Visitas Hoy', Number::format($viewsToday))
                ->description($this->formatTrend($trend))
                ->descriptionIcon($trend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($trend >= 0 ? 'success' : 'danger')
                ->chart($this->getViewsChartData()),

            Stat::make('Visitantes Únicos', Number::format($uniqueToday))
                ->description('Hoy')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Visitas Semana', Number::format($viewsThisWeek))
                ->description('Esta semana')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),

            Stat::make('Página Top', $topPageLabel)
                ->description($topPage ? $topPage->views.' visitas hoy' : 'Sin datos')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
        ];
    }

    /**
     * Get chart data for views trend (last 7 days).
     */
    protected function getViewsChartData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $data[] = PageView::publicPages()
                ->whereDate('created_at', $date)
                ->count();
        }

        return $data;
    }

    private function formatTrend(float $percentage): string
    {
        $sign = $percentage >= 0 ? '+' : '';

        return $sign.number_format($percentage, 0).'% vs ayer';
    }
}
