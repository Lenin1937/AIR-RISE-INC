<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Mail\AccountApprovedMail;
use App\Mail\AccountRejectedMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class ClientApprovalController extends Controller
{
    /**
     * List all clients pending approval.
     */
    public function index(): Response
    {
        $pending = User::role('client')
            ->where('registration_status', 'pending_approval')
            ->with(['orders' => fn ($q) => $q->latest()->limit(1)])
            ->latest()
            ->paginate(20);

        $approved = User::role('client')
            ->where('registration_status', 'approved')
            ->latest()
            ->paginate(10);

        $rejected = User::role('client')
            ->where('registration_status', 'rejected')
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/Clients/Approvals', [
            'pending'   => $pending,
            'approved'  => $approved,
            'rejected'  => $rejected,
        ]);
    }

    /**
     * Approve a client.
     */
    public function approve(Request $request, User $user): RedirectResponse
    {
        $user->update([
            'registration_status' => 'approved',
            'approved_at'         => now(),
            'approved_by'         => Auth::id(),
            'rejection_reason'    => null,
        ]);

        Mail::to($user->email)->queue(new AccountApprovedMail($user));

        return back()->with('success', "Account for {$user->full_name} has been approved.");
    }

    /**
     * Reject a client.
     */
    public function reject(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $user->update([
            'registration_status' => 'rejected',
            'rejection_reason'    => $request->reason,
            'approved_at'         => null,
            'approved_by'         => null,
        ]);

        Mail::to($user->email)->queue(new AccountRejectedMail($user, $request->reason));

        return back()->with('success', "Account for {$user->full_name} has been rejected.");
    }

    /**
     * Show a single client's profile for review.
     */
    public function show(User $user): Response
    {
        $user->load(['orders' => fn ($q) => $q->latest()]);

        return Inertia::render('Admin/Clients/ReviewProfile', [
            'client' => $user->append('full_name')->only([
                'id', 'first_name', 'last_name', 'full_name', 'email', 'username',
                'phone', 'telegram_username', 'company_name',
                'address_line_1', 'address_line_2', 'city', 'state', 'zip_code', 'country',
                'registration_status', 'rejection_reason', 'approved_at', 'created_at',
                'orders',
            ]),
        ]);
    }
}
