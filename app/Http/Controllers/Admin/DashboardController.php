<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Document;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): Response
    {
        // Cache dashboard stats for 5 minutes to improve performance
        $stats = cache()->remember('admin_dashboard_stats', 300, function () {
            return [
                'total_users' => User::role('client')->count(),
                'total_orders' => Order::count(),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'completed_orders' => Order::where('status', 'completed')->count(),
                'total_revenue' => round(Payment::where('status', 'succeeded')->sum('amount'), 2),
                'pending_documents' => Document::where('status', 'pending_review')->count(),
                'monthly_growth' => $this->calculateMonthlyGrowth(),
                'revenue_growth' => $this->calculateRevenueGrowth(),
                'revenue_change' => $this->calculateRevenueGrowth(),
                'new_users' => User::role('client')
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->count(),
                'customer_satisfaction' => 94.2,
            ];
        });

        // Cache recent orders for 2 minutes
        $recent_orders = cache()->remember('admin_recent_orders', 120, function () {
            return Order::with(['user:id,name,first_name,last_name,email,profile_picture'])
                ->select(['id', 'order_number', 'service_type', 'status', 'created_at', 'total_amount', 'user_id'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->order_number,
                        'service_type' => $order->service_type_name,
                        'status' => $order->status,
                        'created_at' => $order->created_at->toISOString(),
                        'total' => round($order->total_amount, 2),
                        'plan' => ['name' => $order->service_type_name],
                        'user' => [
                            'id'     => $order->user->id,
                            'name'   => $order->user->full_name,
                            'email'  => $order->user->email,
                            'avatar' => $order->user->profile_picture ? Storage::url($order->user->profile_picture) : null,
                        ],
                    ];
                });
        });

        // Cache recent users for 2 minutes
        $recent_users = cache()->remember('admin_recent_users', 120, function () {
            return User::role('client')
                ->withCount('orders')
                ->select(['id', 'name', 'first_name', 'last_name', 'email', 'email_verified_at', 'created_at', 'business_type', 'profile_picture'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => 'USR-' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                        'name' => $user->full_name,
                        'email' => $user->email,
                        'avatar' => $user->profile_picture ? Storage::url($user->profile_picture) : null,
                        'email_verified_at' => $user->email_verified_at?->toISOString(),
                        'created_at' => $user->created_at->toISOString(),
                        'account_type' => $user->business_type ?: 'Individual',
                        'orders_count' => $user->orders_count,
                    ];
                });
        });

        // Cache sales by state for 10 minutes
        $sales_by_state = cache()->remember('admin_sales_by_state', 600, function () {
            return $this->getSalesByState();
        });

        // Get order chart data (last 7 months) in the format the frontend expects
        $order_chart_data = $this->getMonthlyChartData();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recent_orders' => $recent_orders,
            'recent_users' => $recent_users,
            'sales_by_state' => $sales_by_state,
            'order_chart_data' => $order_chart_data,
        ]);
    }

    /**
     * Get sales data grouped by US state
     */
    private function getSalesByState(): array
    {
        // Get orders grouped by state
        $ordersByState = Order::select('state_of_incorporation')
            ->whereNotNull('state_of_incorporation')
            ->where('state_of_incorporation', '!=', '')
            ->get()
            ->groupBy('state_of_incorporation')
            ->map(function ($orders) {
                return $orders->count();
            })
            ->toArray();

        // Convert state abbreviations to uppercase and ensure valid format
        $formattedStates = [];
        foreach ($ordersByState as $state => $count) {
            $stateCode = strtoupper(trim($state));
            if (strlen($stateCode) === 2) {
                $formattedStates[$stateCode] = $count;
            }
        }

        // If no real data, show demo data to visualize the map
        if (empty($formattedStates)) {
            $formattedStates = [
                'CA' => 45,  // California - highest
                'TX' => 38,  // Texas
                'FL' => 32,  // Florida
                'NY' => 28,  // New York
                'IL' => 22,  // Illinois
                'PA' => 18,  // Pennsylvania
                'OH' => 15,  // Ohio
                'GA' => 12,  // Georgia
                'NC' => 10,  // North Carolina
                'MI' => 8,   // Michigan
                'NJ' => 7,   // New Jersey
                'VA' => 6,   // Virginia
                'WA' => 5,   // Washington
                'AZ' => 4,   // Arizona
                'MA' => 3,   // Massachusetts
            ];
        }

        return $formattedStates;
    }

    /**
     * Calculate monthly growth percentage
     */
    private function calculateMonthlyGrowth(): float
    {
        $thisMonth = User::role('client')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $lastMonth = User::role('client')
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->count();

        if ($lastMonth == 0) {
            return $thisMonth > 0 ? 100.0 : 0.0;
        }

        return round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1);
    }

    /**
     * Calculate revenue growth percentage
     */
    private function calculateRevenueGrowth(): float
    {
        $thisMonth = Payment::where('status', 'succeeded')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        $lastMonth = Payment::where('status', 'succeeded')
            ->whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('amount');

        if ($lastMonth == 0) {
            return $thisMonth > 0 ? 100.0 : 0.0;
        }

        return round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1);
    }

    /**
     * Get monthly chart data in {labels, data, revenue} format for the frontend.
     */
    private function getMonthlyChartData(): array
    {
        $now = now();
        $labels  = [];
        $orders  = [];
        $revenue = [];

        for ($i = 6; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $labels[] = $month->format('M');

            $orders[] = Order::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $revenue[] = round(
                Payment::where('status', 'succeeded')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->sum('amount'),
                2
            );
        }

        return ['labels' => $labels, 'data' => $orders, 'revenue' => $revenue];
    }

    /**
     * Get order chart data for different time periods
     */
    private function getOrderChartData(string $period): array
    {
        $now = now();
        
        if ($period === 'daily') {
            // Last 7 days
            $labels = [];
            $ordersData = [];
            $completedData = [];
            
            for ($i = 6; $i >= 0; $i--) {
                $date = $now->copy()->subDays($i);
                $labels[] = $date->format('M d');
                
                $ordersCount = Order::whereDate('created_at', $date)->count();
                $completedCount = Order::whereDate('created_at', $date)
                    ->where('status', 'completed')
                    ->count();
                
                $ordersData[] = $ordersCount;
                $completedData[] = $completedCount;
            }
            
        } elseif ($period === 'weekly') {
            // Last 7 weeks
            $labels = [];
            $ordersData = [];
            $completedData = [];
            
            for ($i = 6; $i >= 0; $i--) {
                $weekStart = $now->copy()->subWeeks($i)->startOfWeek();
                $weekEnd = $weekStart->copy()->endOfWeek();
                $labels[] = 'Week ' . $weekStart->format('M d');
                
                $ordersCount = Order::whereBetween('created_at', [$weekStart, $weekEnd])->count();
                $completedCount = Order::whereBetween('created_at', [$weekStart, $weekEnd])
                    ->where('status', 'completed')
                    ->count();
                
                $ordersData[] = $ordersCount;
                $completedData[] = $completedCount;
            }
            
        } else { // monthly
            // Last 7 months
            $labels = [];
            $ordersData = [];
            $completedData = [];
            
            for ($i = 6; $i >= 0; $i--) {
                $month = $now->copy()->subMonths($i);
                $labels[] = $month->format('M Y');
                
                $ordersCount = Order::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count();
                $completedCount = Order::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->where('status', 'completed')
                    ->count();
                
                $ordersData[] = $ordersCount;
                $completedData[] = $completedCount;
            }
        }
        
        return [
            'labels' => $labels,
            'orders' => $ordersData,
            'completed' => $completedData,
        ];
    }
}
