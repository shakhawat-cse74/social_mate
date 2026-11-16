<?php

namespace App\Services\Webhook;

use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookDispatcher
{
    /**
     * Dispatch an event payload to registered external project endpoints.
     */
    public function dispatch(string $event, array $payload): void
    {
        $endpoints = WebhookEndpoint::where('is_active', true)->get();

        foreach ($endpoints as $endpoint) {
            $subscribed = $endpoint->subscribed_events;

            // If empty or includes '*' or the specific event
            if (empty($subscribed) || in_array('*', $subscribed) || in_array($event, $subscribed)) {
                $this->sendToEndpoint($endpoint, $event, $payload);
            }
        }
    }

    protected function sendToEndpoint(WebhookEndpoint $endpoint, string $event, array $payload): void
    {
        $body = [
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'data' => $payload,
        ];

        $jsonBody = json_encode($body);
        $signature = $endpoint->secret
            ? 'sha256=' . hash_hmac('sha256', $jsonBody, $endpoint->secret)
            : null;

        try {
            $request = Http::timeout(5)->withHeaders([
                'Content-Type' => 'application/json',
                'X-Connector-Event' => $event,
                'X-Connector-Signature' => $signature,
            ]);

            $response = $request->post($endpoint->url, $body);

            if ($response->successful()) {
                $endpoint->update([
                    'last_dispatched_at' => now(),
                    'failure_count' => 0,
                ]);
            } else {
                $endpoint->increment('failure_count');
                Log::warning("Webhook failed to {$endpoint->url}: Status {$response->status()}");
            }
        } catch (\Throwable $e) {
            $endpoint->increment('failure_count');
            Log::error("Webhook error dispatching to {$endpoint->url}: " . $e->getMessage());
        }
    }
}
