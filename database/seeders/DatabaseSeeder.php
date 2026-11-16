<?php

namespace Database\Seeders;

use App\Models\AiConfig;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\CrmActivity;
use App\Models\CrmCustomer;
use App\Models\CrmDeal;
use App\Models\CrmLead;
use App\Models\Message;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WebhookEndpoint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Super Admin User (The Platform Owner)
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Administrator',
                'role' => 'super_admin',
                'password' => Hash::make('12345678'),
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@socialcrm.test'],
            [
                'name' => 'System Super Admin',
                'role' => 'super_admin',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );

        // 2. Tenant 1: Apex Electronics (Growth Plan - Omnichannel + AI)
        $tenant1 = Tenant::updateOrCreate(
            ['slug' => 'apex-electronics'],
            [
                'name' => 'Apex Electronics',
                'company_email' => 'contact@apex.test',
                'company_phone' => '+8801700112233',
                'plan_name' => 'Growth',
                'status' => 'active',
                'allowed_channels' => ['facebook', 'whatsapp', 'telegram', 'webchat'],
                'ai_enabled' => true,
                'max_agents' => 5,
                'subscription_starts_at' => now(),
                'subscription_expires_at' => now()->addYear(),
            ]
        );

        // Tenant 1 Admin
        $tenant1Admin = User::updateOrCreate(
            ['email' => 'owner@apex.test'],
            [
                'tenant_id' => $tenant1->id,
                'name' => 'Rahim Chowdhury (Apex Owner)',
                'role' => 'tenant_admin',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );

        // Tenant 1 Agent
        $tenant1Agent = User::updateOrCreate(
            ['email' => 'agent@apex.test'],
            [
                'tenant_id' => $tenant1->id,
                'name' => 'Fahim Support Agent',
                'role' => 'agent',
                'password' => Hash::make('password123'),
                'permissions' => ['can_reply', 'can_manage_leads'],
                'is_active' => true,
            ]
        );

        // Tenant 1 AI Config
        $aiConfig1 = AiConfig::updateOrCreate(
            ['tenant_id' => $tenant1->id],
            [
                'name' => 'Apex Smart Sales & Support Bot',
                'is_active' => true,
                'provider' => 'gemini',
                'model' => 'gemini-1.5-flash',
                'api_key' => null,
                'system_prompt' => "You are an intelligent support representative for Apex Electronics. You assist customers in checking laptop and mobile specs, pricing, and warranty.",
                'knowledge_base' => "Apex Electronics is Bangladesh's leading gadget store.\nProducts: Laptops, Smartphones, Smart Watches, Accessories.\nWarranty: 1 Year Official Replacement Warranty.\nHotline: 09612345678",
                'auto_reply_enabled' => true,
                'handover_keywords' => ['human', 'agent', 'support', 'manush', 'kotha bolbo', 'help'],
            ]
        );

        // Tenant 1 Channels
        $apexFb = Channel::updateOrCreate(
            ['tenant_id' => $tenant1->id, 'platform' => 'facebook'],
            [
                'name' => 'Apex Electronics Facebook Page',
                'platform_account_id' => 'fb_page_apex_101',
                'verify_token' => 'apex_fb_verify_123',
                'access_token' => 'EAAB_sample_apex_token',
                'is_active' => true,
                'ai_enabled' => true,
            ]
        );

        $apexWa = Channel::updateOrCreate(
            ['tenant_id' => $tenant1->id, 'platform' => 'whatsapp'],
            [
                'name' => 'Apex Official WhatsApp Hotline',
                'platform_account_id' => '1088726554123',
                'verify_token' => 'apex_wa_verify_123',
                'access_token' => 'EAAB_sample_apex_wa_token',
                'is_active' => true,
                'ai_enabled' => true,
            ]
        );

        $apexTg = Channel::updateOrCreate(
            ['tenant_id' => $tenant1->id, 'platform' => 'telegram'],
            [
                'name' => 'Apex Support Telegram Bot',
                'platform_account_id' => 'ApexSupportBot',
                'access_token' => '123456789:ABCdefGHIjklMNOpqrSTUvwxYZ',
                'is_active' => true,
                'ai_enabled' => true,
            ]
        );

        $apexWeb = Channel::updateOrCreate(
            ['tenant_id' => $tenant1->id, 'platform' => 'webchat'],
            [
                'name' => 'Apex Web Store Live Chat',
                'is_active' => true,
                'ai_enabled' => true,
            ]
        );

        // Tenant 1 Contacts & Conversations
        $contact1 = Contact::updateOrCreate(
            ['tenant_id' => $tenant1->id, 'platform' => 'facebook', 'platform_sender_id' => 'fb_apex_user_1'],
            [
                'name' => 'Tanvir Ahmed',
                'email' => 'tanvir@gmail.com',
                'phone' => '+8801711223344',
                'lead_status' => 'prospect',
                'pipeline_stage' => 'interested',
                'deal_value' => 850.00,
                'tags' => ['hot_lead', 'laptop_inquiry'],
                'notes' => 'Customer interested in buying Dell XPS 15 laptop.',
                'last_active_at' => now()->subMinutes(10),
            ]
        );

        $conv1 = Conversation::updateOrCreate(
            ['channel_id' => $apexFb->id, 'contact_id' => $contact1->id],
            [
                'status' => 'open',
                'ai_mode' => 'active',
                'unread_count' => 1,
                'last_message_at' => now()->subMinutes(10),
            ]
        );

        Message::updateOrCreate(
            ['platform_message_id' => 'apex_msg_01'],
            [
                'conversation_id' => $conv1->id,
                'contact_id' => $contact1->id,
                'direction' => 'inbound',
                'sender_type' => 'contact',
                'content' => 'Hello! Do you have Dell XPS 15 in stock? What is the official price?',
                'status' => 'read',
                'created_at' => now()->subMinutes(15),
            ]
        );

        Message::updateOrCreate(
            ['platform_message_id' => 'apex_msg_02'],
            [
                'conversation_id' => $conv1->id,
                'contact_id' => $contact1->id,
                'direction' => 'outbound',
                'sender_type' => 'ai_agent',
                'ai_generated' => true,
                'content' => 'Hello Tanvir! 👋 Yes, Dell XPS 15 is available at Apex Electronics with 1 Year Official Replacement Warranty! Would you like our sales team to reserve one for you?',
                'status' => 'delivered',
                'created_at' => now()->subMinutes(14),
            ]
        );

        // 3. Tenant 2: Dhaka Fashion Boutique (Starter Plan - Only Facebook, No AI)
        $tenant2 = Tenant::updateOrCreate(
            ['slug' => 'dhaka-boutique'],
            [
                'name' => 'Dhaka Fashion Boutique',
                'company_email' => 'sales@boutique.test',
                'company_phone' => '+8801800223344',
                'plan_name' => 'Starter',
                'status' => 'active',
                'allowed_channels' => ['facebook', 'webchat'],
                'ai_enabled' => false, // Feature restricted by subscription
                'max_agents' => 2,
                'subscription_starts_at' => now(),
                'subscription_expires_at' => now()->addMonths(6),
            ]
        );

        $tenant2Admin = User::updateOrCreate(
            ['email' => 'owner@boutique.test'],
            [
                'tenant_id' => $tenant2->id,
                'name' => 'Nusrat Jahan (Boutique Owner)',
                'role' => 'tenant_admin',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );

        $boutiqueFb = Channel::updateOrCreate(
            ['tenant_id' => $tenant2->id, 'platform' => 'facebook'],
            [
                'name' => 'Dhaka Boutique Official Page',
                'platform_account_id' => 'fb_page_boutique_202',
                'verify_token' => 'boutique_verify_token',
                'access_token' => 'EAAB_sample_boutique_token',
                'is_active' => true,
                'ai_enabled' => false,
            ]
        );

        $contact2 = Contact::updateOrCreate(
            ['tenant_id' => $tenant2->id, 'platform' => 'facebook', 'platform_sender_id' => 'fb_boutique_user_2'],
            [
                'name' => 'Sadia Islam',
                'email' => 'sadia@yahoo.com',
                'phone' => '+8801912345678',
                'lead_status' => 'lead',
                'pipeline_stage' => 'new',
                'deal_value' => 120.00,
                'tags' => ['eid_collection'],
                'notes' => 'Asked about lawn three-piece.',
                'last_active_at' => now()->subHours(1),
            ]
        );

        $conv2 = Conversation::updateOrCreate(
            ['channel_id' => $boutiqueFb->id, 'contact_id' => $contact2->id],
            [
                'status' => 'open',
                'ai_mode' => 'human_takeover',
                'unread_count' => 0,
                'last_message_at' => now()->subHours(1),
            ]
        );

        Message::updateOrCreate(
            ['platform_message_id' => 'boutique_msg_01'],
            [
                'conversation_id' => $conv2->id,
                'contact_id' => $contact2->id,
                'direction' => 'inbound',
                'sender_type' => 'contact',
                'content' => 'Apar ei dress er size chart ta pawa jabe?',
                'status' => 'read',
                'created_at' => now()->subHours(1),
            ]
        );

        // ==========================================================
        // 4. ERP CRM Suite Data for Tenant 1 (Apex Electronics)
        // ==========================================================

        // Customer Accounts (B2B & High-Value Clients)
        $customer1 = CrmCustomer::updateOrCreate(
            ['tenant_id' => $tenant1->id, 'customer_code' => 'CUST-APEX-001'],
            [
                'type' => 'company',
                'name' => 'Grameen Solution Ltd.',
                'contact_person' => 'Engr. Tareq Mahmud',
                'email' => 'procurement@grameensolution.test',
                'phone' => '+8801712399999',
                'website' => 'https://grameensolution.test',
                'tax_id' => 'TIN-9842104-BD',
                'billing_address' => 'Floor 8, Grameen Complex, Mirpur-2, Dhaka 1216',
                'shipping_address' => 'Central Warehouse, Uttara Sector 3, Dhaka',
                'city' => 'Dhaka',
                'payment_terms' => 'Net 30',
                'credit_limit' => 50000.00,
                'status' => 'active',
                'assigned_user_id' => $tenant1Admin->id,
                'notes' => 'Key corporate IT hardware procurement partner. Annual contract.',
            ]
        );

        $customer2 = CrmCustomer::updateOrCreate(
            ['tenant_id' => $tenant1->id, 'customer_code' => 'CUST-APEX-002'],
            [
                'type' => 'company',
                'name' => 'DataSoft Systems Ltd.',
                'contact_person' => 'Farhana Kabir',
                'email' => 'admin@datasoft.test',
                'phone' => '+8801819876543',
                'website' => 'https://datasoft.test',
                'tax_id' => 'TIN-4451299-BD',
                'billing_address' => 'Level 5, Rupayan Center, Mohakhali C/A, Dhaka',
                'shipping_address' => 'Level 5, Rupayan Center, Mohakhali C/A, Dhaka',
                'city' => 'Dhaka',
                'payment_terms' => 'Net 15',
                'credit_limit' => 25000.00,
                'status' => 'active',
                'assigned_user_id' => $tenant1Agent->id,
                'notes' => 'Requires quarterly upgrade of developer laptops and workstations.',
            ]
        );

        // Leads (Inbound from Facebook / Social Media)
        $lead1 = CrmLead::updateOrCreate(
            ['tenant_id' => $tenant1->id, 'lead_code' => 'LEAD-2026-001'],
            [
                'title' => 'Bulk Purchase: 20x Dell XPS 15 for IT Department',
                'company_name' => 'TechVista BD',
                'first_name' => 'Tanvir',
                'last_name' => 'Ahmed',
                'email' => 'tanvir@gmail.com',
                'phone' => '+8801711223344',
                'source' => 'facebook',
                'channel_id' => $apexFb->id,
                'contact_id' => $contact1->id,
                'status' => 'qualified',
                'priority' => 'high',
                'estimated_budget' => 28500.00,
                'assigned_user_id' => $tenant1Admin->id,
                'notes' => 'Client contacted via Facebook messenger. Inquired about enterprise discount on Dell XPS 15.',
            ]
        );

        $lead2 = CrmLead::updateOrCreate(
            ['tenant_id' => $tenant1->id, 'lead_code' => 'LEAD-2026-002'],
            [
                'title' => 'Network Server & UPS Hardware Inquiry',
                'company_name' => 'Nexus Cloud Labs',
                'first_name' => 'Kamal',
                'last_name' => 'Hossain',
                'email' => 'kamal@nexuscloud.test',
                'phone' => '+8801678112233',
                'source' => 'whatsapp',
                'channel_id' => $apexWa->id,
                'status' => 'contacted',
                'priority' => 'medium',
                'estimated_budget' => 12000.00,
                'assigned_user_id' => $tenant1Agent->id,
                'notes' => 'WhatsApp lead requesting quotation for 3 server racks and battery backup.',
            ]
        );

        // Deals & Pipeline Opportunities
        $deal1 = CrmDeal::updateOrCreate(
            ['tenant_id' => $tenant1->id, 'deal_code' => 'DEAL-8801'],
            [
                'title' => 'Annual Laptop Supply Contract 2026',
                'customer_id' => $customer1->id,
                'lead_id' => $lead1->id,
                'stage' => 'proposal',
                'amount' => 38000.00,
                'probability' => 75,
                'expected_close_date' => now()->addDays(20),
                'assigned_user_id' => $tenant1Admin->id,
                'notes' => 'Quotation #Q-2026-04 sent. Awaiting board review and purchase order.',
            ]
        );

        $deal2 = CrmDeal::updateOrCreate(
            ['tenant_id' => $tenant1->id, 'deal_code' => 'DEAL-8802'],
            [
                'title' => '15x MacBook Pro M3 Workstations',
                'customer_id' => $customer2->id,
                'stage' => 'negotiation',
                'amount' => 29500.00,
                'probability' => 90,
                'expected_close_date' => now()->addDays(7),
                'assigned_user_id' => $tenant1Agent->id,
                'notes' => 'Final pricing negotiated. Preparing delivery schedule.',
            ]
        );

        $deal3 = CrmDeal::updateOrCreate(
            ['tenant_id' => $tenant1->id, 'deal_code' => 'DEAL-8803'],
            [
                'title' => 'Server Room APC Smart-UPS Upgrade',
                'customer_id' => $customer1->id,
                'stage' => 'won',
                'amount' => 14500.00,
                'probability' => 100,
                'expected_close_date' => now()->subDays(5),
                'assigned_user_id' => $tenant1Admin->id,
                'notes' => 'Deal closed and delivered. Invoice #INV-2026-08 issued under Net 30 terms.',
            ]
        );

        // Sales Activities & Follow-ups
        CrmActivity::updateOrCreate(
            ['tenant_id' => $tenant1->id, 'subject' => 'Follow up on Dell XPS Quotation'],
            [
                'type' => 'call',
                'customer_id' => $customer1->id,
                'deal_id' => $deal1->id,
                'lead_id' => $lead1->id,
                'due_date' => now()->addDay()->setHour(11)->setMinute(0),
                'description' => 'Call Engr. Tareq to discuss revised enterprise discount on Dell XPS models.',
                'user_id' => $tenant1Admin->id,
                'status' => 'pending',
            ]
        );

        CrmActivity::updateOrCreate(
            ['tenant_id' => $tenant1->id, 'subject' => 'Finalize Delivery Schedule with DataSoft'],
            [
                'type' => 'meeting',
                'customer_id' => $customer2->id,
                'deal_id' => $deal2->id,
                'due_date' => now()->addDays(2)->setHour(15)->setMinute(30),
                'description' => 'In-person meeting at Mohakhali office to sign procurement agreement.',
                'user_id' => $tenant1Agent->id,
                'status' => 'pending',
            ]
        );

        CrmActivity::updateOrCreate(
            ['tenant_id' => $tenant1->id, 'subject' => 'Initial WhatsApp Lead Qualification'],
            [
                'type' => 'follow_up',
                'lead_id' => $lead2->id,
                'due_date' => now()->subHours(2),
                'description' => 'Sent introductory catalog and price list via WhatsApp.',
                'user_id' => $tenant1Agent->id,
                'status' => 'completed',
                'completed_at' => now()->subHour(),
                'outcome_notes' => 'Customer confirmed budget of $12k. Requested official PDF quotation.',
            ]
        );
    }
}
