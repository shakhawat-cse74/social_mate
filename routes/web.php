<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TenantPortalController;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Middleware\TenantMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->isSuperAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('tenant.dashboard');
    }
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Simulator (can be called from tenant inbox or API)
Route::post('/simulate-inbound', [DashboardController::class, 'simulateInbound'])->name('simulate.inbound');

// Universal Omnichannel In-App Call Screen (Telegram, WhatsApp, Messenger, Webchat)
Route::get('/call/{room}', [TenantPortalController::class, 'callRoom'])->name('tenant.call_room');

/*
|--------------------------------------------------------------------------
| Super Admin SaaS Management Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth', SuperAdminMiddleware::class])->group(function () {
    Route::get('/', [SuperAdminController::class, 'index'])->name('admin.dashboard');
    Route::post('/tenants', [SuperAdminController::class, 'storeTenant'])->name('admin.tenants.store');
    Route::put('/tenants/{tenant}', [SuperAdminController::class, 'updateTenant'])->name('admin.tenants.update');
    Route::delete('/tenants/{tenant}', [SuperAdminController::class, 'destroyTenant'])->name('admin.tenants.destroy');
    Route::post('/tenants/{tenant}/channels', [SuperAdminController::class, 'setupChannel'])->name('admin.tenants.channels');
    Route::post('/channels/{channel}/test', [SuperAdminController::class, 'testChannel'])->name('admin.channels.test');
    Route::delete('/channels/{channel}', [SuperAdminController::class, 'destroyChannel'])->name('admin.channels.destroy');

    // Impersonate customer
    Route::get('/impersonate/{user}', [AuthController::class, 'impersonate'])->name('admin.impersonate');
});

Route::get('/admin/stop-impersonating', [AuthController::class, 'stopImpersonation'])->name('admin.stop_impersonating');

/*
|--------------------------------------------------------------------------
| Subscribed Tenant / Customer Portal Routes (REVE Chat Style)
|--------------------------------------------------------------------------
*/
Route::prefix('tenant')->middleware(['auth', TenantMiddleware::class])->group(function () {
    Route::get('/dashboard', [TenantPortalController::class, 'dashboard'])->name('tenant.dashboard');
    Route::get('/inbox', [TenantPortalController::class, 'inbox'])->name('tenant.inbox');
    Route::post('/inbox/poll', [TenantPortalController::class, 'pollInbox'])->name('tenant.inbox.poll');
    Route::get('/contacts', [TenantPortalController::class, 'contacts'])->name('tenant.contacts');
    Route::get('/pipeline', [TenantPortalController::class, 'pipeline'])->name('tenant.pipeline');
    Route::get('/channels', [TenantPortalController::class, 'channels'])->name('tenant.channels');
    Route::post('/channels/connect', [TenantPortalController::class, 'connectChannel'])->name('tenant.channels.connect');
    Route::post('/channels/{channel}/test', [TenantPortalController::class, 'testChannel'])->name('tenant.channels.test');
    Route::post('/channels/{channel}/sync', [TenantPortalController::class, 'syncTelegram'])->name('tenant.channels.sync');
    Route::patch('/channels/{channel}/toggle-status', [TenantPortalController::class, 'toggleChannelStatus'])->name('tenant.channels.toggle_status');
    Route::patch('/channels/{channel}/toggle-ai', [TenantPortalController::class, 'toggleChannelAi'])->name('tenant.channels.toggle_ai');
    Route::delete('/channels/{channel}', [TenantPortalController::class, 'deleteChannel'])->name('tenant.channels.delete');
    Route::post('/platforms', [TenantPortalController::class, 'storePlatform'])->name('tenant.platforms.store');
    Route::delete('/platforms/{platform}', [TenantPortalController::class, 'destroyPlatform'])->name('tenant.platforms.destroy');
    Route::get('/ai-bot', [TenantPortalController::class, 'aiBot'])->name('tenant.ai');
    Route::post('/ai-bot', [TenantPortalController::class, 'updateAiBot'])->name('tenant.ai.update');
    Route::get('/agents', [TenantPortalController::class, 'agents'])->name('tenant.agents');
    Route::post('/agents', [TenantPortalController::class, 'storeAgent'])->name('tenant.agents.store');
    Route::get('/settings', [TenantPortalController::class, 'settings'])->name('tenant.settings');

    // Quick Replies (Canned Responses) CRUD
    Route::get('/quick-replies', [TenantPortalController::class, 'getQuickReplies'])->name('tenant.quick_replies.index');
    Route::post('/quick-replies', [TenantPortalController::class, 'storeQuickReply'])->name('tenant.quick_replies.store');
    Route::put('/quick-replies/{quickReply}', [TenantPortalController::class, 'updateQuickReply'])->name('tenant.quick_replies.update');
    Route::patch('/quick-replies/{quickReply}/toggle-status', [TenantPortalController::class, 'toggleQuickReplyStatus'])->name('tenant.quick_replies.toggle_status');
    Route::delete('/quick-replies/{quickReply}', [TenantPortalController::class, 'destroyQuickReply'])->name('tenant.quick_replies.destroy');

    // ERP CRM Suite
    Route::prefix('crm')->group(function () {
        // ── UNIFIED HUB (Primary Entry Point) ──
        Route::get('/', [\App\Http\Controllers\ErpCrmController::class, 'hub'])->name('tenant.crm.hub');

        Route::get('/leads', [\App\Http\Controllers\ErpCrmController::class, 'leads'])->name('tenant.crm.leads');
        Route::post('/leads', [\App\Http\Controllers\ErpCrmController::class, 'storeLead'])->name('tenant.crm.leads.store');
        Route::post('/leads/{lead}/convert', [\App\Http\Controllers\ErpCrmController::class, 'convertLead'])->name('tenant.crm.leads.convert');

        Route::get('/customers', [\App\Http\Controllers\ErpCrmController::class, 'customers'])->name('tenant.crm.customers');
        Route::post('/customers', [\App\Http\Controllers\ErpCrmController::class, 'storeCustomer'])->name('tenant.crm.customers.store');
        Route::get('/customers/{customer}', [\App\Http\Controllers\ErpCrmController::class, 'showCustomer'])->name('tenant.crm.customers.show');

        Route::get('/deals', [\App\Http\Controllers\ErpCrmController::class, 'deals'])->name('tenant.crm.deals');
        Route::post('/deals', [\App\Http\Controllers\ErpCrmController::class, 'storeDeal'])->name('tenant.crm.deals.store');
        Route::patch('/deals/{deal}/stage', [\App\Http\Controllers\ErpCrmController::class, 'updateDealStage'])->name('tenant.crm.deals.stage');

        Route::get('/activities', [\App\Http\Controllers\ErpCrmController::class, 'activities'])->name('tenant.crm.activities');
        Route::post('/activities', [\App\Http\Controllers\ErpCrmController::class, 'storeActivity'])->name('tenant.crm.activities.store');
        Route::post('/activities/{activity}/complete', [\App\Http\Controllers\ErpCrmController::class, 'completeActivity'])->name('tenant.crm.activities.complete');

        Route::get('/analytics', [\App\Http\Controllers\ErpCrmController::class, 'analytics'])->name('tenant.crm.analytics');

        Route::post('/convert-social/{conversation}', [\App\Http\Controllers\ErpCrmController::class, 'convertSocialToLead'])->name('tenant.crm.convert_social');
    });

});
