<?php

namespace App\Services;

use Google\Client;
use Google\Service\AnalyticsData;
use Google\Service\AnalyticsData\DateRange;
use Google\Service\AnalyticsData\Dimension;
use Google\Service\AnalyticsData\Metric;
use Google\Service\AnalyticsData\RunReportRequest;
use Google\Service\AnalyticsData\OrderBy;
use Google\Service\AnalyticsData\DimensionOrderBy;
use Google\Service\AnalyticsData\FilterExpression;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class GoogleAnalyticsService
{
    protected ?AnalyticsData $analytics = null;
    protected string $propertyId;
    protected bool $configured = false;

    public function __construct()
    {
        $this->propertyId = config('services.google_analytics.property_id', '');
        $credPath = config('services.google_analytics.credentials_path', '');

        if (empty($this->propertyId) || empty($credPath) || !file_exists($credPath)) {
            return;
        }

        try {
            $client = new Client();
            $client->setAuthConfig($credPath);
            $client->addScope('https://www.googleapis.com/auth/analytics.readonly');
            $this->analytics = new AnalyticsData($client);
            $this->configured = true;
        } catch (Exception $e) {
            Log::error('GoogleAnalyticsService init failed: ' . $e->getMessage());
        }
    }

    public function isConfigured(): bool
    {
        return $this->configured;
    }

    /**
     * Get core KPI metrics (total sessions, users, conversions, bounce rate, session duration).
     */
    public function getCoreMetrics(string $startDate, string $endDate): array
    {
        if (!$this->configured) {
            return $this->demoMetrics();
        }

        try {
            $request = new RunReportRequest([
                'dateRanges'  => [new DateRange(['startDate' => $startDate, 'endDate' => $endDate])],
                'metrics'     => [
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'totalUsers']),
                    new Metric(['name' => 'conversions']),
                    new Metric(['name' => 'bounceRate']),
                    new Metric(['name' => 'averageSessionDuration']),
                ],
            ]);

            $response = $this->analytics->properties->runReport("properties/{$this->propertyId}", $request);
            $row = $response->getRows()[0] ?? null;

            if (!$row) {
                return $this->zeroMetrics();
            }

            $values = array_map(fn($v) => $v->getValue(), $row->getMetricValues());

            return [
                'sessions'            => (int)$values[0],
                'users'               => (int)$values[1],
                'conversions'         => (int)$values[2],
                'conversion_rate'     => $values[0] > 0 ? round($values[2] / $values[0] * 100, 2) : 0,
                'bounce_rate'         => round($values[3] * 100, 2),
                'avg_session_duration' => (int)$values[4],
            ];
        } catch (Exception $e) {
            Log::error('GA4 getCoreMetrics error: ' . $e->getMessage());
            return $this->zeroMetrics();
        }
    }

    /**
     * Get sessions grouped by date for chart.
     */
    public function getSessionsOverTime(string $startDate, string $endDate, string $granularity = 'day'): array
    {
        if (!$this->configured) {
            return $this->demoSessionsOverTime($startDate, $endDate);
        }

        $dimensionName = match ($granularity) {
            'week'  => 'isoWeek',
            'month' => 'yearMonth',
            default => 'date',
        };

        try {
            $request = new RunReportRequest([
                'dateRanges'  => [new DateRange(['startDate' => $startDate, 'endDate' => $endDate])],
                'dimensions'  => [new Dimension(['name' => $dimensionName])],
                'metrics'     => [
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'totalUsers']),
                ],
                'orderBys'    => [new OrderBy([
                    'dimension' => new DimensionOrderBy(['dimensionName' => $dimensionName]),
                    'desc'      => false,
                ])],
            ]);

            $response = $this->analytics->properties->runReport("properties/{$this->propertyId}", $request);

            $labels   = [];
            $sessions = [];
            $users    = [];

            foreach ($response->getRows() ?? [] as $row) {
                $labels[]   = $row->getDimensionValues()[0]->getValue();
                $sessions[] = (int)$row->getMetricValues()[0]->getValue();
                $users[]    = (int)$row->getMetricValues()[1]->getValue();
            }

            return compact('labels', 'sessions', 'users');
        } catch (Exception $e) {
            Log::error('GA4 getSessionsOverTime error: ' . $e->getMessage());
            return ['labels' => [], 'sessions' => [], 'users' => []];
        }
    }

    /**
     * Get traffic by source/medium.
     */
    public function getTrafficBySource(string $startDate, string $endDate): array
    {
        if (!$this->configured) {
            return $this->demoTrafficSources();
        }

        try {
            $request = new RunReportRequest([
                'dateRanges' => [new DateRange(['startDate' => $startDate, 'endDate' => $endDate])],
                'dimensions' => [
                    new Dimension(['name' => 'sessionSourceMedium']),
                ],
                'metrics' => [
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'totalUsers']),
                    new Metric(['name' => 'bounceRate']),
                    new Metric(['name' => 'conversions']),
                ],
                'orderBys' => [new OrderBy([
                    'metric' => new \Google\Service\AnalyticsData\MetricOrderBy(['metricName' => 'sessions']),
                    'desc'   => true,
                ])],
                'limit' => 20,
            ]);

            $response = $this->analytics->properties->runReport("properties/{$this->propertyId}", $request);
            $rows = [];

            foreach ($response->getRows() ?? [] as $row) {
                $sessions     = (int)$row->getMetricValues()[0]->getValue();
                $users        = (int)$row->getMetricValues()[1]->getValue();
                $bounceRate   = round($row->getMetricValues()[2]->getValue() * 100, 2);
                $conversions  = (int)$row->getMetricValues()[3]->getValue();

                $rows[] = [
                    'source'          => $row->getDimensionValues()[0]->getValue(),
                    'sessions'        => $sessions,
                    'users'           => $users,
                    'bounce_rate'     => $bounceRate,
                    'conversion_rate' => $sessions > 0 ? round($conversions / $sessions * 100, 2) : 0,
                ];
            }

            return $rows;
        } catch (Exception $e) {
            Log::error('GA4 getTrafficBySource error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get top landing pages.
     */
    public function getTopPages(string $startDate, string $endDate): array
    {
        if (!$this->configured) {
            return $this->demoTopPages();
        }

        try {
            $request = new RunReportRequest([
                'dateRanges' => [new DateRange(['startDate' => $startDate, 'endDate' => $endDate])],
                'dimensions' => [new Dimension(['name' => 'pagePath'])],
                'metrics'    => [
                    new Metric(['name' => 'screenPageViews']),
                    new Metric(['name' => 'sessions']),
                    new Metric(['name' => 'totalUsers']),
                    new Metric(['name' => 'bounceRate']),
                    new Metric(['name' => 'averageSessionDuration']),
                ],
                'orderBys' => [new OrderBy([
                    'metric' => new \Google\Service\AnalyticsData\MetricOrderBy(['metricName' => 'screenPageViews']),
                    'desc'   => true,
                ])],
                'limit' => 20,
            ]);

            $response = $this->analytics->properties->runReport("properties/{$this->propertyId}", $request);
            $rows = [];

            foreach ($response->getRows() ?? [] as $row) {
                $rows[] = [
                    'page'        => $row->getDimensionValues()[0]->getValue(),
                    'pageviews'   => (int)$row->getMetricValues()[0]->getValue(),
                    'sessions'    => (int)$row->getMetricValues()[1]->getValue(),
                    'users'       => (int)$row->getMetricValues()[2]->getValue(),
                    'bounce_rate' => round($row->getMetricValues()[3]->getValue() * 100, 2),
                    'avg_time'    => (int)$row->getMetricValues()[4]->getValue(),
                ];
            }

            return $rows;
        } catch (Exception $e) {
            Log::error('GA4 getTopPages error: ' . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Demo / fallback data (used when GA4 is not yet configured)
    // ─────────────────────────────────────────────────────────────────────────

    private function demoMetrics(): array
    {
        return [
            'sessions'            => 12847,
            'users'               => 9210,
            'conversions'         => 342,
            'conversion_rate'     => 2.66,
            'bounce_rate'         => 38.4,
            'avg_session_duration' => 187,
        ];
    }

    private function zeroMetrics(): array
    {
        return ['sessions' => 0, 'users' => 0, 'conversions' => 0, 'conversion_rate' => 0, 'bounce_rate' => 0, 'avg_session_duration' => 0];
    }

    private function demoSessionsOverTime(string $startDate, string $endDate): array
    {
        $labels = $sessions = $users = [];
        $start  = Carbon::parse($startDate);
        $end    = Carbon::parse($endDate);
        $days   = (int)$start->diffInDays($end);

        for ($i = 0; $i <= min($days, 29); $i++) {
            $labels[]   = $start->copy()->addDays($i)->format('Y-m-d');
            $sessions[] = rand(300, 700);
            $users[]    = rand(200, 500);
        }

        return compact('labels', 'sessions', 'users');
    }

    private function demoTrafficSources(): array
    {
        return [
            ['source' => 'google / organic',   'sessions' => 5820, 'users' => 4231, 'bounce_rate' => 35.2, 'conversion_rate' => 3.1],
            ['source' => 'direct / none',       'sessions' => 3140, 'users' => 2540, 'bounce_rate' => 42.1, 'conversion_rate' => 2.8],
            ['source' => 'google / cpc',        'sessions' => 1960, 'users' => 1720, 'bounce_rate' => 55.3, 'conversion_rate' => 4.2],
            ['source' => 'facebook / social',   'sessions' => 870,  'users' => 790,  'bounce_rate' => 61.0, 'conversion_rate' => 1.4],
            ['source' => 'bing / organic',      'sessions' => 540,  'users' => 490,  'bounce_rate' => 44.8, 'conversion_rate' => 2.0],
            ['source' => 'twitter / social',    'sessions' => 317,  'users' => 281,  'bounce_rate' => 68.2, 'conversion_rate' => 0.9],
            ['source' => 'linkedin / social',   'sessions' => 200,  'users' => 178,  'bounce_rate' => 50.1, 'conversion_rate' => 2.5],
        ];
    }

    private function demoTopPages(): array
    {
        return [
            ['page' => '/',                  'pageviews' => 4230, 'sessions' => 3100, 'users' => 2340, 'bounce_rate' => 35.0, 'avg_time' => 92],
            ['page' => '/services',          'pageviews' => 2810, 'sessions' => 2100, 'users' => 1870, 'bounce_rate' => 40.2, 'avg_time' => 145],
            ['page' => '/pricing',           'pageviews' => 2110, 'sessions' => 1780, 'users' => 1560, 'bounce_rate' => 28.5, 'avg_time' => 210],
            ['page' => '/about',             'pageviews' => 1340, 'sessions' => 1100, 'users' => 980,  'bounce_rate' => 52.3, 'avg_time' => 78],
            ['page' => '/register',          'pageviews' => 970,  'sessions' => 870,  'users' => 820,  'bounce_rate' => 22.1, 'avg_time' => 310],
            ['page' => '/knowledge-base',    'pageviews' => 840,  'sessions' => 730,  'users' => 680,  'bounce_rate' => 45.0, 'avg_time' => 185],
            ['page' => '/login',             'pageviews' => 760,  'sessions' => 680,  'users' => 640,  'bounce_rate' => 18.2, 'avg_time' => 95],
        ];
    }
}
