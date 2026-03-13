<?php

namespace App\Http\Controllers\Admin\Email;

use App\Http\Controllers\Controller;
use App\Models\EmailSegment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailSegmentController extends Controller
{
    public function index()
    {
        $segments = EmailSegment::latest()->get()->map(fn($s) => [
            'id'            => $s->id,
            'name'          => $s->name,
            'description'   => $s->description,
            'contact_count' => $s->contact_count,
            'auto_update'   => $s->auto_update,
            'conditions'    => $s->conditions,
            'created_at'    => $s->created_at->format('M j, Y'),
        ]);

        return Inertia::render('Admin/Email/Segments/Index', [
            'segments' => $segments,
            'stats'    => [
                'total'        => EmailSegment::count(),
                'auto_update'  => EmailSegment::where('auto_update', true)->count(),
                'total_contacts'=> (int) EmailSegment::sum('contact_count'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'conditions'  => 'required|array|min:1',
            'auto_update' => 'boolean',
        ]);

        $segment = EmailSegment::create($data);
        $segment->refreshCount();

        return back()->with('success', 'Segment created with ' . $segment->contact_count . ' contacts.');
    }

    public function update(Request $request, EmailSegment $segment)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'conditions'  => 'required|array|min:1',
            'auto_update' => 'boolean',
        ]);
        $segment->update($data);
        $segment->refreshCount();
        return back()->with('success', 'Segment updated.');
    }

    public function destroy(EmailSegment $segment)
    {
        $segment->delete();
        return back()->with('success', 'Segment deleted.');
    }

    public function preview(Request $request)
    {
        $conditions = $request->validate(['conditions' => 'required|array'])['conditions'];
        $segment = new EmailSegment(['conditions' => $conditions]);
        $count = $segment->resolveContacts()->count();
        return response()->json(['count' => $count]);
    }
}
