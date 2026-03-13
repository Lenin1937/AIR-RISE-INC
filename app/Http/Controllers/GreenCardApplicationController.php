<?php

namespace App\Http\Controllers;

use App\Models\GreenCardApplication;
use App\Models\GreenCardDocument;
use App\Models\GreenCardFamilyMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class GreenCardApplicationController extends Controller
{
    /**
     * Show the apply wizard page.
     */
    public function apply(Request $request): Response
    {
        $token = $request->query('token');
        $application = null;

        if ($token) {
            $application = GreenCardApplication::where('session_token', $token)
                ->with(['familyMembers', 'documents'])
                ->first();
        }

        return Inertia::render('Marketing/Services/GreenCardApply', [
            'application' => $application,
        ]);
    }

    /**
     * Step 1 — Start a new application (package selection).
     */
    public function start(Request $request): JsonResponse
    {
        $request->validate([
            'package_type' => 'required|in:basic,family,premium',
        ]);

        $token = Str::uuid()->toString();
        $price = GreenCardApplication::packagePrice($request->package_type);

        $application = GreenCardApplication::create([
            'session_token' => $token,
            'user_id'       => auth()->id(),
            'email'         => auth()->user()?->email ?? '',
            'package_type'  => $request->package_type,
            'package_price' => $price,
            'status'        => 'draft',
            'current_step'  => 2,
        ]);

        return response()->json([
            'token'        => $token,
            'application'  => $application,
            'redirect_url' => route('green-card.apply', ['token' => $token]),
        ]);
    }

    /**
     * Step 2 — Save email / account confirmation.
     */
    public function saveEmail(Request $request): JsonResponse
    {
        $request->validate([
            'token'               => 'required|string|exists:green_card_applications,session_token',
            'email'               => 'required|email',
            'confirmed_not_govt'  => 'required|accepted',
            'confirmed_tos'       => 'required|accepted',
        ]);

        $app = GreenCardApplication::where('session_token', $request->token)->firstOrFail();
        $app->update([
            'email'              => $request->email,
            'confirmed_not_govt' => true,
            'confirmed_tos'      => true,
            'current_step'       => 3,
        ]);

        return response()->json(['success' => true, 'application' => $app]);
    }

    /**
     * Step 3 — Save primary applicant information.
     */
    public function saveApplicant(Request $request): JsonResponse
    {
        $request->validate([
            'token'                  => 'required|string|exists:green_card_applications,session_token',
            'first_name'             => 'required|string|max:100',
            'middle_name'            => 'nullable|string|max:100',
            'last_name'              => 'required|string|max:100',
            'gender'                 => 'required|in:male,female',
            'date_of_birth'          => 'required|date|before:today',
            'city_of_birth'          => 'required|string|max:100',
            'country_of_birth'       => 'required|string|max:100',
            'country_of_eligibility' => 'required|string|max:100',
            'passport_number'        => 'required|string|max:50',
            'passport_country'       => 'required|string|max:100',
            'passport_expiry'        => 'required|date|after:today',
            'address_line_1'         => 'required|string|max:255',
            'address_line_2'         => 'nullable|string|max:255',
            'city'                   => 'required|string|max:100',
            'state_province'         => 'nullable|string|max:100',
            'postal_code'            => 'nullable|string|max:20',
            'country'                => 'required|string|max:100',
            'phone'                  => 'nullable|string|max:30',
            'education_level'        => 'required|string',
        ]);

        $app = GreenCardApplication::where('session_token', $request->token)->firstOrFail();
        $app->update([
            'first_name'             => $request->first_name,
            'middle_name'            => $request->middle_name,
            'last_name'              => $request->last_name,
            'gender'                 => $request->gender,
            'date_of_birth'          => $request->date_of_birth,
            'city_of_birth'          => $request->city_of_birth,
            'country_of_birth'       => $request->country_of_birth,
            'country_of_eligibility' => $request->country_of_eligibility,
            'passport_number'        => $request->passport_number,
            'passport_country'       => $request->passport_country,
            'passport_expiry'        => $request->passport_expiry,
            'address_line_1'         => $request->address_line_1,
            'address_line_2'         => $request->address_line_2,
            'city'                   => $request->city,
            'state_province'         => $request->state_province,
            'postal_code'            => $request->postal_code,
            'country'                => $request->country,
            'phone'                  => $request->phone,
            'education_level'        => $request->education_level,
            'current_step'           => 4,
        ]);

        return response()->json(['success' => true, 'application' => $app]);
    }

    /**
     * Step 4 — Save family members.
     */
    public function saveFamily(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => 'required|string|exists:green_card_applications,session_token',
            'members'  => 'nullable|array',
            'members.*.type'           => 'required|in:spouse,child',
            'members.*.first_name'     => 'required|string|max:100',
            'members.*.last_name'      => 'required|string|max:100',
            'members.*.date_of_birth'  => 'nullable|date',
            'members.*.country_of_birth' => 'nullable|string|max:100',
            'members.*.gender'         => 'nullable|in:male,female',
        ]);

        $app = GreenCardApplication::where('session_token', $request->token)->firstOrFail();

        // Delete old family members and re-insert
        $app->familyMembers()->delete();

        $hasSpouse   = false;
        $hasChildren = false;

        foreach (($request->members ?? []) as $member) {
            $app->familyMembers()->create($member);
            if ($member['type'] === 'spouse') {
                $hasSpouse = true;
            } else {
                $hasChildren = true;
            }
        }

        $app->update([
            'has_spouse'   => $hasSpouse,
            'has_children' => $hasChildren,
            'current_step' => 5,
        ]);

        return response()->json(['success' => true, 'application' => $app->load('familyMembers')]);
    }

    /**
     * Step 5 — Upload photo.
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'token'  => 'required|string|exists:green_card_applications,session_token',
            'photo'  => 'required|image|mimes:jpeg,jpg,png|max:5120',
            'target' => 'required|string', // primary, spouse, child_{id}
        ]);

        $app = GreenCardApplication::where('session_token', $request->token)->firstOrFail();

        $path = $request->file('photo')->store(
            'green-card/photos/' . $app->id,
            'private'
        );

        if ($request->target === 'primary') {
            $app->update(['primary_photo_path' => $path, 'current_step' => 6]);
        } elseif (Str::startsWith($request->target, 'child_') || $request->target === 'spouse') {
            // find the family member
            $type = $request->target === 'spouse' ? 'spouse' : null;
            $memberId = Str::startsWith($request->target, 'child_')
                ? (int) Str::after($request->target, 'child_')
                : null;

            if ($memberId) {
                GreenCardFamilyMember::where('id', $memberId)
                    ->where('application_id', $app->id)
                    ->update(['photo_path' => $path]);
            } elseif ($type === 'spouse') {
                $app->familyMembers()->where('type', 'spouse')->first()?->update(['photo_path' => $path]);
            }
        }

        return response()->json(['success' => true, 'path' => $path]);
    }

    /**
     * Step 6 — Upload supporting documents.
     */
    public function uploadDocument(Request $request): JsonResponse
    {
        $request->validate([
            'token'         => 'required|string|exists:green_card_applications,session_token',
            'document'      => 'required|file|mimes:pdf,jpeg,jpg,png|max:10240',
            'document_type' => 'required|string',
            'label'         => 'required|string|max:200',
        ]);

        $app = GreenCardApplication::where('session_token', $request->token)->firstOrFail();

        $file = $request->file('document');
        $path = $file->store('green-card/docs/' . $app->id, 'private');

        $doc = GreenCardDocument::create([
            'application_id' => $app->id,
            'document_type'  => $request->document_type,
            'label'          => $request->label,
            'file_path'      => $path,
            'original_name'  => $file->getClientOriginalName(),
            'file_size'      => $file->getSize(),
            'mime_type'      => $file->getMimeType(),
        ]);

        $app->update(['current_step' => max($app->current_step, 7)]);

        return response()->json(['success' => true, 'document' => $doc]);
    }

    /**
     * Step 7 — Submit review confirmations and proceed to payment.
     */
    public function submitReview(Request $request): JsonResponse
    {
        $request->validate([
            'token'                    => 'required|string|exists:green_card_applications,session_token',
            'confirmed_accuracy'       => 'required|accepted',
            'confirmed_single_entry'   => 'required|accepted',
        ]);

        $app = GreenCardApplication::where('session_token', $request->token)->firstOrFail();
        $app->update([
            'confirmed_accuracy'     => true,
            'confirmed_single_entry' => true,
            'status'                 => 'payment_pending',
            'current_step'           => 8,
        ]);

        return response()->json(['success' => true, 'application' => $app]);
    }

    /**
     * Step 8 — Create Stripe Payment Intent.
     */
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string|exists:green_card_applications,session_token',
        ]);

        $app = GreenCardApplication::where('session_token', $request->token)->firstOrFail();

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            $intent = $stripe->paymentIntents->create([
                'amount'   => (int) ($app->package_price * 100),
                'currency' => 'usd',
                'metadata' => [
                    'application_token' => $app->session_token,
                    'package_type'      => $app->package_type,
                    'email'             => $app->email,
                ],
                'receipt_email' => $app->email,
            ]);

            $app->update(['payment_intent_id' => $intent->id]);

            return response()->json([
                'client_secret' => $intent->client_secret,
                'amount'        => $app->package_price,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 8 — Confirm payment and finalize application.
     */
    public function confirmPayment(Request $request): JsonResponse
    {
        $request->validate([
            'token'          => 'required|string|exists:green_card_applications,session_token',
            'payment_method' => 'required|string',
        ]);

        $app = GreenCardApplication::where('session_token', $request->token)->firstOrFail();

        $app->update([
            'paid'           => true,
            'paid_at'        => now(),
            'payment_method' => $request->payment_method,
            'status'         => 'in_review',
            'current_step'   => 9,
            'submitted_at'   => now(),
        ]);

        return response()->json([
            'success'     => true,
            'redirect_url' => route('green-card.confirmation', ['token' => $app->session_token]),
        ]);
    }

    /**
     * Step 9 — Confirmation page.
     */
    public function confirmation(Request $request): Response
    {
        $token = $request->query('token');
        $app = GreenCardApplication::where('session_token', $token)
            ->with(['familyMembers', 'documents'])
            ->firstOrFail();

        return Inertia::render('Marketing/Services/GreenCardConfirmation', [
            'application' => $app,
        ]);
    }

    /**
     * Get application data by token (for resume later).
     */
    public function getApplication(Request $request): JsonResponse
    {
        $app = GreenCardApplication::where('session_token', $request->token)
            ->with(['familyMembers', 'documents'])
            ->firstOrFail();

        return response()->json($app);
    }
}
