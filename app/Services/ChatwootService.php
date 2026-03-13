<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatwootService
{
    protected string $baseUrl;
    protected string $apiToken;
    protected int $accountId;
    protected int $inboxId;

    public function __construct()
    {
        $this->baseUrl = config('services.chatwoot.url');
        $this->apiToken = config('services.chatwoot.api_token');
        $this->accountId = config('services.chatwoot.account_id');
        $this->inboxId = config('services.chatwoot.inbox_id');
    }

    /**
     * Create or update contact in Chatwoot when user registers
     */
    public function createContact(User $user): ?array
    {
        try {
            $response = Http::withHeaders([
                'api_access_token' => $this->apiToken,
            ])->post("{$this->baseUrl}/api/v1/accounts/{$this->accountId}/contacts", [
                'inbox_id' => $this->inboxId,
                'name' => $user->full_name,
                'email' => $user->email,
                'phone_number' => $user->phone,
                'identifier' => (string) $user->id, // Link to your user ID
                'custom_attributes' => [
                    'user_id' => $user->id,
                    'company' => $user->company_name,
                    'subscription_status' => $user->hasActiveSubscription() ? 'active' : 'inactive',
                    'signup_date' => $user->created_at->toDateString(),
                ],
            ]);

            if ($response->successful()) {
                $contact = $response->json();
                
                // Optionally store Chatwoot contact ID in user model
                $user->update(['chatwoot_contact_id' => $contact['payload']['contact']['id']]);
                
                return $contact;
            }

            Log::error('Chatwoot contact creation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Chatwoot API error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send message from admin to customer
     */
    public function sendMessage(int $conversationId, string $message, string $messageType = 'outgoing'): ?array
    {
        try {
            $response = Http::withHeaders([
                'api_access_token' => $this->apiToken,
            ])->post("{$this->baseUrl}/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}/messages", [
                'content' => $message,
                'message_type' => $messageType, // 'outgoing' for admin, 'incoming' for customer
                'private' => false, // Set true for internal notes
            ]);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('Failed to send Chatwoot message: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get conversation details
     */
    public function getConversation(int $conversationId): ?array
    {
        try {
            $response = Http::withHeaders([
                'api_access_token' => $this->apiToken,
            ])->get("{$this->baseUrl}/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}");

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('Failed to get Chatwoot conversation: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all conversations for an account
     */
    public function getConversations(string $status = 'open'): ?array
    {
        try {
            $response = Http::withHeaders([
                'api_access_token' => $this->apiToken,
            ])->get("{$this->baseUrl}/api/v1/accounts/{$this->accountId}/conversations", [
                'status' => $status, // open, resolved, pending
            ]);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('Failed to get Chatwoot conversations: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Assign conversation to an agent
     */
    public function assignConversation(int $conversationId, int $agentId): bool
    {
        try {
            $response = Http::withHeaders([
                'api_access_token' => $this->apiToken,
            ])->post("{$this->baseUrl}/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}/assignments", [
                'assignee_id' => $agentId,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to assign Chatwoot conversation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update conversation status
     */
    public function updateConversationStatus(int $conversationId, string $status): bool
    {
        try {
            $response = Http::withHeaders([
                'api_access_token' => $this->apiToken,
            ])->post("{$this->baseUrl}/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}/toggle_status", [
                'status' => $status, // open, resolved, pending
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to update Chatwoot conversation status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Add label to conversation
     */
    public function addLabel(int $conversationId, string $label): bool
    {
        try {
            $response = Http::withHeaders([
                'api_access_token' => $this->apiToken,
            ])->post("{$this->baseUrl}/api/v1/accounts/{$this->accountId}/conversations/{$conversationId}/labels", [
                'labels' => [$label],
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to add Chatwoot label: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get contact by identifier (user ID)
     */
    public function getContactByIdentifier(string $identifier): ?array
    {
        try {
            $response = Http::withHeaders([
                'api_access_token' => $this->apiToken,
            ])->get("{$this->baseUrl}/api/v1/accounts/{$this->accountId}/contacts/search", [
                'q' => $identifier,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['payload'][0] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to search Chatwoot contact: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send proactive message to user
     */
    public function sendProactiveMessage(User $user, string $message): ?array
    {
        try {
            // First, ensure contact exists
            $contact = $this->getContactByIdentifier((string) $user->id);
            
            if (!$contact) {
                $contact = $this->createContact($user);
            }

            if (!$contact) {
                return null;
            }

            // Create conversation
            $response = Http::withHeaders([
                'api_access_token' => $this->apiToken,
            ])->post("{$this->baseUrl}/api/v1/accounts/{$this->accountId}/conversations", [
                'source_id' => $contact['payload']['contact']['id'] ?? $contact['id'],
                'inbox_id' => $this->inboxId,
                'contact_id' => $contact['payload']['contact']['id'] ?? $contact['id'],
            ]);

            if ($response->successful()) {
                $conversation = $response->json();
                $conversationId = $conversation['id'];

                // Send message
                return $this->sendMessage($conversationId, $message, 'outgoing');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to send proactive Chatwoot message: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate HMAC for identity verification (secure widget)
     */
    public function generateHMAC(string $identifier): string
    {
        $identitySecret = config('services.chatwoot.identity_secret');
        return hash_hmac('sha256', $identifier, $identitySecret);
    }
}
