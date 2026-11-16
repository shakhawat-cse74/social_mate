<?php

namespace App\Services\AI;

use App\Models\AiConfig;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\Omnichannel\ChannelManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAgentService
{
    /**
     * Handle incoming message with AI Agent logic.
     */
    public function handleInboundMessage(Conversation $conversation, Message $message): void
    {
        $contact = $conversation->contact;
        $content = strtolower(trim($message->content));

        // 1. Check for Active AI Config for this Tenant
        $tenantId = $conversation->channel->tenant_id ?? null;
        $config = $tenantId
            ? AiConfig::where('tenant_id', $tenantId)->where('is_active', true)->first()
            : AiConfig::where('is_active', true)->first();

        if (!$config) {
            $config = AiConfig::create([
                'tenant_id' => $tenantId,
                'name' => 'Default Smart Assistant',
                'is_active' => true,
                'auto_reply_enabled' => true,
                'handover_keywords' => ['human', 'agent', 'support', 'help', 'manush', 'kotha bolbo', 'operator', 'admin', 'live agent'],
            ]);
        }

        if (!$config->auto_reply_enabled) {
            return;
        }

        // 2. Human Handover Check
        $handoverKeywords = $config->handover_keywords ?: [
            'human', 'agent', 'support', 'help', 'manush', 'kotha bolbo', 'operator', 'admin', 'live agent'
        ];

        foreach ($handoverKeywords as $keyword) {
            if (str_contains($content, strtolower($keyword))) {
                $this->triggerHumanHandover($conversation, $message);
                return;
            }
        }

        // 3. Lead Qualification / Intent Tagging
        $this->analyzeLeadIntent($conversation, $message);

        // 4. Generate AI Reply
        $replyText = $this->generateReply($conversation, $message, $config);

        if ($replyText) {
            app(ChannelManager::class)->sendOutboundMessage(
                $conversation,
                $replyText,
                'ai_agent',
                true
            );
        }
    }

    /**
     * Switch conversation to human takeover mode.
     */
    public function triggerHumanHandover(Conversation $conversation, Message $message): void
    {
        $conversation->update([
            'ai_mode' => 'human_takeover',
        ]);

        $contact = $conversation->contact;
        $tags = $contact->tags ?: [];
        if (!in_array('human_requested', $tags)) {
            $tags[] = 'human_requested';
            $contact->update(['tags' => $tags]);
        }

        $handoverMessage = "🤖 Our AI Assistant has handed this conversation over to a human agent. A team member will reply to you shortly!";

        app(ChannelManager::class)->sendOutboundMessage(
            $conversation,
            $handoverMessage,
            'system',
            true
        );
    }

    /**
     * Analyze lead intent and update CRM tags/stage.
     */
    protected function analyzeLeadIntent(Conversation $conversation, Message $message): void
    {
        $text = strtolower($message->content);
        $contact = $conversation->contact;
        $tags = $contact->tags ?: [];

        $pricingKeywords = ['price', 'cost', 'dam koto', 'dam', 'pricing', 'order', 'buy', 'kinbo', 'package'];
        $urgentKeywords = ['urgent', 'emergency', 'asap', 'ajkei lagbe', 'druto'];

        $updated = false;

        foreach ($pricingKeywords as $kw) {
            if (str_contains($text, $kw)) {
                if (!in_array('hot_lead', $tags)) {
                    $tags[] = 'hot_lead';
                    $updated = true;
                }
                if ($contact->pipeline_stage === 'new') {
                    $contact->pipeline_stage = 'interested';
                    $updated = true;
                }
                break;
            }
        }

        foreach ($urgentKeywords as $kw) {
            if (str_contains($text, $kw)) {
                if (!in_array('urgent', $tags)) {
                    $tags[] = 'urgent';
                    $updated = true;
                }
                break;
            }
        }

        if ($updated) {
            $contact->tags = array_values(array_unique($tags));
            $contact->save();
        }
    }

    /**
     * Generate AI response based on conversation history and knowledge base.
     */
    public function generateReply(Conversation $conversation, Message $message, ?AiConfig $config = null): ?string
    {
        if (!$config) {
            $config = AiConfig::where('is_active', true)->first();
        }

        // Fetch recent messages for context (last 6 messages)
        $history = Message::where('conversation_id', $conversation->id)
            ->orderBy('id', 'desc')
            ->take(6)
            ->get()
            ->reverse();

        $systemPrompt = ($config?->system_prompt) ?: "You are a professional, friendly customer service assistant. You assist customers in answering questions, introducing products, and guiding leads.";
        $knowledgeBase = ($config?->knowledge_base) ?: "We provide premium CRM and multi-channel social media integration services.";

        // If Gemini or OpenAI API Key is provided, call real AI provider
        if ($config && $config->api_key) {
            if ($config->provider === 'gemini') {
                return $this->callGeminiApi($config, $systemPrompt, $knowledgeBase, $history, $message);
            } elseif ($config->provider === 'openai') {
                return $this->callOpenAiApi($config, $systemPrompt, $knowledgeBase, $history, $message);
            }
        }

        // Fallback Intelligent Auto-Responder (When API Key is not yet configured)
        return $this->generateSmartLocalReply($message, $knowledgeBase);
    }

    /**
     * Call Google Gemini API.
     */
    protected function callGeminiApi(AiConfig $config, string $systemPrompt, string $knowledgeBase, $history, Message $message): ?string
    {
        try {
            $model = $config->model ?: 'gemini-1.5-flash';
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$config->api_key}";

            $fullSystem = "{$systemPrompt}\n\n[Knowledge Base / Business Context]:\n{$knowledgeBase}\n\nReply concisely and politely.";

            $contents = [
                [
                    'role' => 'user',
                    'parts' => [['text' => "System instruction:\n" . $fullSystem]]
                ],
                [
                    'role' => 'model',
                    'parts' => [['text' => "Understood. I will act according to these instructions and knowledge base."]]
                ]
            ];

            foreach ($history as $hMsg) {
                $role = ($hMsg->sender_type === 'contact') ? 'user' : 'model';
                $contents[] = [
                    'role' => $role,
                    'parts' => [['text' => $hMsg->content]]
                ];
            }

            $response = Http::timeout(10)->post($url, [
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 300,
                ]
            ]);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text');
            }

            Log::warning('Gemini API call returned non-200: ' . $response->body());
        } catch (\Throwable $e) {
            Log::error('Gemini API Exception: ' . $e->getMessage());
        }

        return $this->generateSmartLocalReply($message, $knowledgeBase);
    }

    /**
     * Call OpenAI API.
     */
    protected function callOpenAiApi(AiConfig $config, string $systemPrompt, string $knowledgeBase, $history, Message $message): ?string
    {
        try {
            $model = $config->model ?: 'gpt-4o-mini';
            $url = 'https://api.openai.com/v1/chat/completions';

            $messages = [
                [
                    'role' => 'system',
                    'content' => "{$systemPrompt}\n\n[Business Context]:\n{$knowledgeBase}"
                ]
            ];

            foreach ($history as $hMsg) {
                $role = ($hMsg->sender_type === 'contact') ? 'user' : 'assistant';
                $messages[] = [
                    'role' => $role,
                    'content' => $hMsg->content
                ];
            }

            $response = Http::withToken($config->api_key)->timeout(10)->post($url, [
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => 250,
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::warning('OpenAI API call failed: ' . $response->body());
        } catch (\Throwable $e) {
            Log::error('OpenAI API Exception: ' . $e->getMessage());
        }

        return $this->generateSmartLocalReply($message, $knowledgeBase);
    }

    /**
     * Smart built-in auto-responder when external LLM API key is not yet configured.
     */
    protected function generateSmartLocalReply(Message $message, string $knowledgeBase): string
    {
        $text = strtolower($message->content);

        if (str_contains($text, 'price') || str_contains($text, 'dam') || str_contains($text, 'cost')) {
            return "Hello! Thank you for your inquiry about our pricing. Our pricing depends on your selected package and monthly message volume. Let us know which features you need, or a sales specialist can connect with you directly.";
        }

        if (str_contains($text, 'hello') || str_contains($text, 'hi') || str_contains($text, 'salam') || str_contains($text, 'hey')) {
            return "Hello! 👋 Thanks for reaching out to us. How can we help you today?";
        }

        if (str_contains($text, 'location') || str_contains($text, 'address') || str_contains($text, 'thikana')) {
            return "Thank you for asking! We provide services online and our support desk is active 24/7. Please let us know how we can assist you.";
        }

        return "Thank you for your message! 🤖 Our team has received your query. If you'd like to speak directly with an agent at any point, simply type 'human' or 'support'.";
    }
}
