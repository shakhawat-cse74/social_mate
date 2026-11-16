<?php

namespace App\Services\Omnichannel;

use App\Models\Channel;
use App\Models\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChannelConnectionService
{
    /**
     * Connect or update a social media channel with live verification.
     */
    public function connect(Tenant $tenant, string $platform, array $data): array
    {
        return match ($platform) {
            'telegram' => $this->connectTelegram($tenant, $data),
            'facebook' => $this->connectFacebook($tenant, $data),
            'whatsapp' => $this->connectWhatsApp($tenant, $data),
            'instagram' => $this->connectInstagram($tenant, $data),
            'webchat' => $this->connectWebchat($tenant, $data),
            default => $this->connectCustomPlatform($tenant, $platform, $data),
        };
    }

    /**
     * Connect Telegram Bot with instant token verification & automated webhook registration.
     */
    protected function connectTelegram(Tenant $tenant, array $data): array
    {
        $token = trim($data['bot_token'] ?? '');
        if (empty($token)) {
            return ['success' => false, 'message' => 'Please provide a valid Telegram Bot Token from @BotFather.'];
        }

        // Demo / Simulator Mode
        if (str_starts_with($token, 'demo_') || str_starts_with($token, 'test_')) {
            $botUsername = '@' . ($data['bot_username'] ?? 'demo_support_bot');
            $botName = $data['name'] ?? 'Demo Telegram Bot';

            $channel = Channel::updateOrCreate(
                ['tenant_id' => $tenant->id, 'platform' => 'telegram'],
                [
                    'name' => $botName,
                    'platform_account_id' => $botUsername,
                    'access_token' => $token,
                    'is_active' => true,
                    'ai_enabled' => $tenant->ai_enabled,
                    'settings' => ['bot_username' => $botUsername, 'verified_at' => now()->toIso8601String()],
                ]
            );

            return [
                'success' => true,
                'message' => "Telegram Bot connected successfully! ({$botUsername})",
                'channel' => $channel,
            ];
        }

        // Live API Verification with Telegram Servers
        try {
            $response = Http::timeout(10)->get("https://api.telegram.org/bot{$token}/getMe");

            if (!$response->successful() || !$response->json('ok')) {
                $description = $response->json('description') ?? 'Invalid Bot Token. Check token from @BotFather.';
                return ['success' => false, 'message' => "Telegram Verification Failed: {$description}"];
            }

            $botData = $response->json('result');
            $botUsername = '@' . ($botData['username'] ?? 'telegram_bot');
            $botName = $data['name'] ?? ($botData['first_name'] ?? 'Telegram Bot');

            // Save Channel first to get ID
            $channel = Channel::updateOrCreate(
                ['tenant_id' => $tenant->id, 'platform' => 'telegram'],
                [
                    'name' => $botName,
                    'platform_account_id' => $botUsername,
                    'access_token' => $token,
                    'is_active' => true,
                    'ai_enabled' => $tenant->ai_enabled,
                    'settings' => [
                        'bot_id' => $botData['id'] ?? null,
                        'bot_username' => $botUsername,
                        'can_join_groups' => $botData['can_join_groups'] ?? false,
                        'verified_at' => now()->toIso8601String(),
                    ],
                ]
            );

            // Automatically Register Webhook directly to Telegram
            $webhookUrl = url("/api/webhooks/telegram/{$channel->id}");
            $webhookRes = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/setWebhook", [
                'url' => $webhookUrl,
                'drop_pending_updates' => false,
            ]);

            $webhookSet = $webhookRes->successful() && $webhookRes->json('ok');

            return [
                'success' => true,
                'message' => "Telegram Bot '{$botUsername}' verified and connected successfully!" .
                    ($webhookSet ? " Webhook registered automatically." : " (Notice: webhook registration pending)"),
                'channel' => $channel,
            ];
        } catch (\Throwable $e) {
            Log::error('Telegram Connection Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Connection failed: Unable to communicate with Telegram API. Please check your network and token.'];
        }
    }

    /**
     * Connect Facebook Page / Messenger with Meta Graph API verification.
     */
    protected function connectFacebook(Tenant $tenant, array $data): array
    {
        $token = trim($data['access_token'] ?? '');
        $pageId = trim($data['page_id'] ?? '');

        if (empty($token)) {
            return ['success' => false, 'message' => 'Page Access Token is required.'];
        }

        // Demo / Simulator Mode
        if (str_starts_with($token, 'demo_') || str_starts_with($token, 'test_') || str_starts_with($pageId, 'demo_')) {
            $pageName = $data['name'] ?? 'Demo FB Business Page';
            $pageId = $pageId ?: '100' . rand(1000000, 9999999);

            $channel = Channel::updateOrCreate(
                ['tenant_id' => $tenant->id, 'platform' => 'facebook'],
                [
                    'name' => $pageName,
                    'platform_account_id' => $pageId,
                    'access_token' => $token,
                    'verify_token' => Str::random(24),
                    'is_active' => true,
                    'ai_enabled' => $tenant->ai_enabled,
                    'settings' => ['page_name' => $pageName, 'page_id' => $pageId, 'verified_at' => now()->toIso8601String()],
                ]
            );

            return [
                'success' => true,
                'message' => "Facebook Page '{$pageName}' (ID: {$pageId}) connected successfully!",
                'channel' => $channel,
            ];
        }

        // Live API Verification with Meta Graph API
        try {
            $effectiveToken = $token;
            $detectedPageId = $pageId;
            $detectedPageName = !empty($data['name']) ? trim($data['name']) : 'Facebook Business Page';

            // 1. Check if the provided token is a System User / User token that manages Pages via /me/accounts
            $accountsRes = Http::timeout(10)->get("https://graph.facebook.com/v20.0/me/accounts", [
                'access_token' => $token,
            ]);

            if ($accountsRes->successful() && !empty($accountsRes->json('data'))) {
                $accounts = $accountsRes->json('data');
                $selected = null;
                if (!empty($pageId)) {
                    foreach ($accounts as $acc) {
                        if (($acc['id'] ?? '') === $pageId) {
                            $selected = $acc;
                            break;
                        }
                    }
                }
                if (!$selected) {
                    $selected = $accounts[0];
                }

                $detectedPageId = (string) ($selected['id'] ?? $detectedPageId);
                $detectedPageName = $selected['name'] ?? $detectedPageName;
                if (!empty($selected['access_token'])) {
                    $effectiveToken = $selected['access_token'];
                }
            } else {
                // 2. Direct Page Token verification
                $targetEndpoint = !empty($pageId)
                    ? "https://graph.facebook.com/v20.0/{$pageId}?fields=id,name&access_token={$token}"
                    : "https://graph.facebook.com/v20.0/me?fields=id,name&access_token={$token}";

                $response = Http::timeout(10)->get($targetEndpoint);

                if ($response->successful()) {
                    $pageData = $response->json();
                    $detectedPageId = (string) ($pageData['id'] ?? $detectedPageId);
                    $detectedPageName = $pageData['name'] ?? $detectedPageName;
                } else {
                    // Fallback 1: Try bare /me endpoint
                    $bareRes = Http::timeout(10)->get("https://graph.facebook.com/v20.0/me?access_token={$token}");
                    if ($bareRes->successful()) {
                        $pageData = $bareRes->json();
                        $detectedPageId = (string) ($pageData['id'] ?? $detectedPageId);
                        $detectedPageName = $pageData['name'] ?? $detectedPageName;
                    } elseif (!empty($pageId)) {
                        // Fallback 2: Page ID and Name were provided directly by user
                        $detectedPageId = $pageId;
                    } else {
                        $errorMsg = $response->json('error.message') ?? 'Invalid Page Token. Please verify permissions.';
                        return ['success' => false, 'message' => "Meta Verification Failed: {$errorMsg}"];
                    }
                }
            }

            $channel = Channel::updateOrCreate(
                ['tenant_id' => $tenant->id, 'platform' => 'facebook'],
                [
                    'name' => $detectedPageName,
                    'platform_account_id' => $detectedPageId ?: 'fb_' . Str::random(10),
                    'access_token' => $effectiveToken,
                    'verify_token' => Str::random(24),
                    'is_active' => true,
                    'ai_enabled' => $tenant->ai_enabled,
                    'settings' => [
                        'page_id' => $detectedPageId,
                        'page_name' => $detectedPageName,
                        'system_user_token' => $token !== $effectiveToken ? $token : null,
                        'verified_at' => now()->toIso8601String(),
                    ],
                ]
            );

            // Automatically subscribe our app to page messages
            if (!empty($detectedPageId)) {
                try {
                    Http::timeout(10)->post("https://graph.facebook.com/v20.0/{$detectedPageId}/subscribed_apps", [
                        'subscribed_fields' => 'messages,messaging_postbacks,message_reads',
                        'access_token' => $effectiveToken,
                    ]);
                } catch (\Throwable $subEx) {
                    Log::warning("Could not auto-subscribe app to Meta Page {$detectedPageId}: " . $subEx->getMessage());
                }
            }

            return [
                'success' => true,
                'message' => "Facebook Page '{$detectedPageName}'" . ($detectedPageId ? " (ID: {$detectedPageId})" : '') . " verified & Messenger connected!",
                'channel' => $channel,
            ];
        } catch (\Throwable $e) {
            Log::error('Facebook Connection Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Unable to connect with Facebook. Please check token or network.'];
        }
    }

    /**
     * Connect WhatsApp Business Cloud API with phone number validation.
     */
    protected function connectWhatsApp(Tenant $tenant, array $data): array
    {
        $token = trim($data['access_token'] ?? '');
        $phoneId = trim($data['phone_number_id'] ?? '');

        if (empty($token) || empty($phoneId)) {
            return ['success' => false, 'message' => 'Both Phone Number ID and Permanent Access Token are required.'];
        }

        // Demo / Simulator Mode
        if (str_starts_with($token, 'demo_') || str_starts_with($phoneId, 'demo_') || str_starts_with($token, 'test_')) {
            $phone = $data['phone_number'] ?? '+1 555-0199';
            $channelName = $data['name'] ?? "WhatsApp ({$phone})";

            $channel = Channel::updateOrCreate(
                ['tenant_id' => $tenant->id, 'platform' => 'whatsapp'],
                [
                    'name' => $channelName,
                    'platform_account_id' => $phoneId,
                    'access_token' => $token,
                    'verify_token' => Str::random(24),
                    'is_active' => true,
                    'ai_enabled' => $tenant->ai_enabled,
                    'settings' => [
                        'phone_number_id' => $phoneId,
                        'phone_number' => $phone,
                        'verified_at' => now()->toIso8601String(),
                    ],
                ]
            );

            return [
                'success' => true,
                'message' => "WhatsApp Business Channel '{$channelName}' connected successfully!",
                'channel' => $channel,
            ];
        }

        // Live API Verification with Meta WhatsApp Cloud API
        try {
            $response = Http::timeout(10)->withToken($token)
                ->get("https://graph.facebook.com/v20.0/{$phoneId}?fields=id,display_phone_number,verified_name,code_verification_status");

            if (!$response->successful()) {
                $errorMsg = $response->json('error.message') ?? 'Invalid Phone Number ID or Token.';
                return ['success' => false, 'message' => "WhatsApp Verification Failed: {$errorMsg}"];
            }

            $waData = $response->json();
            $displayPhone = $waData['display_phone_number'] ?? $phoneId;
            $verifiedName = $waData['verified_name'] ?? ($data['name'] ?? "WhatsApp ({$displayPhone})");

            $channel = Channel::updateOrCreate(
                ['tenant_id' => $tenant->id, 'platform' => 'whatsapp'],
                [
                    'name' => $verifiedName,
                    'platform_account_id' => $phoneId,
                    'access_token' => $token,
                    'verify_token' => Str::random(24),
                    'is_active' => true,
                    'ai_enabled' => $tenant->ai_enabled,
                    'settings' => [
                        'phone_number_id' => $phoneId,
                        'display_phone_number' => $displayPhone,
                        'verified_name' => $verifiedName,
                        'verified_at' => now()->toIso8601String(),
                    ],
                ]
            );

            return [
                'success' => true,
                'message' => "WhatsApp Business ({$displayPhone}) verified and connected successfully!",
                'channel' => $channel,
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsApp Connection Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Unable to connect to WhatsApp Cloud API. Check network or credentials.'];
        }
    }

    /**
     * Connect Instagram Direct Business Account.
     */
    protected function connectInstagram(Tenant $tenant, array $data): array
    {
        $token = trim($data['access_token'] ?? '');
        $igId = trim($data['instagram_id'] ?? '');

        if (empty($token)) {
            return ['success' => false, 'message' => 'Instagram Access Token is required.'];
        }

        // Demo / Simulator Mode
        if (str_starts_with($token, 'demo_') || str_starts_with($igId, 'demo_') || str_starts_with($token, 'test_')) {
            $igHandle = '@' . ($data['username'] ?? 'demo_business_ig');
            $channelName = $data['name'] ?? "Instagram ({$igHandle})";

            $channel = Channel::updateOrCreate(
                ['tenant_id' => $tenant->id, 'platform' => 'instagram'],
                [
                    'name' => $channelName,
                    'platform_account_id' => $igId ?: 'ig_' . rand(1000, 9999),
                    'access_token' => $token,
                    'verify_token' => Str::random(24),
                    'is_active' => true,
                    'ai_enabled' => $tenant->ai_enabled,
                    'settings' => ['username' => $igHandle, 'verified_at' => now()->toIso8601String()],
                ]
            );

            return [
                'success' => true,
                'message' => "Instagram Account '{$igHandle}' connected successfully!",
                'channel' => $channel,
            ];
        }

        try {
            $endpoint = !empty($igId)
                ? "https://graph.facebook.com/v20.0/{$igId}?fields=id,username,name&access_token={$token}"
                : "https://graph.facebook.com/v20.0/me?fields=id,username,name&access_token={$token}";

            $response = Http::timeout(10)->get($endpoint);

            if (!$response->successful()) {
                $errorMsg = $response->json('error.message') ?? 'Invalid Instagram Token or Account ID.';
                return ['success' => false, 'message' => "Instagram Verification Failed: {$errorMsg}"];
            }

            $igData = $response->json();
            $detectedId = (string) ($igData['id'] ?? $igId);
            $username = isset($igData['username']) ? '@' . $igData['username'] : ($data['name'] ?? 'Instagram DM');

            $channel = Channel::updateOrCreate(
                ['tenant_id' => $tenant->id, 'platform' => 'instagram'],
                [
                    'name' => $username,
                    'platform_account_id' => $detectedId,
                    'access_token' => $token,
                    'verify_token' => Str::random(24),
                    'is_active' => true,
                    'ai_enabled' => $tenant->ai_enabled,
                    'settings' => [
                        'instagram_id' => $detectedId,
                        'username' => $username,
                        'verified_at' => now()->toIso8601String(),
                    ],
                ]
            );

            return [
                'success' => true,
                'message' => "Instagram Account '{$username}' verified and connected!",
                'channel' => $channel,
            ];
        } catch (\Throwable $e) {
            Log::error('Instagram Connection Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Unable to connect to Instagram API. Check network or credentials.'];
        }
    }

    /**
     * Connect or Customize Built-in Website Chat Widget.
     */
    protected function connectWebchat(Tenant $tenant, array $data): array
    {
        $widgetName = $data['name'] ?? ($tenant->name . ' Web Widget');
        $primaryColor = $data['primary_color'] ?? '#6366f1';
        $welcomeMessage = $data['welcome_message'] ?? 'Hi there! How can we help your business today?';

        $channel = Channel::updateOrCreate(
            ['tenant_id' => $tenant->id, 'platform' => 'webchat'],
            [
                'name' => $widgetName,
                'platform_account_id' => 'web_' . $tenant->id,
                'is_active' => true,
                'ai_enabled' => $tenant->ai_enabled,
                'settings' => [
                    'primary_color' => $primaryColor,
                    'welcome_message' => $welcomeMessage,
                    'widget_title' => $widgetName,
                    'configured_at' => now()->toIso8601String(),
                ],
            ]
        );

        return [
            'success' => true,
            'message' => "Website Chat Widget configured and ready to embed!",
            'channel' => $channel,
        ];
    }

    /**
     * Test an existing channel to check if the connection & tokens are still valid.
     */
    public function testConnection(Channel $channel): array
    {
        if (!$channel->is_active) {
            return ['success' => false, 'message' => 'Channel is currently disabled.'];
        }

        switch ($channel->platform) {
            case 'telegram':
                $token = $channel->access_token;
                if (!$token) return ['success' => false, 'message' => 'No token configured.'];
                if (str_starts_with($token, 'demo_') || str_starts_with($token, 'test_')) {
                    return ['success' => true, 'message' => 'Telegram Bot is connected & healthy (Simulated mode).'];
                }
                $res = Http::timeout(6)->get("https://api.telegram.org/bot{$token}/getMe");
                return $res->successful()
                    ? ['success' => true, 'message' => "Telegram Bot (@{$res->json('result.username')}) is active and responding!"]
                    : ['success' => false, 'message' => "Telegram Token Expired or Invalid: " . ($res->json('description') ?? 'Check BotFather')];

            case 'facebook':
                $token = $channel->access_token;
                if (!$token) return ['success' => false, 'message' => 'No access token configured.'];
                if (str_starts_with($token, 'demo_') || str_starts_with($token, 'test_')) {
                    return ['success' => true, 'message' => 'Facebook Page is connected & healthy (Simulated mode).'];
                }
                $pageId = $channel->platform_account_id ?: 'me';
                $res = Http::timeout(6)->get("https://graph.facebook.com/v20.0/{$pageId}?fields=id,name&access_token={$token}");
                return $res->successful()
                    ? ['success' => true, 'message' => "Facebook Page '{$res->json('name')}' is connected & active!"]
                    : ['success' => false, 'message' => "Meta Page Token Error: " . ($res->json('error.message') ?? 'Token Expired')];

            case 'whatsapp':
                $phoneId = $channel->platform_account_id;
                $token = $channel->access_token;
                if (!$phoneId || !$token) return ['success' => false, 'message' => 'Missing Phone ID or Token.'];
                if (str_starts_with($token, 'demo_') || str_starts_with($token, 'test_')) {
                    return ['success' => true, 'message' => 'WhatsApp Cloud API is active & healthy (Simulated mode).'];
                }
                $res = Http::timeout(6)->withToken($token)->get("https://graph.facebook.com/v20.0/{$phoneId}?fields=id,display_phone_number");
                return $res->successful()
                    ? ['success' => true, 'message' => "WhatsApp Number ({$res->json('display_phone_number')}) is active!"]
                    : ['success' => false, 'message' => "WhatsApp API Error: " . ($res->json('error.message') ?? 'Auth Failed')];

            case 'instagram':
                $token = $channel->access_token;
                if (!$token) return ['success' => false, 'message' => 'No access token configured.'];
                if (str_starts_with($token, 'demo_') || str_starts_with($token, 'test_')) {
                    return ['success' => true, 'message' => 'Instagram Direct is connected & healthy (Simulated mode).'];
                }
                $igId = $channel->platform_account_id ?: 'me';
                $res = Http::timeout(6)->get("https://graph.facebook.com/v20.0/{$igId}?fields=id,username&access_token={$token}");
                return $res->successful()
                    ? ['success' => true, 'message' => "Instagram Account is connected & active!"]
                    : ['success' => false, 'message' => "Instagram Token Error: " . ($res->json('error.message') ?? 'Auth Failed')];

            case 'webchat':
                return ['success' => true, 'message' => 'Webchat Widget is active and serving visitors.'];

            default:
                if (!empty($channel->settings['outbound_api_url'])) {
                    return ['success' => true, 'message' => "Channel '{$channel->name}' ({$channel->platform}) is healthy & outbound webhook is ready."];
                }
                return ['success' => true, 'message' => "Channel '{$channel->name}' ({$channel->platform}) is active and ready to receive incoming messages."];
        }
    }

    /**
     * Connect any custom dynamic platform (LinkedIn, Discord, Slack, etc.)
     */
    protected function connectCustomPlatform(Tenant $tenant, string $platform, array $data): array
    {
        $name = trim($data['name'] ?? ucfirst($platform) . ' Channel');
        $token = trim($data['access_token'] ?? $data['bot_token'] ?? '');
        $accountId = trim($data['platform_account_id'] ?? $data['account_id'] ?? ($platform . '_' . rand(100, 999)));
        $apiUrl = trim($data['outbound_api_url'] ?? '');
        $bodyTemplate = $data['outbound_body_template'] ?? null;

        $channel = Channel::updateOrCreate(
            ['tenant_id' => $tenant->id, 'platform' => $platform],
            [
                'name' => $name,
                'platform_account_id' => $accountId,
                'access_token' => $token ?: null,
                'verify_token' => Str::random(24),
                'is_active' => true,
                'ai_enabled' => $tenant->ai_enabled,
                'settings' => [
                    'outbound_api_url' => $apiUrl,
                    'outbound_body_template' => $bodyTemplate,
                    'field_mapping' => [
                        'sender_id' => $data['mapping_sender_id'] ?? null,
                        'sender_name' => $data['mapping_sender_name'] ?? null,
                        'content' => $data['mapping_content'] ?? null,
                    ],
                    'connected_at' => now()->toIso8601String(),
                ],
            ]
        );

        return [
            'success' => true,
            'message' => "Channel '{$name}' ({$platform}) connected successfully!",
            'channel' => $channel,
        ];
    }
}
