<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PricingController extends Controller
{
    /**
     * Display the pricing page.
     */
    public function index(): Response
    {
        $services = [
            [
                'name' => 'LLC Formation',
                'description' => 'The most flexible option for entrepreneurs and freelancers',
                'starting_price' => 1480,
                'packages' => [
                    ['name' => 'Starter', 'price' => 1480],
                    ['name' => 'Standard', 'price' => 1560],
                    ['name' => 'Premium', 'price' => 1650]
                ]
            ],
            [
                'name' => 'C-Corporation',
                'description' => 'Perfect for businesses seeking strong investor credibility',
                'starting_price' => 580,
                'packages' => [
                    ['name' => 'Starter', 'price' => 580],
                    ['name' => 'Standard', 'price' => 610],
                    ['name' => 'Premium', 'price' => 680]
                ]
            ],
            [
                'name' => 'S-Corporation',
                'description' => 'Best for small businesses with pass-through taxation',
                'starting_price' => 880,
                'packages' => [
                    ['name' => 'Starter', 'price' => 880],
                    ['name' => 'Standard', 'price' => 900],
                    ['name' => 'Premium', 'price' => 950]
                ]
            ],
            [
                'name' => 'Nonprofit Organization',
                'description' => 'Build a mission-driven entity with tax-exempt benefits',
                'starting_price' => 499,
                'packages' => [
                    ['name' => 'Starter', 'price' => 499],
                    ['name' => 'Standard', 'price' => 520],
                    ['name' => 'Premium', 'price' => 580]
                ]
            ],
            [
                'name' => 'Green Card Services',
                'description' => 'Professional immigration assistance for U.S. residency',
                'starting_price' => 49,
                'packages' => [
                    ['name' => 'Basic', 'price' => 49],
                    ['name' => 'Family', 'price' => 89],
                    ['name' => 'Premium', 'price' => 149]
                ]
            ],
            [
                'name' => 'Income Tax Filing',
                'description' => 'Professional tax filing for corporations and individuals',
                'starting_price' => 175,
                'packages' => [
                    ['name' => 'Personal Tax', 'price' => 175],
                    ['name' => 'Corporate Tax', 'price' => 500],
                    ['name' => 'Payroll Taxes', 'price' => 300]
                ]
            ]
        ];

        return Inertia::render('Marketing/Pricing', [
            'services' => $services
        ]);
    }
}
