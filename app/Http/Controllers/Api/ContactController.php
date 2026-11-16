<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Contact::query()->withCount('conversations');

        if ($request->filled('lead_status')) {
            $query->where('lead_status', $request->lead_status);
        }

        if ($request->filled('pipeline_stage')) {
            $query->where('pipeline_stage', $request->pipeline_stage);
        }

        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $contacts = $query->orderBy('last_active_at', 'desc')->paginate($request->get('per_page', 25));

        return response()->json($contacts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'platform' => 'nullable|string|max:50',
            'platform_sender_id' => 'required|string|max:255',
            'pipeline_stage' => 'nullable|string',
            'deal_value' => 'nullable|numeric',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $contact = Contact::updateOrCreate(
            [
                'platform' => $validated['platform'] ?? 'webchat',
                'platform_sender_id' => $validated['platform_sender_id'],
            ],
            $validated
        );

        return response()->json([
            'message' => 'Contact saved successfully',
            'contact' => $contact,
        ], 201);
    }

    public function show(Contact $contact): JsonResponse
    {
        $contact->load(['conversations.channel', 'messages' => fn($q) => $q->latest()->take(20)]);

        return response()->json($contact);
    }

    public function update(Request $request, Contact $contact): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'lead_status' => 'sometimes|string',
            'pipeline_stage' => 'sometimes|string',
            'deal_value' => 'sometimes|numeric',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $contact->update($validated);

        return response()->json([
            'message' => 'Contact updated successfully',
            'contact' => $contact,
        ]);
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();

        return response()->json(['message' => 'Contact deleted successfully']);
    }
}
