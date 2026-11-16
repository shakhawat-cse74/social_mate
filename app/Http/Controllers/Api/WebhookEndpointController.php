<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebhookEndpoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookEndpointController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(WebhookEndpoint::all());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'subscribed_events' => 'nullable|array',
        ]);

        $endpoint = WebhookEndpoint::create([
            'name' => $validated['name'],
            'url' => $validated['url'],
            'secret' => Str::random(32),
            'subscribed_events' => $validated['subscribed_events'] ?: ['*'],
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'External Webhook integration created',
            'endpoint' => $endpoint,
        ], 201);
    }

    public function destroy(WebhookEndpoint $endpoint): JsonResponse
    {
        $endpoint->delete();

        return response()->json(['message' => 'Endpoint deleted']);
    }
}
