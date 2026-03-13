<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Order;
use App\Models\Document;
use App\Models\Payment;
use App\Models\Message;
use App\Models\Notification;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sample users
        $client1 = User::where('email', 'client@example.com')->first();
        $client2 = User::where('email', 'sarah@innovatecorp.com')->first();
        $admin = User::where('email', 'admin@icorp.pro')->first();

        if (!$client1 || !$client2 || !$admin) {
            $this->command->error('Please run RolePermissionSeeder first!');
            return;
        }

        // Create sample orders
        $order1 = Order::create([
            'order_number' => 'ORD-2025-000001',
            'user_id' => $client1->id,
            'service_type' => 'llc',
            'state_of_incorporation' => 'DE',
            'entity_name' => 'Tech Innovations LLC',
            'business_purpose' => 'Software development and technology consulting services',
            'members' => json_encode([
                [
                    'name' => 'John Smith',
                    'address' => '123 Main St, New York, NY 10001',
                    'ownership_percentage' => 100,
                    'role' => 'Managing Member'
                ]
            ]),
            'management_structure' => 'member_managed',
            'status' => 'in_progress',
            'service_fee' => 299.00,
            'state_fee' => 90.00,
            'total_amount' => 389.00,
            'amount_paid' => 389.00,
            'processing_speed' => 'standard',
            'estimated_days' => 7,
            'expected_completion_date' => now()->addDays(7),
            'submitted_at' => now()->subDays(3),
        ]);

        $order2 = Order::create([
            'order_number' => 'ORD-2025-000002',
            'user_id' => $client2->id,
            'service_type' => 'c_corp',
            'state_of_incorporation' => 'DE',
            'entity_name' => 'Innovate Corp',
            'business_purpose' => 'Technology innovation and product development',
            'shareholders' => json_encode([
                [
                    'name' => 'Sarah Johnson',
                    'address' => '456 Tech Ave, San Francisco, CA 94105',
                    'shares' => 1000,
                    'ownership_percentage' => 100
                ]
            ]),
            'directors' => json_encode([
                [
                    'name' => 'Sarah Johnson',
                    'address' => '456 Tech Ave, San Francisco, CA 94105',
                    'role' => 'CEO/Director'
                ]
            ]),
            'officers' => json_encode([
                [
                    'name' => 'Sarah Johnson',
                    'title' => 'Chief Executive Officer',
                    'address' => '456 Tech Ave, San Francisco, CA 94105'
                ]
            ]),
            'authorized_shares' => 10000,
            'par_value' => 0.001,
            'status' => 'completed',
            'service_fee' => 499.00,
            'state_fee' => 89.00,
            'total_amount' => 588.00,
            'amount_paid' => 588.00,
            'processing_speed' => 'expedited',
            'estimated_days' => 3,
            'ein' => '12-3456789',
            'ein_received_date' => now()->subDays(2),
            'state_confirmation_number' => 'DE-2025-12345',
            'state_filing_date' => now()->subDays(5),
            'state_approval_date' => now()->subDays(3),
            'submitted_at' => now()->subDays(10),
            'completed_at' => now()->subDays(1),
        ]);

        // Create sample payments
        Payment::create([
            'payment_id' => 'PAY-2025-000001',
            'user_id' => $client1->id,
            'order_id' => $order1->id,
            'amount' => 389.00,
            'currency' => 'USD',
            'type' => 'order_payment',
            'description' => 'LLC Formation - Tech Innovations LLC',
            'status' => 'succeeded',
            'payment_method' => 'card',
            'card_last_four' => '4242',
            'card_brand' => 'visa',
            'card_exp_month' => '12',
            'card_exp_year' => '2027',
            'processing_fee' => 11.67,
            'net_amount' => 377.33,
            'processed_at' => now()->subDays(3),
        ]);

        Payment::create([
            'payment_id' => 'PAY-2025-000002',
            'user_id' => $client2->id,
            'order_id' => $order2->id,
            'amount' => 588.00,
            'currency' => 'USD',
            'type' => 'order_payment',
            'description' => 'C-Corporation Formation - Innovate Corp',
            'status' => 'succeeded',
            'payment_method' => 'card',
            'card_last_four' => '1234',
            'card_brand' => 'mastercard',
            'card_exp_month' => '08',
            'card_exp_year' => '2026',
            'processing_fee' => 17.64,
            'net_amount' => 570.36,
            'processed_at' => now()->subDays(10),
        ]);

        // Create sample documents
        Document::create([
            'document_id' => 'DOC-2025-000001',
            'user_id' => $client2->id,
            'order_id' => $order2->id,
            'name' => 'Certificate of Incorporation - Innovate Corp',
            'display_name' => 'Certificate of Incorporation',
            'type' => 'certificate_of_incorporation',
            'description' => 'Official Certificate of Incorporation from Delaware Division of Corporations',
            'file_path' => 'documents/2025/certificate_of_incorporation_innovate_corp.pdf',
            'original_filename' => 'certificate_of_incorporation.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 2457600, // 2.4 MB
            'file_extension' => 'pdf',
            'status' => 'approved',
            'visibility' => 'private',
        ]);

        Document::create([
            'document_id' => 'DOC-2025-000002',
            'user_id' => $client1->id,
            'order_id' => $order1->id,
            'name' => 'Operating Agreement - Tech Innovations LLC',
            'display_name' => 'Operating Agreement',
            'type' => 'operating_agreement',
            'description' => 'LLC Operating Agreement draft for review',
            'file_path' => 'documents/2025/operating_agreement_tech_innovations.pdf',
            'original_filename' => 'operating_agreement_draft.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1843200, // 1.8 MB
            'file_extension' => 'pdf',
            'status' => 'pending_review',
            'visibility' => 'private',
        ]);

        // Create sample messages
        Message::create([
            'message_id' => 'MSG-2025-000001',
            'sender_id' => $admin->id,
            'recipient_id' => $client1->id,
            'order_id' => $order1->id,
            'subject' => 'LLC Formation Update - Document Review',
            'body' => 'Hello John, your Operating Agreement has been prepared and is ready for your review. Please check the Documents section of your dashboard to download and review the draft. If you have any questions or need modifications, please let us know.',
            'type' => 'order_update',
            'status' => 'sent',
            'priority' => 'normal',
            'sender_type' => 'admin',
            'is_read' => false,
            'is_thread_starter' => true,
        ]);

        Message::create([
            'message_id' => 'MSG-2025-000002',
            'sender_id' => $admin->id,
            'recipient_id' => $client2->id,
            'order_id' => $order2->id,
            'subject' => 'Congratulations! Your C-Corporation is Complete',
            'body' => 'Dear Sarah, we are pleased to inform you that your C-Corporation "Innovate Corp" has been successfully formed in Delaware. Your Certificate of Incorporation and EIN Letter are now available in your dashboard. Welcome to the world of corporate business!',
            'type' => 'completion_notice',
            'status' => 'sent',
            'priority' => 'high',
            'sender_type' => 'admin',
            'is_read' => true,
            'read_at' => now()->subHours(2),
            'is_thread_starter' => true,
        ]);

        Message::create([
            'message_id' => 'MSG-2025-000003',
            'sender_id' => $client1->id,
            'recipient_id' => $admin->id,
            'order_id' => $order1->id,
            'subject' => 'Question about Operating Agreement',
            'body' => 'Hi, I reviewed the Operating Agreement and have a question about the management structure section. Could we schedule a call to discuss some modifications?',
            'type' => 'general',
            'status' => 'sent',
            'priority' => 'normal',
            'sender_type' => 'client',
            'is_read' => false,
            'requires_response' => true,
            'response_due_at' => now()->addDays(2),
            'is_thread_starter' => true,
        ]);

        // Note: Notifications will be created automatically through system events
        // No dummy notifications seeded to keep data clean

        $this->command->info('Sample data seeded successfully!');
    }
}