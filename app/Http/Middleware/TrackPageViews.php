<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to track page views for analytics.
 *
 * Only tracks GET requests to public pages (excludes admin, livewire, api).
 */
class TrackPageViews
{
    /**
     * Paths to exclude from tracking.
     */
    private array $excludedPaths = [
        'admin*',
        'livewire*',
        'api*',
        '_debugbar*',
        'favicon.ico',
        'robots.txt',
        'sitemap.xml',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track successful GET requests
        if (
            $request->isMethod('GET') &&
            $response->isSuccessful() &&
            ! $this->shouldExclude($request)
        ) {
            $this->trackPageView($request);
        }

        return $response;
    }

    /**
     * Check if the request should be excluded from tracking.
     */
    private function shouldExclude(Request $request): bool
    {
        $path = $request->path();

        foreach ($this->excludedPaths as $pattern) {
            if (fnmatch($pattern, $path)) {
                return true;
            }
        }

        // Exclude ajax/fetch requests
        if ($request->ajax() || $request->wantsJson()) {
            return true;
        }

        // Exclude bot traffic
        $userAgent = $request->userAgent() ?? '';
        if (preg_match('/bot|crawl|spider|slurp|googlebot|bingbot/i', $userAgent)) {
            return true;
        }

        return false;
    }

    /**
     * Record the page view.
     */
    private function trackPageView(Request $request): void
    {
        try {
            // Create unique session hash for visitor tracking
            $sessionId = $request->session()->getId();
            $sessionHash = hash('sha256', $sessionId.$request->ip());

            PageView::create([
                'path' => '/'.ltrim($request->path(), '/'),
                'session_hash' => $sessionHash,
                'user_id' => Auth::id(),
                'ip_hash' => hash('sha256', $request->ip() ?? ''),
                'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                'referrer' => substr($request->header('referer') ?? '', 0, 500),
                'device_type' => PageView::detectDeviceType($request->userAgent()),
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            // Silently fail - don't break the request for tracking errors
        }
    }
}
