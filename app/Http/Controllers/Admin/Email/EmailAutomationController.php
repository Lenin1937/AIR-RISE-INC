<?php

namespace App\Http\Controllers\Admin\Email;

use App\Http\Controllers\Controller;
use App\Models\EmailAutomation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailAutomationController extends Controller
{
    public function index()
    {
        $automations = EmailAutomation::latest()->get()->map(fn($a) => [
            'id'              => $a->id,
            'name'            => $a->name,
            'description'     => $a->description,
            'trigger'         => $a->trigger,
            'status'          => $a->status,
            'step_count'      => $a->step_count,
            'enrolled_count'  => $a->enrolled_count,
            'completed_count' => $a->completed_count,
            'created_at'      => $a->created_at->format('M j, Y'),
        ]);

        $stats = [
            'total'    => EmailAutomation::count(),
            'active'   => EmailAutomation::where('status', 'active')->count(),
            'paused'   => EmailAutomation::where('status', 'paused')->count(),
            'enrolled' => (int) EmailAutomation::sum('enrolled_count'),
        ];

        return Inertia::render('Admin/Email/Automations/Index', [
            'automations' => $automations,
            'stats'       => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'trigger'             => 'required|in:new_contact,order_created,order_status_changed,document_uploaded,manual,tag_added,date_based',
            'trigger_conditions'  => 'nullable|array',
            'steps'               => 'required|array|min:1',
            'steps.*.delay_days'  => 'required|integer|min:0',
            'steps.*.subject'     => 'required|string',
            'steps.*.body_html'   => 'required|string',
        ]);
        $data['status'] = 'draft';
        EmailAutomation::create($data);
        return back()->with('success', 'Automation created.');
    }

    public function update(Request $request, EmailAutomation $automation)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,paused,draft',
            'steps'       => 'required|array|min:1',
        ]);
        $automation->update($data);
        return back()->with('success', 'Automation updated.');
    }

    public function toggleStatus(EmailAutomation $automation)
    {
        $automation->update([
            'status' => $automation->status === 'active' ? 'paused' : 'active',
        ]);
        return back()->with('success', 'Status updated.');
    }

    public function destroy(EmailAutomation $automation)
    {
        $automation->delete();
        return back()->with('success', 'Automation deleted.');
    }
}
