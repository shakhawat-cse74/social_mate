<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\CrmActivity;
use App\Models\CrmCustomer;
use App\Models\CrmDeal;
use App\Models\CrmLead;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErpCrmTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_tenant_admin_can_view_erp_leads(): void
    {
        $tenantUser = User::where('email', 'owner@apex.test')->first();

        $response = $this->actingAs($tenantUser)->get('/tenant/crm/leads');

        $response->assertStatus(200);
        $response->assertSee('ERP Leads Management');
        $response->assertSee('Bulk Purchase: 20x Dell XPS 15');
    }

    public function test_tenant_admin_can_create_new_erp_lead(): void
    {
        $tenantUser = User::where('email', 'owner@apex.test')->first();

        $response = $this->actingAs($tenantUser)->post('/tenant/crm/leads', [
            'title' => 'Inquiry for 100 Gaming Keyboards',
            'company_name' => 'CyberZone Cyber Cafe',
            'first_name' => 'Jamil',
            'last_name' => 'Ahmed',
            'email' => 'jamil@cyberzone.test',
            'phone' => '+8801999887766',
            'source' => 'whatsapp',
            'priority' => 'high',
            'estimated_budget' => 5000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('crm_leads', [
            'tenant_id' => $tenantUser->tenant_id,
            'title' => 'Inquiry for 100 Gaming Keyboards',
            'company_name' => 'CyberZone Cyber Cafe',
        ]);
    }

    public function test_tenant_can_convert_erp_lead_to_customer_account_and_deal(): void
    {
        $tenantUser = User::where('email', 'owner@apex.test')->first();
        $lead = CrmLead::where('lead_code', 'LEAD-2026-001')->first();

        $response = $this->actingAs($tenantUser)->post("/tenant/crm/leads/{$lead->id}/convert", [
            'payment_terms' => 'Net 30',
            'credit_limit' => 30000,
            'deal_title' => 'Deal: Dell XPS 15 Bulk Purchase',
            'deal_amount' => 28500,
        ]);

        $response->assertRedirect();

        // Lead should now be converted
        $lead->refresh();
        $this->assertEquals('converted', $lead->status);
        $this->assertNotNull($lead->converted_customer_id);

        // Customer created
        $customer = CrmCustomer::find($lead->converted_customer_id);
        $this->assertNotNull($customer);
        $this->assertEquals('TechVista BD', $customer->name);

        // Deal created
        $this->assertDatabaseHas('crm_deals', [
            'customer_id' => $customer->id,
            'title' => 'Deal: Dell XPS 15 Bulk Purchase',
            'amount' => 28500,
        ]);
    }

    public function test_tenant_can_view_erp_customer_accounts_and_360_profile(): void
    {
        $tenantUser = User::where('email', 'owner@apex.test')->first();
        $customer = CrmCustomer::where('customer_code', 'CUST-APEX-001')->first();

        $listResponse = $this->actingAs($tenantUser)->get('/tenant/crm/customers');
        $listResponse->assertStatus(200);
        $listResponse->assertSee('Grameen Solution Ltd.');

        $profileResponse = $this->actingAs($tenantUser)->get("/tenant/crm/customers/{$customer->id}");
        $profileResponse->assertStatus(200);
        $profileResponse->assertSee('Grameen Solution Ltd.');
        $profileResponse->assertSee('CUST-APEX-001');
    }

    public function test_tenant_can_manage_pipeline_deals_and_change_stages(): void
    {
        $tenantUser = User::where('email', 'owner@apex.test')->first();
        $deal = CrmDeal::where('deal_code', 'DEAL-8801')->first();

        $response = $this->actingAs($tenantUser)->get('/tenant/crm/deals');
        $response->assertStatus(200);
        $response->assertSee('Annual Laptop Supply Contract 2026');

        // Update stage to won
        $patchResponse = $this->actingAs($tenantUser)->patch("/tenant/crm/deals/{$deal->id}/stage", [
            'stage' => 'won',
        ]);
        $patchResponse->assertRedirect();

        $deal->refresh();
        $this->assertEquals('won', $deal->stage);
    }

    public function test_tenant_can_schedule_and_complete_erp_activities(): void
    {
        $tenantUser = User::where('email', 'owner@apex.test')->first();

        $createResponse = $this->actingAs($tenantUser)->post('/tenant/crm/activities', [
            'type' => 'call',
            'subject' => 'Call client regarding invoice terms',
            'due_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'description' => 'Verify VAT invoice requirement',
        ]);
        $createResponse->assertRedirect();

        $activity = CrmActivity::where('subject', 'Call client regarding invoice terms')->first();
        $this->assertNotNull($activity);
        $this->assertEquals('pending', $activity->status);

        // Complete activity
        $completeResponse = $this->actingAs($tenantUser)->post("/tenant/crm/activities/{$activity->id}/complete", [
            'outcome_notes' => 'Client approved VAT invoice format.',
        ]);
        $completeResponse->assertRedirect();

        $activity->refresh();
        $this->assertEquals('completed', $activity->status);
        $this->assertEquals('Client approved VAT invoice format.', $activity->outcome_notes);
    }

    public function test_can_convert_social_chat_conversation_to_erp_lead_via_one_click(): void
    {
        $tenantUser = User::where('email', 'owner@apex.test')->first();
        $conversation = Conversation::first();

        $response = $this->actingAs($tenantUser)->postJson("/tenant/crm/convert-social/{$conversation->id}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('crm_leads', [
            'tenant_id' => $tenantUser->tenant_id,
            'contact_id' => $conversation->contact_id,
        ]);
    }

    public function test_tenant_isolation_prevents_unauthorized_access_to_erp_records(): void
    {
        $tenant2User = User::where('email', 'owner@boutique.test')->first();
        $apexCustomer = CrmCustomer::where('customer_code', 'CUST-APEX-001')->first();

        // Tenant 2 trying to access Tenant 1's customer account
        $response = $this->actingAs($tenant2User)->get("/tenant/crm/customers/{$apexCustomer->id}");
        $response->assertStatus(403);
    }

    public function test_tenant_can_view_crm_analytics_dashboard(): void
    {
        $tenantUser = User::where('email', 'owner@apex.test')->first();

        $response = $this->actingAs($tenantUser)->get('/tenant/crm/analytics');

        $response->assertStatus(200);
        $response->assertSee('Sales Pipeline Funnel');
        $response->assertSee('Sales Team Performance Leaderboard');
        $response->assertSee('Lead Conversion');
    }
}
