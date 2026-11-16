<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    public function index(): JsonResponse
    {
        $channels = Channel::withCount('conversations')->get();
        return response()->json($channels);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'platform' => 'required|string|in:facebook,instagram,whatsapp,telegram,webchat',
            'platform_account_id' => 'nullable|string',
            'access_token' => 'nullable|string',
            'verify_token' => 'nullable|string',
            'webhook_secret' => 'nullable|string',
            'ai_enabled' => 'boolean',
            'settings' => 'nullable|array',
        ]);

        $channel = Channel::create($validated);

        return response()->json([
            'message' => 'Channel added successfully',
            'channel' => $channel,
        ], 201);
    }

    public function update(Request $request, Channel $channel): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'is_active' => 'sometimes|boolean',
            'ai_enabled' => 'sometimes|boolean',
            'access_token' => 'nullable|string',
            'settings' => 'nullable|array',
        ]);

        $channel->update($validated);

        return response()->json([
            'message' => 'Channel updated successfully',
            'channel' => $channel,
        ]);
    }

    public function destroy(Channel $channel): JsonResponse
    {
        $channel->delete();

        return response()->json(['message' => 'Channel deleted successfully']);
    }
}
