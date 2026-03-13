<?php

namespace App\Http\Controllers\Admin\Email;

use App\Http\Controllers\Controller;
use App\Models\EmailCampaign;
use App\Models\EmailContact;
use App\Models\EmailSegment;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class EmailCampaignController extends Controller
{
    public function index()
    {
        $campaigns = EmailCampaign::with('template', 'segment')
            ->latest()
            ->paginate(20);

        $mapped = $campaigns->through(fn($c) => [
            'id'          => $c->id,
            'name'        => $c->name,
            'status'      => $c->status,
            'segment'     => $c->segment?->name ?? 'All Contacts',
            'template'    => $c->template?->name ?? '—',
            'from_name'   => $c->from_name,
            'from_email'  => $c->from_email,
            'total_sent'  => $c->total_sent,
            'open_rate'   => $c->open_rate,
            'click_rate'  => $c->click_rate,
            'scheduled_at'=> $c->scheduled_at?->format('M j, Y H:i'),
            'sent_at'     => $c->sent_at?->format('M j, Y'),
            'created_at'  => $c->created_at->format('M j, Y'),
        ]);

        $stats = [
            'total'      => EmailCampaign::count(),
            'sent'       => EmailCampaign::where('status', 'sent')->count(),
            'scheduled'  => EmailCampaign::where('status', 'scheduled')->count(),
            'draft'      => EmailCampaign::where('status', 'draft')->count(),
            'total_sent' => (int) EmailCampaign::sum('total_sent'),
        ];

        return Inertia::render('Admin/Email/Campaigns/Index', [
            'campaigns' => $mapped,
            'stats'     => $stats,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Email/Campaigns/Create', [
            'templates' => EmailTemplate::select('id', 'name', 'subject', 'category')->get(),
            'segments'  => EmailSegment::select('id', 'name', 'contact_count')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'template_id'  => 'nullable|exists:email_templates,id',
            'segment_id'   => 'nullable|exists:email_segments,id',
            'from_name'    => 'required|string|max:100',
            'from_email'   => 'required|email',
            'reply_to'     => 'nullable|email',
            'scheduled_at' => 'nullable|date|after:now',
        ]);
        $data['created_by'] = auth()->id();
        $data['status']     = $data['scheduled_at'] ? 'scheduled' : 'draft';

        $campaign = EmailCampaign::create($data);
        return redirect()->route('admin.email.campaigns.index')
            ->with('success', 'Campaign created.');
    }

    public function show(EmailCampaign $campaign)
    {
        $campaign->load('template', 'segment');
        return Inertia::render('Admin/Email/Campaigns/Show', [
            'campaign' => [
                'id'          => $campaign->id,
                'name'        => $campaign->name,
                'status'      => $campaign->status,
                'from_name'   => $campaign->from_name,
                'from_email'  => $campaign->from_email,
                'reply_to'    => $campaign->reply_to,
                'segment'     => $campaign->segment?->name ?? 'All Contacts',
                'template'    => $campaign->template?->name,
                'scheduled_at'=> $campaign->scheduled_at?->format('M j, Y H:i'),
                'sent_at'     => $campaign->sent_at?->format('M j, Y'),
                'total_sent'  => $campaign->total_sent,
                'total_delivered'=> $campaign->total_delivered,
                'total_opened'=> $campaign->total_opened,
                'total_clicked'=> $campaign->total_clicked,
                'total_bounced'=> $campaign->total_bounced,
                'open_rate'   => $campaign->open_rate,
                'click_rate'  => $campaign->click_rate,
                'created_at'  => $campaign->created_at->format('M j, Y'),
            ],
        ]);
    }

    public function send(EmailCampaign $campaign)
    {
        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            return back()->with('error', 'Campaign cannot be sent in its current state.');
        }

        // Resolve contacts
        $contacts = $campaign->segment_id
            ? EmailSegment::find($campaign->segment_id)?->resolveContacts()->subscribed()->get()
            : EmailContact::subscribed()->get();

        if (!$contacts || $contacts->isEmpty()) {
            return back()->with('error', 'No subscribed contacts in this segment.');
        }

        $template = $campaign->template;
        if (!$template) {
            return back()->with('error', 'No template attached to this campaign.');
        }

        // Mark as sending
        $campaign->update(['status' => 'sending', 'total_sent' => $contacts->count()]);

        // Queue actual sends (simplified — dispatch jobs in production)
        dispatch(function () use ($campaign, $contacts, $template) {
            foreach ($contacts as $contact) {
                try {
                    $html = str_replace(
                        ['{{FirstName}}', '{{LastName}}', '{{Email}}'],
                        [$contact->first_name ?? 'there', $contact->last_name ?? '', $contact->email],
                        $template->body_html
                    );
                    Mail::html($html, fn($m) => $m
                        ->to($contact->email, $contact->full_name)
                        ->from($campaign->from_email, $campaign->from_name)
                        ->subject($template->subject)
                    );
                    \App\Models\EmailCampaignEvent::create([
                        'campaign_id'   => $campaign->id,
                        'contact_email' => $contact->email,
                        'event_type'    => 'sent',
                    ]);
                } catch (\Exception $e) {
                    // Log bounce
                }
            }
            $campaign->update(['status' => 'sent', 'sent_at' => now(), 'total_delivered' => $campaign->total_sent]);
        })->afterResponse();

        return back()->with('success', "Campaign is being sent to {$contacts->count()} contacts.");
    }

    public function destroy(EmailCampaign $campaign)
    {
        $campaign->delete();
        return back()->with('success', 'Campaign deleted.');
    }
}
