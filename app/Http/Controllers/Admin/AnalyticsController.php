<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleAnalyticsService;
use App\Services\GoogleSearchConsoleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function __construct(
        protected GoogleAnalyticsService $ga,
        protected GoogleSearchConsoleService $gsc
    ) {}

    /**
     * Main analytics dashboard page (SSR initial load with default 30-day data).
     */
    public function index()
    {
        $endDate   = Carbon::today()->format('Y-m-d');
        $startDate = Carbon::today()->subDays(29)->format('Y-m-d');

        return Inertia::render('Admin/Analytics/Index', [
            'ga_configured'  => $this->ga->isConfigured(),
            'gsc_configured' => $this->gsc->isConfigured(),

            // Initial data loads on first page render
            'initial' => [
                'metrics'            => $this->ga->getCoreMetrics($startDate, $endDate),
                'sessions_over_time' => $this->ga->getSessionsOverTime($startDate, $endDate, 'day'),
                'traffic_sources'    => $this->ga->getTrafficBySource($startDate, $endDate),
                'top_ga_pages'       => $this->ga->getTopPages($startDate, $endDate),
                'gsc_queries'        => $this->gsc->getTopQueries($startDate, $endDate),
                'gsc_pages'          => $this->gsc->getTopPages($startDate, $endDate),
                'start_date'         => $startDate,
                'end_date'           => $endDate,
            ],
        ]);
    }

    /**
     * AJAX endpoint – re-fetch all data for the given date range & filters.
     */
    public function data(Request $request)
    {
        $validated = $request->validate([
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'granularity' => 'nullable|in:day,week,month',
            'country'     => 'nullable|string|max:10',
            'search_type' => 'nullable|in:web,image,video,discover,news',
            'row_limit'   => 'nullable|integer|min:10|max:100',
        ]);

        $startDate   = $validated['start_date'];
        $endDate     = $validated['end_date'];
        $granularity = $validated['granularity'] ?? 'day';
        $country     = $validated['country']     ?? '';
        $searchType  = $validated['search_type'] ?? 'web';
        $rowLimit    = (int)($validated['row_limit'] ?? 25);

        return response()->json([
            'metrics'            => $this->ga->getCoreMetrics($startDate, $endDate),
            'sessions_over_time' => $this->ga->getSessionsOverTime($startDate, $endDate, $granularity),
            'traffic_sources'    => $this->ga->getTrafficBySource($startDate, $endDate),
            'top_ga_pages'       => $this->ga->getTopPages($startDate, $endDate),
            'gsc_queries'        => $this->gsc->getTopQueries($startDate, $endDate, $country, $searchType, $rowLimit),
            'gsc_pages'          => $this->gsc->getTopPages($startDate, $endDate, $country, $searchType, $rowLimit),
        ]);
    }
}
