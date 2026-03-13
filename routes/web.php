<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\Email\EmailDashboardController;
use App\Http\Controllers\Admin\Email\EmailContactController;
use App\Http\Controllers\Admin\Email\EmailTemplateController;
use App\Http\Controllers\Admin\Email\EmailCampaignController;
use App\Http\Controllers\Admin\Email\EmailAutomationController;
use App\Http\Controllers\Admin\Email\EmailSegmentController;
use App\Http\Controllers\Admin\Email\AiAssistController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\BlogPostController as AdminBlogPostController;
use App\Http\Controllers\Marketing\HomeController;
use App\Http\Controllers\Marketing\ServicesController;
use App\Http\Controllers\Marketing\PricingController;
use App\Http\Controllers\Marketing\BlogController;
use App\Http\Controllers\Marketing\KnowledgeBaseController as MarketingKnowledgeBaseController;
use App\Http\Controllers\Billing\SubscriptionController;
use App\Http\Controllers\Billing\PaymentController;
use App\Http\Controllers\Billing\InvoiceController;
use App\Http\Controllers\Billing\WebhookController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Onboarding\OnboardingController;
use App\Http\Controllers\Admin\ClientApprovalController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Sitemap & SEO
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemap-news.xml', [SitemapController::class, 'news'])->name('sitemap.news');

// CSRF token refresh endpoint (used by registration OTP flow)
Route::get('/csrf-token', function () {
    request()->session()->regenerateToken();

    return response()->json([
        'token' => csrf_token(),
    ]);
})->name('csrf.token');

// Public Document Sharing (no auth required)
Route::get('/shared/{token}', [DocumentController::class, 'accessShared'])->name('documents.shared');
Route::get('/shared/{token}/download', [DocumentController::class, 'downloadShared'])->name('documents.download-shared');

// Language Switcher
Route::post('/language/switch', [LanguageController::class, 'switch'])->name('language.switch');

// Debug locale (temporary)
if (config('app.debug')) {
    require __DIR__.'/debug.php';
}

// AI Chat Routes (accessible to everyone - guests and authenticated users)
Route::prefix('api/chat')->middleware(['throttle:60,1'])->group(function () {
    Route::post('/start', [ChatController::class, 'startSession']);
    Route::post('/stream', [ChatController::class, 'stream'])->middleware('throttle:20,1'); // stricter limit on AI stream
    Route::get('/history/{sessionId}', [ChatController::class, 'history']);
    Route::post('/close/{sessionId}', [ChatController::class, 'close']);
    Route::post('/lead/{sessionId}', [ChatController::class, 'markAsLead']);
});

// Marketing Routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Service Pages
Route::prefix('services')->name('services.')->group(function () {
    Route::get('/c-corporation', [ServicesController::class, 'cCorp'])->name('c-corp');
    Route::get('/s-corporation', [ServicesController::class, 'sCorp'])->name('s-corp');
    Route::get('/llc', [ServicesController::class, 'llc'])->name('llc');
    Route::get('/nonprofit', [ServicesController::class, 'nonprofit'])->name('nonprofit');
    Route::get('/green-card-lottery', [ServicesController::class, 'greenCard'])->name('green-card');
    Route::get('/income-tax-filing-planning', [ServicesController::class, 'incomeTax'])->name('income-tax');
});

// Green Card Application Wizard
use App\Http\Controllers\GreenCardApplicationController;

