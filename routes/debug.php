<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

// Debug routes — only available in local/debug mode AND require admin auth
Route::middleware(['auth', 'role:super-admin,administrator'])->group(function () {

    Route::get('/debug-locale', function () {
        return response()->json([
            'app_locale' => App::getLocale(),
            'session_locale' => Session::get('locale'),
            'translations_exist' => file_exists(lang_path(App::getLocale() . '/common.php')),
            'translations_path' => lang_path(App::getLocale()),
            'common_trans' => __('common.home'),
        ]);
    });

    Route::get('/debug-auth', function () {
        $user = auth()->user();
        return response()->json([
            'authenticated' => auth()->check(),
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'user_name' => $user?->name,
            'roles' => $user?->roles->pluck('name'),
            'all_permissions' => $user?->getAllPermissions()->pluck('name'),
            'has_admin_role' => $user?->hasRole('administrator'),
            'has_any_admin_role' => $user?->hasAnyRole(['super-admin', 'administrator', 'staff']),
        ]);
    });

});
