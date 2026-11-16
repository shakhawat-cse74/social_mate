@extends('tenant.layout')

@section('title', 'AI Chatbot & Knowledge Base')

@section('content')
<div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 max-w-4xl mx-auto w-full space-y-6">
    <div>
        <h1 class="text-xl font-bold text-white tracking-tight">AI Chatbot & Business Knowledge Base</h1>
        <p class="text-xs text-slate-400">Train your intelligent assistant with your business FAQs, product catalogue, and warranty terms</p>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center gap-2">
            <span>✓</span> {{ session('success') }}
        </div>
    @endif

    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 space-y-6">
        <form action="{{ route('tenant.ai.update') }}" method="POST" class="space-y-5">
            @csrf

            <!-- AI Engine & Model Settings -->
            <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="text-xs font-bold text-white flex items-center gap-1.5">
                            <span>🧠</span> <span>AI Engine & Brain Setup</span>
                        </h3>
                        <p class="text-[11px] text-slate-400">Connect Google Gemini or OpenAI to empower your bot with real-time natural language answers.</p>
                    </div>
                    <span class="text-[10px] px-2 py-0.5 rounded font-bold {{ $aiConfig->api_key ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-400 border border-amber-500/30' }}">
                        {{ $aiConfig->api_key ? '● Live LLM Connected' : '○ Fallback Mode (No API Key)' }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">AI Engine Provider</label>
                        <select name="provider" id="ai-provider" onchange="handleProviderChange(this.value)" class="w-full bg-slate-900 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                            <option value="gemini" {{ ($aiConfig->provider ?? 'gemini') === 'gemini' ? 'selected' : '' }}>Google Gemini (Recommended - Free Tier)</option>
                            <option value="openai" {{ ($aiConfig->provider ?? '') === 'openai' ? 'selected' : '' }}>OpenAI (ChatGPT)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Model</label>
                        <input type="text" name="model" id="ai-model" value="{{ $aiConfig->model ?? 'gemini-1.5-flash' }}" placeholder="e.g. gemini-1.5-flash or gpt-4o-mini" class="w-full bg-slate-900 border border-slate-800 rounded-xl p-2.5 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-300">API Key <span class="text-rose-400">*</span></label>
                        <a href="https://aistudio.google.com/app/apikey" target="_blank" id="apikey-link" class="text-[11px] text-indigo-400 hover:text-indigo-300 underline flex items-center gap-1">
                            Get free Google Gemini API Key &nearr;
                        </a>
                    </div>
                    <div class="relative">
                        <input type="password" name="api_key" id="input-ai-key" value="{{ $aiConfig->api_key ?? '' }}" placeholder="AIzaSy... (Paste Gemini or OpenAI API Key)" class="w-full bg-slate-900 border border-slate-800 rounded-xl p-2.5 pr-10 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono">
                        <button type="button" onclick="toggleApiKeyVisibility()" class="absolute right-3 top-2.5 text-slate-400 hover:text-white text-xs" title="Show/Hide Key">👁️</button>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">API key is securely encrypted at rest. When connected, the AI will use this key and your Knowledge Base to dynamically answer any question from customers in Bangla or English.</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-2">Bot Persona & Instructions</label>
                <textarea name="system_prompt" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono">{{ $aiConfig->system_prompt ?? '' }}</textarea>
                <p class="text-[11px] text-slate-500 mt-1">Defines how the bot speaks (tone, company background, boundaries).</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-2">Business Knowledge Base (FAQs, Product Pricing, Terms)</label>
                <textarea name="knowledge_base" rows="8" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono">{{ $aiConfig->knowledge_base ?? '' }}</textarea>
                <p class="text-[11px] text-slate-500 mt-1">Whenever a customer messages on Facebook or WhatsApp, the bot references this text to provide instant answers.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-2">Human Handover Trigger Keywords (Comma separated)</label>
                <input type="text" name="handover_keywords" value="{{ implode(', ', $aiConfig->handover_keywords ?? ['human', 'agent', 'support', 'manush', 'kotha bolbo']) }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-white focus:outline-none focus:border-indigo-500">
                <p class="text-[11px] text-slate-500 mt-1">If the customer types any of these words, the AI automatically pauses and notifies your human team.</p>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-4 border-t border-slate-800">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="auto_reply_enabled" id="auto_reply_enabled" value="1" {{ ($aiConfig->auto_reply_enabled ?? true) ? 'checked' : '' }} class="rounded text-indigo-600 focus:ring-0 w-4 h-4 bg-slate-950 border-slate-800">
                    <label for="auto_reply_enabled" class="text-xs text-slate-300">Enable Automated AI Replies for new customer inquiries</label>
                </div>

                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 rounded-xl text-xs font-bold text-white shadow-lg shadow-indigo-600/30 transition-all text-center">
                    Save Knowledge Base
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function handleProviderChange(provider) {
        const modelInput = document.getElementById('ai-model');
        const link = document.getElementById('apikey-link');
        const keyInput = document.getElementById('input-ai-key');

        if (provider === 'gemini') {
            modelInput.value = 'gemini-1.5-flash';
            link.href = 'https://aistudio.google.com/app/apikey';
            link.innerHTML = 'Get free Google Gemini API Key &nearr;';
            keyInput.placeholder = 'AIzaSy... (Paste Gemini API Key)';
        } else {
            modelInput.value = 'gpt-4o-mini';
            link.href = 'https://platform.openai.com/api-keys';
            link.innerHTML = 'Get OpenAI API Key &nearr;';
            keyInput.placeholder = 'sk-... (Paste OpenAI API Key)';
        }
    }

    function toggleApiKeyVisibility() {
        const inp = document.getElementById('input-ai-key');
        inp.type = inp.type === 'password' ? 'text' : 'password';
    }
</script>
@endsection
