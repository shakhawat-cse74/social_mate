<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Conversation;
use App\Models\CrmActivity;
use App\Models\CrmCustomer;
use App\Models\CrmDeal;
use App\Models\CrmLead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ErpCrmController extends Controller
{
    protected function getTenant()
    {
        $tenant = Auth::user()->tenant;
        if (!$tenant) abort(403, 'Tenant required');
        return $tenant;
    }

    // ==========================================
    // 0. UNIFIED CRM HUB (Single Page w/ Tabs)
    // ==========================================
    public function hub(Request $request)
    {
        $tenant = $this->getTenant();

        // — Leads —
        $leads = CrmLead::where('tenant_id', $tenant->id)
            ->with(['assignedUser', 'channel', 'contact'])
            ->latest()
            ->get();

        // — Customers —
        $customers = CrmCustomer::where('tenant_id', $tenant->id)
            ->with(['assignedUser', 'deals'])
            ->latest()
            ->get();

        // — Deals —
        $deals = CrmDeal::where('tenant_id', $tenant->id)
            ->with(['customer', 'assignedUser'])
            ->latest()
            ->get();

        // — Activities —
        $activities = CrmActivity::where('tenant_id', $tenant->id)
            ->with(['customer', 'deal', 'lead', 'user'])
            ->latest()
            ->get();

        // — Analytics KPIs —
        $totalLeads = $leads->count();
        $convertedLeads = $leads->where('status', 'converted')->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;
        $leadsBySource = $leads->groupBy('source')->map->count()->toArray();

        $totalPipelineValue = $deals->whereNotIn('stage', ['lost'])->sum('amount');
        $wonValue = $deals->where('stage', 'won')->sum('amount');
        $wonDealsCount = $deals->where('stage', 'won')->count();
        $lostDealsCount = $deals->where('stage', 'lost')->count();
        $closedDealsCount = $wonDealsCount + $lostDealsCount;
        $winRate = $closedDealsCount > 0 ? round(($wonDealsCount / $closedDealsCount) * 100, 1) : 0;
        $avgDealSize = $wonDealsCount > 0 ? round($wonValue / $wonDealsCount, 2) : 0;

        $dealsByStage = [
            'qualification' => ['count' => $deals->where('stage', 'qualification')->count(), 'amount' => $deals->where('stage', 'qualification')->sum('amount')],
            'proposal'      => ['count' => $deals->where('stage', 'proposal')->count(),      'amount' => $deals->where('stage', 'proposal')->sum('amount')],
            'negotiation'   => ['count' => $deals->where('stage', 'negotiation')->count(),   'amount' => $deals->where('stage', 'negotiation')->sum('amount')],
            'won'           => ['count' => $wonDealsCount,  'amount' => $wonValue],
            'lost'          => ['count' => $lostDealsCount, 'amount' => $deals->where('stage', 'lost')->sum('amount')],
        ];

        $agents = User::where('tenant_id', $tenant->id)->get();
        $agentStats = $agents->map(function ($agent) use ($tenant) {
            $wonDeals = CrmDeal::where('tenant_id', $tenant->id)->where('assigned_user_id', $agent->id)->where('stage', 'won')->get();
            return [
                'user'           => $agent,
                'leads_count'    => CrmLead::where('tenant_id', $tenant->id)->where('assigned_user_id', $agent->id)->count(),
                'deals_count'    => CrmDeal::where('tenant_id', $tenant->id)->where('assigned_user_id', $agent->id)->count(),
                'won_count'      => $wonDeals->count(),
                'revenue_closed' => $wonDeals->sum('amount'),
                'activities_done'=> CrmActivity::where('tenant_id', $tenant->id)->where('user_id', $agent->id)->where('status', 'completed')->count(),
            ];
        });

        $activitiesPending   = $activities->where('status', 'pending')->count();
        $activitiesCompleted = $activities->where('status', 'completed')->count();

        $activeTab = $request->get('tab', 'overview');

        return view('tenant.crm.hub', compact(
            'tenant', 'activeTab',
            'leads', 'customers', 'deals', 'activities', 'agents',
            'totalLeads', 'convertedLeads', 'conversionRate', 'leadsBySource',
            'totalPipelineValue', 'wonValue', 'winRate', 'avgDealSize',
            'dealsByStage', 'agentStats', 'activitiesPending', 'activitiesCompleted'
        ));
    }


    public function leads(Request $request)
    {
        $tenant = $this->getTenant();

        $query = CrmLead::where('tenant_id', $tenant->id)->with(['assignedUser', 'channel', 'contact']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('company_name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $leads = $query->latest()->paginate(15);
        $agents = User::where('tenant_id', $tenant->id)->get();

        return view('tenant.crm.leads', compact('tenant', 'leads', 'agents'));
    }

    public function storeLead(Request $request)
    {
        $tenant = $this->getTenant();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'source' => 'required|string',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'estimated_budget' => 'nullable|numeric|min:0',
            'assigned_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $lead = CrmLead::create([
            'tenant_id' => $tenant->id,
            'lead_code' => 'LEAD-' . date('Y') . '-' . rand(100, 999),
            'title' => $validated['title'],
            'first_name' => $validated['first_name'] ?? null,
            'last_name' => $validated['last_name'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'source' => $validated['source'],
            'priority' => $validated['priority'],
            'status' => 'new',
            'estimated_budget' => $validated['estimated_budget'] ?? 0,
            'assigned_user_id' => $validated['assigned_user_id'] ?? Auth::id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "ERP Lead {$lead->lead_code} created successfully!");
    }

    public function convertLead(Request $request, CrmLead $lead)
    {
        if ($lead->tenant_id !== Auth::user()->tenant_id) abort(403);

        $validated = $request->validate([
            'deal_title' => 'nullable|string',
            'amount' => 'nullable|numeric',
        ]);

        $result = $lead->convertToCustomerAndDeal(
            dealTitle: $validated['deal_title'] ?? null,
            amount: $validated['amount'] ?? null
        );

        return redirect()->route('tenant.crm.customers.show', $result['customer']->id)
            ->with('success', "Lead converted into Customer Account: {$result['customer']->name}");
    }

    // ==========================================
    // 2. CUSTOMERS / ACCOUNTS (360 Degree View)
    // ==========================================
    public function customers(Request $request)
    {
        $tenant = $this->getTenant();

        $query = CrmCustomer::where('tenant_id', $tenant->id)->withCount(['deals', 'activities']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('customer_code', 'like', "%{$s}%");
            });
        }

        $customers = $query->latest()->paginate(15);

        return view('tenant.crm.customers', compact('tenant', 'customers'));
    }

    public function storeCustomer(Request $request)
    {
        $tenant = $this->getTenant();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:company,individual',
            'contact_person' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'website' => 'nullable|string',
            'tax_id' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $customer = CrmCustomer::create([
            'tenant_id' => $tenant->id,
            'customer_code' => 'CUST-' . strtoupper(Str::random(5)),
            'type' => $validated['type'],
            'name' => $validated['name'],
            'contact_person' => $validated['contact_person'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'website' => $validated['website'] ?? null,
            'tax_id' => $validated['tax_id'] ?? null,
            'billing_address' => $validated['billing_address'] ?? null,
            'shipping_address' => $validated['shipping_address'] ?? null,
            'payment_terms' => $validated['payment_terms'] ?? 'Net 30',
            'credit_limit' => $validated['credit_limit'] ?? 0,
            'status' => 'active',
            'assigned_user_id' => Auth::id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Customer Account {$customer->customer_code} created!");
    }

    public function showCustomer(CrmCustomer $customer)
    {
        if ($customer->tenant_id !== Auth::user()->tenant_id) abort(403);

        $customer->load(['deals', 'activities.user', 'leads']);

        // Check if there are social media conversations linked to this customer's email or phone
        $linkedConversations = Conversation::whereHas('contact', function ($q) use ($customer) {
            $q->where(function ($sub) use ($customer) {
                if ($customer->email) $sub->where('email', $customer->email);
                if ($customer->phone) $sub->orWhere('phone', $customer->phone);
            });
        })->with(['channel', 'latestMessage'])->get();

        $tenant = $this->getTenant();
        $agents = User::where('tenant_id', $tenant->id)->get();

        return view('tenant.crm.customer-show', compact('tenant', 'customer', 'linkedConversations', 'agents'));
    }

    // ==========================================
    // 3. DEALS & QUOTATIONS (ERP Pipeline)
    // ==========================================
    public function deals(Request $request)
    {
        $tenant = $this->getTenant();

        $deals = CrmDeal::where('tenant_id', $tenant->id)
            ->with(['customer', 'assignedUser'])
            ->latest()
            ->get();

        $customers = CrmCustomer::where('tenant_id', $tenant->id)->get();
        $agents = User::where('tenant_id', $tenant->id)->get();

        $totalPipelineValue = $deals->whereNotIn('stage', ['lost'])->sum('amount');
        $wonValue = $deals->where('stage', 'won')->sum('amount');

        return view('tenant.crm.deals', compact('tenant', 'deals', 'customers', 'agents', 'totalPipelineValue', 'wonValue'));
    }

    public function storeDeal(Request $request)
    {
        $tenant = $this->getTenant();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'customer_id' => 'required|exists:crm_customers,id',
            'stage' => 'required|string|in:qualification,proposal,negotiation,won,lost',
            'amount' => 'required|numeric|min:0',
            'probability' => 'required|integer|between:0,100',
            'expected_close_date' => 'nullable|date',
            'assigned_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $deal = CrmDeal::create([
            'tenant_id' => $tenant->id,
            'deal_code' => 'DEAL-' . rand(1000, 9999),
            'title' => $validated['title'],
            'customer_id' => $validated['customer_id'],
            'stage' => $validated['stage'],
            'amount' => $validated['amount'],
            'probability' => $validated['probability'],
            'expected_close_date' => $validated['expected_close_date'] ?? now()->addMonth(),
            'assigned_user_id' => $validated['assigned_user_id'] ?? Auth::id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Deal {$deal->deal_code} created!");
    }

    public function updateDealStage(Request $request, CrmDeal $deal)
    {
        if ($deal->tenant_id !== Auth::user()->tenant_id) abort(403);

        $validated = $request->validate([
            'stage' => 'required|string|in:qualification,proposal,negotiation,won,lost',
            'lost_reason' => 'nullable|string',
        ]);

        $deal->update([
            'stage' => $validated['stage'],
            'lost_reason' => $validated['lost_reason'] ?? null,
        ]);

        // If won, update customer total revenue
        if ($validated['stage'] === 'won' && $deal->customer) {
            $deal->customer->increment('total_revenue', $deal->amount);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Deal stage updated!');
    }

    // ==========================================
    // 4. SALES ACTIVITIES & TASKS
    // ==========================================
    public function activities(Request $request)
    {
        $tenant = $this->getTenant();

        $query = CrmActivity::where('tenant_id', $tenant->id)->with(['customer', 'deal', 'lead', 'user']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $activities = $query->orderBy('due_date', 'asc')->paginate(20);
        $customers = CrmCustomer::where('tenant_id', $tenant->id)->get();
        $deals = CrmDeal::where('tenant_id', $tenant->id)->get();
        $agents = User::where('tenant_id', $tenant->id)->get();

        return view('tenant.crm.activities', compact('tenant', 'activities', 'customers', 'deals', 'agents'));
    }

    public function storeActivity(Request $request)
    {
        $tenant = $this->getTenant();

        $validated = $request->validate([
            'type' => 'required|string|in:call,meeting,task,follow_up,quotation_sent',
            'subject' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:crm_customers,id',
            'deal_id' => 'nullable|exists:crm_deals,id',
            'lead_id' => 'nullable|exists:crm_leads,id',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $activity = CrmActivity::create([
            'tenant_id' => $tenant->id,
            'type' => $validated['type'],
            'subject' => $validated['subject'],
            'customer_id' => $validated['customer_id'] ?? null,
            'deal_id' => $validated['deal_id'] ?? null,
            'lead_id' => $validated['lead_id'] ?? null,
            'due_date' => $validated['due_date'],
            'description' => $validated['description'] ?? null,
            'user_id' => $validated['user_id'] ?? Auth::id(),
            'status' => 'pending',
        ]);

        return back()->with('success', "Sales Activity '{$activity->subject}' scheduled successfully!");
    }

    public function completeActivity(Request $request, CrmActivity $activity)
    {
        if ($activity->tenant_id !== Auth::user()->tenant_id) abort(403);

        $activity->update([
            'status' => 'completed',
            'completed_at' => now(),
            'outcome_notes' => $request->outcome_notes,
        ]);

        return back()->with('success', "Activity marked as completed!");
    }

    // ==========================================
    // 5. 1-CLICK SOCIAL MESSAGE TO ERP LEAD
    // ==========================================
    public function convertSocialToLead(Request $request, Conversation $conversation)
    {
        $tenant = $this->getTenant();
        $contact = $conversation->contact;

        $lead = CrmLead::create([
            'tenant_id' => $tenant->id,
            'lead_code' => 'LEAD-' . date('Y') . '-' . rand(100, 999),
            'title' => "Inquiry from {$contact->name} ({$conversation->channel->platform})",
            'first_name' => $contact->name,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'source' => $conversation->channel->platform,
            'channel_id' => $conversation->channel_id,
            'contact_id' => $contact->id,
            'status' => 'qualified',
            'priority' => in_array('hot_lead', $contact->tags ?? []) ? 'high' : 'medium',
            'estimated_budget' => $contact->deal_value ?: 0,
            'assigned_user_id' => Auth::id(),
            'notes' => "Initiated from Social Chat. Latest message: {$conversation->latestMessage?->content}",
        ]);

        return response()->json([
            'success' => true,
            'message' => "Successfully created ERP Lead {$lead->lead_code}!",
            'lead' => $lead,
        ]);
    }

    // ==========================================
    // 6. REPORTING & ANALYTICS
    // ==========================================
    public function analytics(Request $request)
    {
        $tenant = $this->getTenant();

        // 1. Leads Metrics
        $totalLeads = CrmLead::where('tenant_id', $tenant->id)->count();
        $convertedLeads = CrmLead::where('tenant_id', $tenant->id)->where('status', 'converted')->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

        $leadsBySource = CrmLead::where('tenant_id', $tenant->id)
            ->selectRaw('source, count(*) as count')
            ->groupBy('source')
            ->pluck('count', 'source')
            ->toArray();

        // 2. Deals & Revenue Metrics
        $deals = CrmDeal::where('tenant_id', $tenant->id)->get();
        $totalPipelineValue = $deals->whereNotIn('stage', ['lost'])->sum('amount');
        $wonValue = $deals->where('stage', 'won')->sum('amount');
        $wonDealsCount = $deals->where('stage', 'won')->count();
        $lostDealsCount = $deals->where('stage', 'lost')->count();
        $closedDealsCount = $wonDealsCount + $lostDealsCount;
        $winRate = $closedDealsCount > 0 ? round(($wonDealsCount / $closedDealsCount) * 100, 1) : 0;
        $avgDealSize = $wonDealsCount > 0 ? round($wonValue / $wonDealsCount, 2) : 0;

        $dealsByStage = [
            'qualification' => ['count' => $deals->where('stage', 'qualification')->count(), 'amount' => $deals->where('stage', 'qualification')->sum('amount')],
            'proposal' => ['count' => $deals->where('stage', 'proposal')->count(), 'amount' => $deals->where('stage', 'proposal')->sum('amount')],
            'negotiation' => ['count' => $deals->where('stage', 'negotiation')->count(), 'amount' => $deals->where('stage', 'negotiation')->sum('amount')],
            'won' => ['count' => $wonDealsCount, 'amount' => $wonValue],
            'lost' => ['count' => $lostDealsCount, 'amount' => $deals->where('stage', 'lost')->sum('amount')],
        ];

        // 3. Agent Performance Leaderboard
        $agentStats = User::where('tenant_id', $tenant->id)->get()->map(function ($agent) use ($tenant) {
            $leadsCount = CrmLead::where('tenant_id', $tenant->id)->where('assigned_user_id', $agent->id)->count();
            $dealsCount = CrmDeal::where('tenant_id', $tenant->id)->where('assigned_user_id', $agent->id)->count();
            $wonDeals = CrmDeal::where('tenant_id', $tenant->id)->where('assigned_user_id', $agent->id)->where('stage', 'won')->get();
            $wonCount = $wonDeals->count();
            $revenueClosed = $wonDeals->sum('amount');
            $activitiesDone = CrmActivity::where('tenant_id', $tenant->id)->where('user_id', $agent->id)->where('status', 'completed')->count();

            return [
                'user' => $agent,
                'leads_count' => $leadsCount,
                'deals_count' => $dealsCount,
                'won_count' => $wonCount,
                'revenue_closed' => $revenueClosed,
                'activities_done' => $activitiesDone,
            ];
        });

        // 4. Activities Breakdown
        $activities = CrmActivity::where('tenant_id', $tenant->id)->get();
        $activitiesPending = $activities->where('status', 'pending')->count();
        $activitiesCompleted = $activities->where('status', 'completed')->count();

        return view('tenant.crm.analytics', compact(
            'tenant',
            'totalLeads',
            'convertedLeads',
            'conversionRate',
            'leadsBySource',
            'totalPipelineValue',
            'wonValue',
            'winRate',
            'avgDealSize',
            'dealsByStage',
            'agentStats',
            'activitiesPending',
            'activitiesCompleted'
        ));
    }
}
