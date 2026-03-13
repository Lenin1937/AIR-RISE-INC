<?php

namespace App\Http\Controllers\Admin\Email;

use App\Http\Controllers\Controller;
use App\Models\EmailCampaign;
use App\Models\EmailContact;
use App\Models\EmailTemplate;
use App\Models\EmailAutomation;
use App\Models\EmailSegment;
use Inertia\Inertia;

class EmailDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_contacts'     => EmailContact::count(),
            'subscribed'         => EmailContact::subscribed()->count(),
            'total_campaigns'    => EmailCampaign::count(),
            'sent_campaigns'     => EmailCampaign::where('status', 'sent')->count(),
            'total_templates'    => EmailTemplate::count(),
            'active_automations' => EmailAutomation::where('status', 'active')->count(),
            'total_sent'         => EmailCampaign::sum('total_sent'),
            'avg_open_rate'      => $this->avgOpenRate(),
            'avg_click_rate'     => $this->avgClickRate(),
        ];

        $recentCampaigns = EmailCampaign::with('template', 'segment')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($c) => [
                'id'         => $c->id,
                'name'       => $c->name,
                'status'     => $c->status,
                'sent'       => $c->total_sent,
                'open_rate'  => $c->open_rate,
                'click_rate' => $c->click_rate,
                'sent_at'    => $c->sent_at?->format('M j, Y'),
                'segment'    => $c->segment?->name ?? 'All Contacts',
            ]);

        $recentContacts = EmailContact::latest()->take(6)->get()->map(fn($c) => [
            'id'          => $c->id,
            'email'       => $c->email,
            'full_name'   => $c->full_name,
            'client_type' => $c->client_type,
            'service_type'=> $c->service_type,
            'created_at'  => $c->created_at->format('M j'),
        ]);

        return Inertia::render('Admin/Email/Index', compact('stats', 'recentCampaigns', 'recentContacts'));
    }

    private function avgOpenRate(): float
    {
        $campaigns = EmailCampaign::where('status', 'sent')->where('total_delivered', '>', 0)->get();
        if ($campaigns->isEmpty()) return 0;
        return round($campaigns->avg(fn($c) => $c->open_rate), 1);
    }

    private function avgClickRate(): float
    {
        $campaigns = EmailCampaign::where('status', 'sent')->where('total_delivered', '>', 0)->get();
        if ($campaigns->isEmpty()) return 0;
        return round($campaigns->avg(fn($c) => $c->click_rate), 1);
    }
}
