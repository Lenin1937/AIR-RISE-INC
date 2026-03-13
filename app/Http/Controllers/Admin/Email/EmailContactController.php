<?php

namespace App\Http\Controllers\Admin\Email;

use App\Http\Controllers\Controller;
use App\Models\EmailContact;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailContactController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailContact::query();

        if ($search = $request->get('search')) {
            $query->where(fn($q) => $q
                ->where('email', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
            );
        }
        if ($type = $request->get('client_type')) $query->where('client_type', $type);
        if ($service = $request->get('service_type')) $query->where('service_type', $service);
        if ($sub = $request->get('subscribed')) {
            $sub === '1' ? $query->subscribed() : $query->whereNotNull('unsubscribed_at');
        }

        $contacts = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'total'        => EmailContact::count(),
            'subscribed'   => EmailContact::subscribed()->count(),
            'unsubscribed' => EmailContact::whereNotNull('unsubscribed_at')->count(),
            'prospects'    => EmailContact::where('client_type', 'prospect')->count(),
        ];

        return Inertia::render('Admin/Email/Contacts/Index', [
            'contacts' => $contacts,
            'stats'    => $stats,
            'filters'  => $request->only('search', 'client_type', 'service_type', 'subscribed'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email'                    => 'required|email|unique:email_contacts,email',
            'first_name'               => 'nullable|string|max:100',
            'last_name'                => 'nullable|string|max:100',
            'country'                  => 'nullable|string|max:2',
            'state'                    => 'nullable|string|max:50',
            'language'                 => 'nullable|string|max:5',
            'client_type'              => 'nullable|in:prospect,active,past,internal',
            'service_type'             => 'nullable|string',
            'source'                   => 'nullable|in:website_form,manual,import,order,referral',
            'tags'                     => 'nullable|array',
            'subscribed_marketing'     => 'boolean',
            'subscribed_transactional' => 'boolean',
        ]);

        EmailContact::create($data);
        return back()->with('success', 'Contact created.');
    }

    public function update(Request $request, EmailContact $contact)
    {
        $data = $request->validate([
            'first_name'               => 'nullable|string|max:100',
            'last_name'                => 'nullable|string|max:100',
            'country'                  => 'nullable|string|max:2',
            'state'                    => 'nullable|string|max:50',
            'language'                 => 'nullable|string|max:5',
            'client_type'              => 'nullable|in:prospect,active,past,internal',
            'service_type'             => 'nullable|string',
            'tags'                     => 'nullable|array',
            'subscribed_marketing'     => 'boolean',
            'subscribed_transactional' => 'boolean',
        ]);
        $contact->update($data);
        return back()->with('success', 'Contact updated.');
    }

    public function destroy(EmailContact $contact)
    {
        $contact->delete();
        return back()->with('success', 'Contact deleted.');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:5120']);
        $file   = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle);
        $header = array_map('strtolower', array_map('trim', $header));

        $created = 0;
        $skipped = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);
            $email = $data['email'] ?? null;
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $skipped++; continue; }
            if (EmailContact::where('email', $email)->exists()) { $skipped++; continue; }
            EmailContact::create([
                'email'      => $email,
                'first_name' => $data['first_name'] ?? $data['firstname'] ?? null,
                'last_name'  => $data['last_name']  ?? $data['lastname']  ?? null,
                'country'    => $data['country'] ?? null,
                'source'     => 'import',
            ]);
            $created++;
        }
        fclose($handle);
        return back()->with('success', "{$created} contacts imported, {$skipped} skipped.");
    }
}
