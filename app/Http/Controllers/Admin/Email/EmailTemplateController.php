<?php

namespace App\Http\Controllers\Admin\Email;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::with('creator')
            ->latest()
            ->get()
            ->map(fn($t) => [
                'id'           => $t->id,
                'name'         => $t->name,
                'subject'      => $t->subject,
                'category'     => $t->category,
                'ai_generated' => $t->ai_generated,
                'created_by'   => $t->creator?->name ?? 'System',
                'created_at'   => $t->created_at->format('M j, Y'),
                'updated_at'   => $t->updated_at->format('M j, Y'),
            ]);

        return Inertia::render('Admin/Email/Templates/Index', [
            'templates' => $templates,
            'stats' => [
                'total'         => EmailTemplate::count(),
                'marketing'     => EmailTemplate::where('category', 'marketing')->count(),
                'transactional' => EmailTemplate::where('category', 'transactional')->count(),
                'ai_generated'  => EmailTemplate::where('ai_generated', true)->count(),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Email/Templates/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'subject'      => 'required|string|max:255',
            'preheader'    => 'nullable|string|max:255',
            'body_html'    => 'required|string',
            'body_text'    => 'nullable|string',
            'category'     => 'required|in:transactional,marketing',
            'ai_generated' => 'boolean',
        ]);
        $data['created_by'] = auth()->id();
        $template = EmailTemplate::create($data);
        return redirect()->route('admin.email.templates.index')
            ->with('success', 'Template created successfully.');
    }

    public function edit(EmailTemplate $template)
    {
        return Inertia::render('Admin/Email/Templates/Create', [
            'template' => $template,
        ]);
    }

    public function update(Request $request, EmailTemplate $template)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'subject'      => 'required|string|max:255',
            'preheader'    => 'nullable|string|max:255',
            'body_html'    => 'required|string',
            'body_text'    => 'nullable|string',
            'category'     => 'required|in:transactional,marketing',
        ]);
        $template->update($data);
        return redirect()->route('admin.email.templates.index')
            ->with('success', 'Template updated.');
    }

    public function destroy(EmailTemplate $template)
    {
        $template->delete();
        return back()->with('success', 'Template deleted.');
    }

    public function preview(EmailTemplate $template)
    {
        return response($template->body_html)->header('Content-Type', 'text/html');
    }
}
