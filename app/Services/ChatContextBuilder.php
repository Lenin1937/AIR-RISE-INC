<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Request;

class ChatContextBuilder
{
    /**
     * Build context array for AI chat based on current state
     */
    public function buildContext(?User $user = null, array $additionalContext = []): array
    {
        $context = [];

        // Add page context
        $context['page'] = $additionalContext['page'] ?? Request::url();
        $context['page_type'] = $this->detectPageType($context['page']);
        
        // Add user context if authenticated
        if ($user) {
            $context['user'] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->hasRole('administrator'),
            ];

            // Add user-specific data for dashboard pages
            if ($context['page_type'] === 'dashboard') {
                $context['user']['orders_count'] = Order::where('user_id', $user->id)->count();
                $context['user']['pending_orders'] = Order::where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->count();
            }
        }

        // Add timestamp
        $context['timestamp'] = now()->toDateTimeString();

        // Merge any additional context
        return array_merge($context, $additionalContext);
    }

    /**
     * Detect page type from URL
     */
    private function detectPageType(string $url): string
    {
        if (str_contains($url, '/services/c-corporation')) {
            return 'service_c_corp';
        }
        if (str_contains($url, '/services/s-corporation')) {
            return 'service_s_corp';
        }
        if (str_contains($url, '/services/llc')) {
            return 'service_llc';
        }
        if (str_contains($url, '/services/nonprofit')) {
            return 'service_nonprofit';
        }
        if (str_contains($url, '/services/green-card')) {
            return 'service_greencard';
        }
        if (str_contains($url, '/pricing')) {
            return 'pricing';
        }
        if (str_contains($url, '/about')) {
            return 'about';
        }
        if (str_contains($url, '/contact')) {
            return 'contact';
        }
        if (str_contains($url, '/knowledge-base')) {
            return 'knowledge_base';
        }
        if (str_contains($url, '/dashboard')) {
            return 'dashboard';
        }
        if (str_contains($url, '/admin')) {
            return 'admin';
        }

        return 'home';
    }

    /**
     * Get page-specific knowledge for AI
     */
    public function getPageKnowledge(string $pageType): array
    {
        $knowledge = [
            'service_c_corp' => [
                'title' => 'C-Corporation Formation',
                'benefits' => [
                    'Unlimited shareholders',
                    'Separate legal entity',
                    'Limited liability protection',
                    'Easier to raise capital',
                    'Perpetual existence'
                ],
                'best_for' => 'Large businesses planning to go public or seek venture capital',
                'starting_price' => '$299'
            ],
            'service_s_corp' => [
                'title' => 'S-Corporation Formation',
                'benefits' => [
                    'Pass-through taxation',
                    'Avoid double taxation',
                    'Limited to 100 shareholders',
                    'Self-employment tax savings',
                    'Limited liability protection'
                ],
                'best_for' => 'Small to medium businesses wanting tax benefits',
                'starting_price' => '$299'
            ],
            'service_llc' => [
                'title' => 'LLC Formation',
                'benefits' => [
                    'Simple structure',
                    'Flexible management',
                    'Pass-through taxation',
                    'Limited liability protection',
                    'Less paperwork'
                ],
                'best_for' => 'Small businesses, freelancers, and startups',
                'starting_price' => '$149'
            ],
            'service_nonprofit' => [
                'title' => 'Nonprofit 501(c)(3) Formation',
                'benefits' => [
                    'Tax-exempt status',
                    'Eligible for grants',
                    'Tax-deductible donations',
                    'Limited liability',
                    'Credibility'
                ],
                'best_for' => 'Charitable, religious, educational, or scientific organizations',
                'starting_price' => '$399'
            ],
            'service_greencard' => [
                'title' => 'Green Card Lottery Assistance',
                'benefits' => [
                    'Expert application review',
                    'Photo compliance check',
                    'Document preparation',
                    'Application tracking',
                    'Higher success rate'
                ],
                'best_for' => 'Individuals seeking permanent US residency',
                'starting_price' => '$199'
            ],
        ];

        return $knowledge[$pageType] ?? [];
    }
}
