<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiChat;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminChatController extends Controller
{
    /**
     * Display a listing of AI chats
     */
    public function index(Request $request)
    {
        $query = AiChat::with(['user', 'messages'])
            ->withCount('messages')
            ->orderBy('created_at', 'desc');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('page_name', 'like', "%{$search}%")
                ->orWhere('page_url', 'like', "%{$search}%")
                ->orWhere('session_id', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status') && $request->input('status') !== 'all') {
            if ($request->input('status') === 'lead') {
                $query->where('is_lead', true);
            } else {
                $query->where('status', $request->input('status'));
            }
        }

        $chats = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Chats/Index', [
            'chats' => $chats,
            'filters' => [
                'search' => $request->input('search'),
                'status' => $request->input('status', 'all'),
            ],
        ]);
    }

    /**
     * Display the specified chat
     */
    public function show($id)
    {
        $chat = AiChat::with(['user', 'messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }])->findOrFail($id);

        return Inertia::render('Admin/Chats/Show', [
            'chat' => $chat,
        ]);
    }

    /**
     * Update chat status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,closed,archived',
        ]);

        $chat = AiChat::findOrFail($id);
        $chat->update(['status' => $request->input('status')]);

        return back()->with('success', 'Chat status updated successfully.');
    }

    /**
     * Mark chat as lead
     */
    public function markAsLead(Request $request, $id)
    {
        $chat = AiChat::findOrFail($id);
        $chat->update([
            'is_lead' => true,
            'lead_email' => $request->input('email'),
            'lead_data' => $request->input('data', []),
        ]);

        return back()->with('success', 'Chat marked as lead successfully.');
    }

    /**
     * Delete chat
     */
    public function destroy($id)
    {
        $chat = AiChat::findOrFail($id);
        $chat->delete();

        return redirect()->route('admin.chats.index')
            ->with('success', 'Chat deleted successfully.');
    }

    /**
     * Get analytics data
     */
    public function analytics()
    {
        $stats = [
            'total_chats' => AiChat::count(),
            'active_chats' => AiChat::where('status', 'active')->count(),
            'leads' => AiChat::where('is_lead', true)->count(),
            'total_messages' => \App\Models\AiMessage::count(),
            'chats_today' => AiChat::whereDate('created_at', today())->count(),
            'chats_this_week' => AiChat::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'chats_this_month' => AiChat::whereMonth('created_at', now()->month)->count(),
        ];

        // Top pages
        $topPages = AiChat::select('page_name')
            ->selectRaw('count(*) as chat_count')
            ->selectRaw('MAX(page_url) as page_type')
            ->groupBy('page_name')
            ->orderBy('chat_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($page) {
                // Extract page type from URL
                $url = $page->page_type;
                if (str_contains($url, '/services/c-corporation')) {
                    $page->page_type = 'service_c_corp';
                } elseif (str_contains($url, '/services/s-corporation')) {
                    $page->page_type = 'service_s_corp';
                } elseif (str_contains($url, '/services/llc')) {
                    $page->page_type = 'service_llc';
                } elseif (str_contains($url, '/services/nonprofit')) {
                    $page->page_type = 'service_nonprofit';
                } elseif (str_contains($url, '/services/green-card')) {
                    $page->page_type = 'service_greencard';
                } elseif (str_contains($url, '/pricing')) {
                    $page->page_type = 'pricing';
                } elseif (str_contains($url, '/dashboard')) {
                    $page->page_type = 'dashboard';
                } else {
                    $page->page_type = 'home';
                }
                return $page;
            });

        // Recent conversations
        $recentChats = AiChat::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Admin/Chats/Analytics', [
            'stats' => $stats,
            'topPages' => $topPages,
            'recentChats' => $recentChats,
        ]);
    }
}
