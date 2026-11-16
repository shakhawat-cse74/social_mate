<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiConfigController extends Controller
{
    public function show(): JsonResponse
    {
        $config = AiConfig::firstOrCreate([], [
            'name' => 'Default Smart Assistant',
            'provider' => 'gemini',
            'model' => 'gemini-1.5-flash',
            'system_prompt' => 'You are an intelligent customer support assistant. Answer politely and accurately.',
            'knowledge_base' => "Business Name: SocialCRM\nServices: Social Media Connector, Unified Inbox, Customer Relationship Management, Automated AI Support.\nRefund Policy: 7 days full refund if requested.\nContact: support@example.com",
            'auto_reply_enabled' => true,
            'handover_keywords' => ['human', 'agent', 'support', 'manush', 'kotha bolbo', 'help'],
        ]);

        return response()->json($config);
    }

    public function update(Request $request): JsonResponse
    {
        $config = AiConfig::firstOrCreate([]);

        $validated = $request->validate([
            'name' => 'nullable|string',
            'provider' => 'required|string|in:gemini,openai,mock',
            'model' => 'nullable|string',
            'api_key' => 'nullable|string',
            'system_prompt' => 'nullable|string',
            'knowledge_base' => 'nullable|string',
            'auto_reply_enabled' => 'boolean',
            'handover_keywords' => 'nullable|array',
        ]);

        if (empty($validated['api_key'])) {
            unset($validated['api_key']);
        }

        $config->update($validated);

        return response()->json([
            'message' => 'AI Configuration updated successfully',
            'config' => $config,
        ]);
    }
}