Route::prefix('services/green-card-lottery')->name('green-card.')->group(function () {
    Route::get('/apply', [GreenCardApplicationController::class, 'apply'])->name('apply');
    Route::get('/confirmation', [GreenCardApplicationController::class, 'confirmation'])->name('confirmation');

    // API steps
    Route::post('/start',               [GreenCardApplicationController::class, 'start'])->name('start');
    Route::post('/save-email',          [GreenCardApplicationController::class, 'saveEmail'])->name('save-email');
    Route::post('/save-applicant',      [GreenCardApplicationController::class, 'saveApplicant'])->name('save-applicant');
    Route::post('/save-family',         [GreenCardApplicationController::class, 'saveFamily'])->name('save-family');
    Route::post('/upload-photo',        [GreenCardApplicationController::class, 'uploadPhoto'])->name('upload-photo');
    Route::post('/upload-document',     [GreenCardApplicationController::class, 'uploadDocument'])->name('upload-document');
    Route::post('/submit-review',       [GreenCardApplicationController::class, 'submitReview'])->name('submit-review');
    Route::post('/create-payment-intent', [GreenCardApplicationController::class, 'createPaymentIntent'])->name('payment-intent');
    Route::post('/confirm-payment',     [GreenCardApplicationController::class, 'confirmPayment'])->name('confirm-payment');
    Route::get('/get',                  [GreenCardApplicationController::class, 'getApplication'])->name('get');
});

// Other Marketing Pages
Route::get('/pricing', [PricingController::class, 'index'])->name('marketing.pricing');
Route::get('/about', function () {
    return Inertia::render('Marketing/About');
})->name('marketing.about');
Route::get('/contact', function () {
    return Inertia::render('Marketing/Contact');
})->name('marketing.contact');

// Blog
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/feed', [BlogController::class, 'feed'])->name('feed');
    Route::get('/{slug}', [BlogController::class, 'show'])->name('show');
});

// Marketing Knowledge Base (public pages)
Route::prefix('knowledge-base')->name('marketing.knowledge-base.')->group(function () {
    Route::get('/', [MarketingKnowledgeBaseController::class, 'index'])->name('index');
    Route::get('/{slug}', [MarketingKnowledgeBaseController::class, 'show'])->name('show');
});

// Legal Pages
Route::get('/privacy-policy', function () {
    return Inertia::render('Marketing/Legal/PrivacyPolicy');
})->name('legal.privacy-policy');

Route::get('/terms-of-service', function () {
    return Inertia::render('Marketing/Legal/TermsOfService');
})->name('legal.terms-of-service');

Route::get('/cookie-policy', function () {
    return Inertia::render('Marketing/Legal/CookiePolicy');
})->name('legal.cookie-policy');

Route::get('/compliance', function () {
    return Inertia::render('Marketing/Legal/CompliancePolicy');
})->name('legal.compliance');

Route::get('/refund-policy', function () {
    return Inertia::render('Marketing/Legal/RefundPolicy');
})->name('legal.refund-policy');

Route::get('/legal-disclaimer', function () {
    return Inertia::render('Marketing/Legal/LegalDisclaimer');
})->name('legal.disclaimer');

Route::get('/security-policy', function () {
    return Inertia::render('Marketing/Legal/SecurityPolicy');
})->name('legal.security-policy');

Route::get('/incident-response-policy', function () {
    return Inertia::render('Marketing/Legal/IncidentResponsePolicy');
})->name('legal.incident-response-policy');

// Onboarding Flow (auth required, but not yet approved)
Route::middleware(['auth', 'role:client'])->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/personal-info',  [OnboardingController::class, 'personalInfo'])->name('personal-info');
    Route::post('/personal-info', [OnboardingController::class, 'savePersonalInfo'])->name('save-personal-info');
    Route::get('/order',          [OnboardingController::class, 'order'])->name('order');
    Route::post('/order',         [OnboardingController::class, 'saveOrder'])->name('save-order');
    Route::get('/review',         [OnboardingController::class, 'review'])->name('review');
});

