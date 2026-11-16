<?php

use App\Http\Controllers\Api\AiConfigController;
use App\Http\Controllers\Api\ChannelController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\WebchatWidgetController;
use App\Http\Controllers\Api\WebhookEndpointController;
use App\Http\Controllers\SocialWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Social Media Incoming Webhooks (Meta, Telegram, WhatsApp)
|--------------------------------------------------------------------------
*/
Route::match(['get', 'post'], '/webhooks/meta/{channel}', [SocialWebhookController::class, 'handleMeta']);
Route::post('/webhooks/telegram/{channel}', [SocialWebhookController::class, 'handleTelegram']);
Route::match(['get', 'post'], '/webhooks/whatsapp/{channel}', [SocialWebhookController::class, 'handleWhatsApp']);
Route::match(['get', 'post'], '/webhooks/custom/{channel}', [SocialWebhookController::class, 'handleCustom']);
Route::match(['get', 'post'], '/webhooks/{platform}/{channel}', [SocialWebhookController::class, 'handleCustom']);


/*
|--------------------------------------------------------------------------
| Embeddable Web Chat Widget Endpoints (Any Website / External App)
|--------------------------------------------------------------------------
*/
Route::prefix('webchat')->group(function () {
    Route::post('/send', [WebchatWidgetController::class, 'sendMessage']);
    Route::get('/messages', [WebchatWidgetController::class, 'getMessages']);
});

/*
|--------------------------------------------------------------------------
| Core Hub & Add-on API Endpoints (CRM, Channels, Unified Inbox, AI)
|--------------------------------------------------------------------------
*/
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Conversations & Unified Inbox
Route::prefix('conversations')->group(function () {
    Route::get('/', [ConversationController::class, 'index']);
    Route::get('/{conversation}', [ConversationController::class, 'show']);
    Route::post('/{conversation}/reply', [ConversationController::class, 'reply']);
    Route::patch('/{conversation}/ai-mode', [ConversationController::class, 'toggleAi']);
    Route::patch('/{conversation}/status', [ConversationController::class, 'updateStatus']);
    Route::post('/{conversation}/call', [ConversationController::class, 'initiateCall']);
});

Route::post('/messages/{message}/react', [ConversationController::class, 'react']);

// CRM Contacts / Leads
Route::apiResource('contacts', ContactController::class);

// Social Media Channels
Route::apiResource('channels', ChannelController::class);

// AI Agent & Knowledge Base Configuration
Route::prefix('ai-config')->group(function () {
    Route::get('/', [AiConfigController::class, 'show']);
    Route::post('/', [AiConfigController::class, 'update']);
});

// Outbound Webhooks for external projects
Route::apiResource('webhook-endpoints', WebhookEndpointController::class);
