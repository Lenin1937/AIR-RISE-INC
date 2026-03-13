<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Upload and update the user's profile picture.
     */
    public function uploadProfilePicture(Request $request): RedirectResponse
    {
        $request->validate([
            'profile_picture' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $user = $request->user();
        
        // Delete old profile picture if exists
        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        // Store the uploaded file
        $file = $request->file('profile_picture');
        $filename = 'profile_pictures/' . uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        
        // Resize and optimize image
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file);
        $image->cover(300, 300);
        
        // Save optimized image
        Storage::disk('public')->put($filename, $image->encode());
        
        // Update user profile picture
        $user->update(['profile_picture' => $filename]);
        
        // Refresh the user instance to ensure the updated data is available
        $user->refresh();

        return back()->with('success', 'Profile picture updated successfully!');
    }

    /**
     * Remove the user's profile picture.
     */
    public function removeProfilePicture(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        // Delete profile picture file if exists
        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }
        
        // Clear profile picture from database
        $user->update(['profile_picture' => null]);
        
        // Refresh the user instance
        $user->refresh();

        return back()->with('success', 'Profile picture removed successfully!');
    }
}