// Client Dashboard and Features
Route::middleware(['auth', 'role:client', 'client.onboarding'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Knowledge Base (Client Dashboard)
    Route::prefix('dashboard/knowledge-base')->name('knowledge-base.')->group(function () {
        Route::get('/', [KnowledgeBaseController::class, 'index'])->name('index');
        Route::get('/search', [KnowledgeBaseController::class, 'search'])->name('search');
        Route::get('/popular', [KnowledgeBaseController::class, 'popular'])->name('popular');
        Route::get('/category/{category}', [KnowledgeBaseController::class, 'category'])->name('category');
        Route::get('/{slug}', [KnowledgeBaseController::class, 'show'])->name('show');
    });
    
    // Orders - Custom routes must come before resource routes
    Route::get('/orders/checkout', function() {
        return redirect()->route('orders.create');
    })->name('orders.checkout.get');
    Route::post('/orders/checkout', [OrderController::class, 'checkout'])->name('orders.checkout');
    Route::get('/orders/draft', [OrderController::class, 'getDraft'])->name('orders.draft.get');
    Route::post('/orders/draft', [OrderController::class, 'saveDraft'])->name('orders.draft.save');
    Route::delete('/orders/draft', [OrderController::class, 'deleteDraft'])->name('orders.draft.delete');
    Route::post('/orders/create-payment-intent', [OrderController::class, 'createPaymentIntent'])->name('orders.create-payment-intent');
    Route::post('/orders/process-payment', [OrderController::class, 'processPayment'])->name('orders.process-payment');
    Route::post('/orders/paypal-process', [OrderController::class, 'processPayPalPayment'])->name('orders.paypal-process');
    Route::post('/orders/create-crypto-payment', [OrderController::class, 'createCryptoPayment'])->name('orders.create-crypto-payment');
    Route::get('/orders/crypto/success', [OrderController::class, 'cryptoPaymentSuccess'])->name('orders.crypto-success');
    Route::get('/orders/stripe/success', [OrderController::class, 'stripeSuccess'])->name('orders.stripe.success');
    Route::get('/orders/stripe/cancel', [OrderController::class, 'stripeCancel'])->name('orders.stripe.cancel');
    Route::resource('orders', OrderController::class)->except(['edit', 'update', 'destroy']);
    
    // Documents
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('/documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
    
    // Document Sharing
    Route::post('/documents/{document}/share', [DocumentController::class, 'generateShareLink'])->name('documents.share');
    Route::delete('/documents/share/{token}', [DocumentController::class, 'revokeShareLink'])->name('documents.share.revoke');
    Route::get('/documents/{document}/shares', [DocumentController::class, 'shareLinks'])->name('documents.shares');
    
    // Messages
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    
    // Payment Methods
    Route::get('/payment-methods', [PaymentMethodController::class, 'index'])->name('payment-methods.index');
    Route::post('/payment-methods', [PaymentMethodController::class, 'store'])->name('payment-methods.store');
    Route::patch('/payment-methods/{paymentMethod}/set-default', [PaymentMethodController::class, 'setDefault'])->name('payment-methods.set-default');
    Route::delete('/payment-methods/{paymentMethod}', [PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super-admin,administrator,admin,staff'])->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Client Profile Approvals
    Route::get('client-approvals',              [ClientApprovalController::class, 'index'])->name('client-approvals.index');
    Route::get('client-approvals/{user}',       [ClientApprovalController::class, 'show'])->name('client-approvals.show');
    Route::post('client-approvals/{user}/approve', [ClientApprovalController::class, 'approve'])->name('client-approvals.approve');
    Route::post('client-approvals/{user}/reject',  [ClientApprovalController::class, 'reject'])->name('client-approvals.reject');

    // User Management
    Route::get('users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [Admin\UserController::class, 'create'])->name('users.create');
    Route::post('users', [Admin\UserController::class, 'store'])->name('users.store');
    Route::get('users/{user}', [Admin\UserController::class, 'show'])->name('users.show');
    Route::get('users/{user}/edit', [Admin\UserController::class, 'edit'])->name('users.edit');
    Route::patch('users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
    Route::post('users/bulk-action', [Admin\UserController::class, 'bulkAction'])->name('users.bulk-action');
    
    // Order Management
    Route::get('orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/edit', [Admin\OrderController::class, 'edit'])->name('orders.edit');
    Route::patch('orders/{order}', [Admin\OrderController::class, 'update'])->name('orders.update');
    Route::delete('orders/{order}', [Admin\OrderController::class, 'destroy'])->name('orders.destroy');
    Route::post('orders/{order}/approve', [Admin\OrderController::class, 'approve'])->name('orders.approve');
    Route::post('orders/{order}/reject', [Admin\OrderController::class, 'reject'])->name('orders.reject');
    Route::post('orders/{order}/request-documents', [Admin\OrderController::class, 'requestDocuments'])->name('orders.request-documents');
    
    // Payment Management
    Route::get('payments', [Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/{payment}', [Admin\PaymentController::class, 'show'])->name('payments.show');
    Route::patch('payments/{payment}', [Admin\PaymentController::class, 'update'])->name('payments.update');
    Route::post('payments/manual', [Admin\PaymentController::class, 'storeManual'])->name('payments.manual');
    
    // Document Management
    Route::get('documents', [Admin\DocumentController::class, 'index'])->name('documents.index');
    Route::post('documents', [Admin\DocumentController::class, 'store'])->name('documents.store');
    Route::get('documents/{document}', [Admin\DocumentController::class, 'show'])->name('documents.show');
    Route::get('documents/{document}/download', [Admin\DocumentController::class, 'download'])->name('documents.download');
    Route::get('documents/{document}/preview', [Admin\DocumentController::class, 'preview'])->name('documents.preview');
    Route::patch('documents/{document}/status', [Admin\DocumentController::class, 'updateStatus'])->name('documents.updateStatus');
    Route::delete('documents/{document}', [Admin\DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::post('documents/bulk-action', [Admin\DocumentController::class, 'bulkAction'])->name('documents.bulkAction');
    
    // Order Document Downloads (for documents stored in orders)
    Route::get('orders/{order}/documents/{documentType}/download', [Admin\OrderController::class, 'downloadDocument'])->name('orders.documents.download');
    
    // Message Management
    Route::get('messages', [Admin\MessageController::class, 'index'])->name('messages.index');
    Route::post('messages', [Admin\MessageController::class, 'store'])->name('messages.store');
    Route::get('messages/{message}', [Admin\MessageController::class, 'show'])->name('messages.show');
    Route::patch('messages/{message}', [Admin\MessageController::class, 'update'])->name('messages.update');
    
    // Live Chat Management (Chatwoot)
    Route::get('chat', [AdminChatController::class, 'index'])->name('chat.index');
    Route::get('chat/{conversation}', [AdminChatController::class, 'show'])->name('chat.show');
    Route::post('chat/{conversation}/send', [AdminChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('chat/{conversation}/assign', [AdminChatController::class, 'assign'])->name('chat.assign');
    Route::post('chat/{conversation}/toggle-status', [AdminChatController::class, 'toggleStatus'])->name('chat.toggleStatus');
    Route::get('chat-updates', [AdminChatController::class, 'pollUpdates'])->name('chat.updates');
    
    // Role Management
    Route::get('/roles', [Admin\RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [Admin\RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [Admin\RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}', [Admin\RoleController::class, 'show'])->name('roles.show');
    Route::get('/roles/{role}/edit', [Admin\RoleController::class, 'edit'])->name('roles.edit');
    Route::patch('/roles/{role}', [Admin\RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [Admin\RoleController::class, 'destroy'])->name('roles.destroy');
    
    // Article Management
    Route::resource('articles', Admin\ArticleController::class);
    Route::post('articles/bulk-action', [Admin\ArticleController::class, 'bulkAction'])->name('articles.bulk-action');
    Route::get('articles-stats', [Admin\ArticleController::class, 'getStats'])->name('articles.stats');

    // Blog Management
    Route::post('blog/generate-ai', [AdminBlogPostController::class, 'generateAI'])->name('blog.generate-ai');
    Route::resource('blog', AdminBlogPostController::class)->parameters(['blog' => 'blog']);
    Route::patch('blog/{blog}/toggle-publish', [AdminBlogPostController::class, 'togglePublish'])->name('blog.toggle-publish');
    
    // Admin Profile Management
    Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('password.update');
    Route::post('/profile/upload-avatar', [AdminProfileController::class, 'uploadProfilePicture'])->name('profile.upload-avatar');
    Route::delete('/profile/remove-avatar', [AdminProfileController::class, 'removeProfilePicture'])->name('profile.remove-avatar');
    
    // AI Chat Management
    Route::get('chats', [Admin\AdminChatController::class, 'index'])->name('chats.index');
    Route::get('chats/analytics', [Admin\AdminChatController::class, 'analytics'])->name('chats.analytics');
    Route::get('chats/{id}', [Admin\AdminChatController::class, 'show'])->name('chats.show');
    Route::patch('chats/{id}/status', [Admin\AdminChatController::class, 'updateStatus'])->name('chats.updateStatus');
    Route::post('chats/{id}/mark-lead', [Admin\AdminChatController::class, 'markAsLead'])->name('chats.markLead');
    Route::delete('chats/{id}', [Admin\AdminChatController::class, 'destroy'])->name('chats.destroy');

    // SEO & Analytics Dashboard
    Route::get('analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/data', [AdminAnalyticsController::class, 'data'])->name('analytics.data');

    // Email Marketing
    Route::prefix('email')->name('email.')->group(function () {
        Route::get('/', [EmailDashboardController::class, 'index'])->name('index');

        // Contacts
        Route::get('contacts', [EmailContactController::class, 'index'])->name('contacts.index');
        Route::post('contacts', [EmailContactController::class, 'store'])->name('contacts.store');
        Route::patch('contacts/{contact}', [EmailContactController::class, 'update'])->name('contacts.update');
        Route::delete('contacts/{contact}', [EmailContactController::class, 'destroy'])->name('contacts.destroy');
        Route::post('contacts/import', [EmailContactController::class, 'import'])->name('contacts.import');

        // Templates
        Route::get('templates', [EmailTemplateController::class, 'index'])->name('templates.index');
        Route::get('templates/create', [EmailTemplateController::class, 'create'])->name('templates.create');
        Route::post('templates', [EmailTemplateController::class, 'store'])->name('templates.store');
        Route::get('templates/{template}/edit', [EmailTemplateController::class, 'edit'])->name('templates.edit');
        Route::patch('templates/{template}', [EmailTemplateController::class, 'update'])->name('templates.update');
        Route::delete('templates/{template}', [EmailTemplateController::class, 'destroy'])->name('templates.destroy');
        Route::get('templates/{template}/preview', [EmailTemplateController::class, 'preview'])->name('templates.preview');

        // Campaigns
        Route::get('campaigns', [EmailCampaignController::class, 'index'])->name('campaigns.index');
        Route::get('campaigns/create', [EmailCampaignController::class, 'create'])->name('campaigns.create');
        Route::post('campaigns', [EmailCampaignController::class, 'store'])->name('campaigns.store');
        Route::get('campaigns/{campaign}', [EmailCampaignController::class, 'show'])->name('campaigns.show');
        Route::post('campaigns/{campaign}/send', [EmailCampaignController::class, 'send'])->name('campaigns.send');
        Route::delete('campaigns/{campaign}', [EmailCampaignController::class, 'destroy'])->name('campaigns.destroy');

        // Automations
        Route::get('automations', [EmailAutomationController::class, 'index'])->name('automations.index');
        Route::post('automations', [EmailAutomationController::class, 'store'])->name('automations.store');
        Route::patch('automations/{automation}', [EmailAutomationController::class, 'update'])->name('automations.update');
        Route::post('automations/{automation}/toggle', [EmailAutomationController::class, 'toggleStatus'])->name('automations.toggle');
        Route::delete('automations/{automation}', [EmailAutomationController::class, 'destroy'])->name('automations.destroy');

        // Segments
        Route::get('segments', [EmailSegmentController::class, 'index'])->name('segments.index');
        Route::post('segments', [EmailSegmentController::class, 'store'])->name('segments.store');
        Route::patch('segments/{segment}', [EmailSegmentController::class, 'update'])->name('segments.update');
        Route::delete('segments/{segment}', [EmailSegmentController::class, 'destroy'])->name('segments.destroy');
        Route::post('segments/preview', [EmailSegmentController::class, 'preview'])->name('segments.preview');

        // AI Assist
        Route::post('ai/generate', [AiAssistController::class, 'generate'])->name('ai.generate');
    });
});

// Profile Routes (available to all authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/upload-avatar', [ProfileController::class, 'uploadProfilePicture'])->name('profile.upload-avatar');
    Route::delete('/profile/remove-avatar', [ProfileController::class, 'removeProfilePicture'])->name('profile.remove-avatar');
});

// Billing Routes (available to all authenticated users)
Route::middleware('auth')->prefix('billing')->name('billing.')->group(function () {
    // Subscription Management
    Route::get('/plans', [SubscriptionController::class, 'index'])->name('plans');
    Route::get('/subscription', [SubscriptionController::class, 'show'])->name('subscription');
    
    // Stripe Subscription Routes
    Route::post('/subscription/stripe', [SubscriptionController::class, 'storeStripe'])->name('subscription.stripe.store');
    Route::post('/subscription/stripe/cancel', [SubscriptionController::class, 'cancelStripe'])->name('subscription.stripe.cancel');
    Route::post('/subscription/stripe/resume', [SubscriptionController::class, 'resumeStripe'])->name('subscription.stripe.resume');
    Route::post('/subscription/stripe/swap', [SubscriptionController::class, 'swapStripe'])->name('subscription.stripe.swap');
    Route::get('/subscription/portal', [SubscriptionController::class, 'portal'])->name('subscription.portal');
    
    // PayPal Subscription Routes
    Route::post('/subscription/paypal', [SubscriptionController::class, 'storePayPal'])->name('subscription.paypal.store');
    Route::post('/subscription/paypal/cancel', [SubscriptionController::class, 'cancelPayPal'])->name('subscription.paypal.cancel');
    
    // Payment Management
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments');
    Route::post('/payment/intent', [PaymentController::class, 'createIntent'])->name('payment.intent');
    Route::post('/payment/crypto-intent', [PaymentController::class, 'createCryptoIntent'])->name('payment.crypto-intent');
    Route::post('/payment/confirm', [PaymentController::class, 'confirmStripe'])->name('payment.confirm');
    Route::post('/payment/setup-intent', [PaymentController::class, 'createSetupIntent'])->name('payment.setup-intent');
    Route::post('/payment/save-method', [PaymentController::class, 'savePaymentMethod'])->name('payment.save-method');
    Route::get('/payment/methods', [PaymentController::class, 'getPaymentMethods'])->name('payment.methods');
    
    // PayPal Payment Routes
    Route::post('/paypal/create-order', [PaymentController::class, 'createPayPalOrder'])->name('paypal.create-order');
    Route::post('/paypal/capture-order', [PaymentController::class, 'capturePayPalOrder'])->name('paypal.capture-order');
    Route::get('/paypal/success', function () {
        return redirect()->route('billing.payments')->with('success', 'Payment successful!');
    })->name('paypal.success');
    Route::get('/paypal/cancel', function () {
        return redirect()->route('billing.payments')->with('error', 'Payment cancelled.');
    })->name('paypal.cancel');
    Route::get('/paypal/subscription/success', function () {
        return redirect()->route('billing.subscription', ['gateway' => 'paypal'])->with('success', 'Subscription created successfully!');
    })->name('paypal.subscription.success');
    Route::get('/paypal/subscription/cancel', function () {
        return redirect()->route('billing.plans')->with('error', 'Subscription creation cancelled.');
    })->name('paypal.subscription.cancel');
    
    // Invoice Management
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::get('/invoices/upcoming', [InvoiceController::class, 'upcoming'])->name('invoices.upcoming');
    Route::post('/invoices/{invoice}/retry', [InvoiceController::class, 'retry'])->name('invoices.retry');
});

// Webhook Routes (no auth required - verified by signature)
// Cashier's built-in webhook handler for Stripe
Route::post('/stripe/webhook', '\Laravel\Cashier\Http\Controllers\WebhookController@handleWebhook')->name('webhooks.stripe');
// Custom PayPal webhook handler
Route::post('/webhooks/paypal', [WebhookController::class, 'handlePayPal'])->name('webhooks.paypal');


require __DIR__.'/auth.php';
