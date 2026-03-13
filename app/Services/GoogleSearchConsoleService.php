<?php

namespace App\Services;

use Google\Client;
use Google\Service\SearchConsole;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleSearchConsoleService
{
    protected ?SearchConsole $service = null;
    protected string $siteUrl;
    protected bool $configured = false;

    public function __construct()
    {
        $this->siteUrl  = config('services.google_search_console.site_url', '');
        $credPath       = config('services.google_search_console.credentials_path', '');

        if (empty($this->siteUrl) || empty($credPath) || !file_exists($credPath)) {
            return;
        }

        try {
            $client = new Client();
            $client->setAuthConfig($credPath);
            $client->addScope(SearchConsole::WEBMASTERS_READONLY);
            $this->service    = new SearchConsole($client);
            $this->configured = true;
        } catch (Exception $e) {
            Log::error('GoogleSearchConsoleService init failed: ' . $e->getMessage());
        }
    }

    public function isConfigured(): bool
    {
        return $this->configured;
    }

    /**
     * Get top search queries with impressions, clicks, CTR, and position.
     */
    public function getTopQueries(
        string $startDate,
        string $endDate,
        string $country      = '',
        string $searchType   = 'web',
        int    $rowLimit     = 25
    ): array {
        if (!$this->configured) {
            return $this->demoQueries();
        }

        try {
            $request = new SearchConsole\SearchAnalyticsQueryRequest();
            $request->setStartDate($startDate);
            $request->setEndDate($endDate);
            $request->setDimensions(['query']);
            $request->setType($searchType);
            $request->setRowLimit($rowLimit);

            if ($country) {
                $filter = new SearchConsole\ApiDimensionFilter();
                $filter->setDimension('country');
                $filter->setOperator('equals');
                $filter->setExpression(strtolower($country));
                $group = new SearchConsole\ApiDimensionFilterGroup();
                $group->setFilters([$filter]);
                $request->setDimensionFilterGroups([$group]);
            }

            $response = $this->service->searchanalytics->query($this->siteUrl, $request);
            $rows     = [];

            foreach ($response->getRows() ?? [] as $row) {
                $rows[] = [
                    'query'       => $row->getKeys()[0] ?? '',
                    'clicks'      => $row->getClicks(),
                    'impressions' => $row->getImpressions(),
                    'ctr'         => round($row->getCtr() * 100, 2),
                    'position'    => round($row->getPosition(), 1),
                ];
            }

            return $rows;
        } catch (Exception $e) {
            Log::error('GSC getTopQueries error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get top pages with impressions, clicks, CTR, and position.
     */
    public function getTopPages(
        string $startDate,
        string $endDate,
        string $country    = '',
        string $searchType = 'web',
        int    $rowLimit   = 25
    ): array {
        if (!$this->configured) {
            return $this->demoPages();
        }

        try {
            $request = new SearchConsole\SearchAnalyticsQueryRequest();
            $request->setStartDate($startDate);
            $request->setEndDate($endDate);
            $request->setDimensions(['page']);
            $request->setType($searchType);
            $request->setRowLimit($rowLimit);

            if ($country) {
                $filter = new SearchConsole\ApiDimensionFilter();
                $filter->setDimension('country');
                $filter->setOperator('equals');
                $filter->setExpression(strtolower($country));
                $group = new SearchConsole\ApiDimensionFilterGroup();
                $group->setFilters([$filter]);
                $request->setDimensionFilterGroups([$group]);
            }

            $response = $this->service->searchanalytics->query($this->siteUrl, $request);
            $rows     = [];

            foreach ($response->getRows() ?? [] as $row) {
                $url       = $row->getKeys()[0] ?? '';
                $parsedUrl = parse_url($url, PHP_URL_PATH) ?: $url;

                $rows[] = [
                    'page'        => $parsedUrl,
                    'full_url'    => $url,
                    'clicks'      => $row->getClicks(),
                    'impressions' => $row->getImpressions(),
                    'ctr'         => round($row->getCtr() * 100, 2),
                    'position'    => round($row->getPosition(), 1),
                ];
            }

            return $rows;
        } catch (Exception $e) {
            Log::error('GSC getTopPages error: ' . $e->getMessage());
            return [];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Demo data
    // ─────────────────────────────────────────────────────────────────────────

    private function demoQueries(): array
    {
        return [
            ['query' => 'corpius incorporation',          'clicks' => 842, 'impressions' => 9300,  'ctr' => 9.05, 'position' => 2.1],
            ['query' => 'us company formation online',    'clicks' => 560, 'impressions' => 14200, 'ctr' => 3.94, 'position' => 4.8],
            ['query' => 'llc vs c-corp',                  'clicks' => 430, 'impressions' => 11800, 'ctr' => 3.64, 'position' => 5.2],
            ['query' => 'register c corp usa',             'clicks' => 390, 'impressions' => 8100,  'ctr' => 4.81, 'position' => 3.5],
            ['query' => 'delaware llc formation',         'clicks' => 340, 'impressions' => 7200,  'ctr' => 4.72, 'position' => 3.1],
            ['query' => 'incorporate business online',    'clicks' => 310, 'impressions' => 9800,  'ctr' => 3.16, 'position' => 6.4],
            ['query' => 'wyoming llc non resident',       'clicks' => 285, 'impressions' => 5400,  'ctr' => 5.28, 'position' => 2.8],
            ['query' => 'open us bank account online',    'clicks' => 260, 'impressions' => 6700,  'ctr' => 3.88, 'position' => 7.1],
            ['query' => 'ein number for foreigners',      'clicks' => 240, 'impressions' => 5100,  'ctr' => 4.71, 'position' => 4.0],
            ['query' => 'corpius price',                  'clicks' => 215, 'impressions' => 2800,  'ctr' => 7.68, 'position' => 1.9],
        ];
    }

    private function demoPages(): array
    {
        return [
            ['page' => '/',                 'full_url' => 'https://corpius.net/',                 'clicks' => 1240, 'impressions' => 18400, 'ctr' => 6.74, 'position' => 2.8],
            ['page' => '/services',         'full_url' => 'https://corpius.net/services',         'clicks' => 890,  'impressions' => 14200, 'ctr' => 6.27, 'position' => 3.2],
            ['page' => '/pricing',          'full_url' => 'https://corpius.net/pricing',          'clicks' => 720,  'impressions' => 11300, 'ctr' => 6.37, 'position' => 4.1],
            ['page' => '/about',            'full_url' => 'https://corpius.net/about',            'clicks' => 430,  'impressions' => 9200,  'ctr' => 4.67, 'position' => 5.6],
            ['page' => '/knowledge-base',   'full_url' => 'https://corpius.net/knowledge-base',   'clicks' => 380,  'impressions' => 8700,  'ctr' => 4.37, 'position' => 6.3],
            ['page' => '/register',         'full_url' => 'https://corpius.net/register',         'clicks' => 310,  'impressions' => 5900,  'ctr' => 5.25, 'position' => 3.8],
            ['page' => '/login',            'full_url' => 'https://corpius.net/login',            'clicks' => 275,  'impressions' => 4200,  'ctr' => 6.55, 'position' => 2.5],
        ];
    }
}
