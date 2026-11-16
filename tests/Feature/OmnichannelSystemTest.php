<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OmnichannelSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');

        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('Sign In to Dashboard');
    }

    public function test_meta_webhook_challenge_verification(): void
    {
        $channel = Channel::firstOrCreate(
            ['platform' => 'facebook'],
            ['name' => 'FB Page', 'verify_token' => 'meta_test_token']
        );

        $response = $this->get('/api/webhooks/meta/' . $channel->id . '?hub_mode=subscribe&hub_verify_token=meta_test_token&hub_challenge=123456789');

        $response->assertStatus(200);
        $this->assertEquals('123456789', $response->getContent());
    }

    public function test_inbound_meta_message_triggers_ai_auto_reply(): void
    {
        $channel = Channel::firstOrCreate(
            ['platform' => 'facebook'],
            ['name' => 'FB Page', 'is_active' => true, 'ai_enabled' => true]
        );

        $payload = [
            'entry' => [
                [
                    'messaging' => [
                        [
                            'sender' => ['id' => 'test_user_9988'],
                            'message' => [
                                'mid' => 'mid_test_' . uniqid(),
                                'text' => 'Hello! What is your pricing?'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/webhooks/meta/' . $channel->id, $payload);
        $response->assertStatus(200);

        // Check Contact created
        $contact = Contact::where('platform_sender_id', 'test_user_9988')->first();
        $this->assertNotNull($contact);
        $this->assertContains('hot_lead', $contact->tags);

        // Check Conversation created
        $conversation = Conversation::where('contact_id', $contact->id)->first();
        $this->assertNotNull($conversation);

        // Check Messages: Inbound + AI Auto Reply
        $messages = $conversation->messages;
        $this->assertGreaterThanOrEqual(2, $messages->count());
        $this->assertTrue($messages->contains('direction', 'inbound'));
        $this->assertTrue($messages->contains('sender_type', 'ai_agent'));
    }

    public function test_human_handover_triggers_on_keyword(): void
    {
        $channel = Channel::firstOrCreate(
            ['platform' => 'facebook'],
            ['name' => 'FB Page', 'is_active' => true, 'ai_enabled' => true]
        );

        $payload = [
            'entry' => [
                [
                    'messaging' => [
                        [
                            'sender' => ['id' => 'test_user_human_req'],
                            'message' => [
                                'mid' => 'mid_test_human_' . uniqid(),
                                'text' => 'I need to speak to a human support agent immediately'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/webhooks/meta/' . $channel->id, $payload);
        $response->assertStatus(200);

        $contact = Contact::where('platform_sender_id', 'test_user_human_req')->first();
        $conversation = Conversation::where('contact_id', $contact->id)->first();

        // AI mode should now be human_takeover!
        $this->assertEquals('human_takeover', $conversation->ai_mode);
        $this->assertContains('human_requested', $contact->tags);
    }

    public function test_outbound_reply_api(): void
    {
        $channel = Channel::create(['name' => 'Webchat', 'platform' => 'webchat']);
        $contact = Contact::create(['name' => 'Test User', 'platform' => 'webchat', 'platform_sender_id' => 'user_123']);
        $conv = Conversation::create(['channel_id' => $channel->id, 'contact_id' => $contact->id]);

        $response = $this->postJson('/api/conversations/' . $conv->id . '/reply', [
            'content' => 'Hello! This is a human agent replying to your inquiry.',
            'sender_type' => 'human_agent'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conv->id,
            'direction' => 'outbound',
            'sender_type' => 'human_agent'
        ]);
    }

    public function test_webchat_widget_endpoints(): void
    {
        $sessionId = 'test_session_' . uniqid();

        $sendResponse = $this->postJson('/api/webchat/send', [
            'session_id' => $sessionId,
            'name' => 'Web Tester',
            'content' => 'Hi from external widget'
        ]);

        $sendResponse->assertStatus(200);

        $messagesResponse = $this->getJson('/api/webchat/messages?session_id=' . $sessionId);
        $messagesResponse->assertStatus(200);
        $this->assertNotEmpty($messagesResponse->json('messages'));
    }

    public function test_super_admin_can_access_admin_dashboard(): void
    {
        $admin = \App\Models\User::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Client Organizations');
    }

    public function test_tenant_can_access_tenant_dashboard(): void
    {
        $tenant = \App\Models\Tenant::create([
            'name' => 'Demo Tenant',
            'slug' => 'demo-tenant',
            'status' => 'active',
            'allowed_channels' => ['facebook', 'webchat'],
        ]);

        $user = \App\Models\User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'tenant_admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/tenant/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Live metrics for');
    }

    public function test_user_can_register_new_tenant_account(): void
    {
        $registerPageResponse = $this->get('/register');
        $registerPageResponse->assertStatus(200);
        $registerPageResponse->assertSee('Start your 14-day free trial');

        $response = $this->post('/register', [
            'company_name' => 'Tech Solutions Ltd',
            'name' => 'Kamrul Hasan',
            'email' => 'kamrul@techsolutions.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/tenant/dashboard');

        $this->assertDatabaseHas('tenants', [
            'name' => 'Tech Solutions Ltd',
            'company_email' => 'kamrul@techsolutions.test',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'kamrul@techsolutions.test',
            'role' => 'tenant_admin',
        ]);
    }
}
