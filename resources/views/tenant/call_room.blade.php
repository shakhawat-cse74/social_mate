<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Live Call with Support | {{ $conversation?->channel?->name ?? 'Social Connector' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @keyframes ringPulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 35px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .ring-pulse-emerald { animation: ringPulse 1.8s infinite; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between overflow-hidden selection:bg-indigo-500 selection:text-white">

    <!-- Top Header -->
    <header class="p-4 flex items-center justify-between border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-md z-20">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-white shadow-lg shadow-indigo-500/30 text-base">
                {{ strtoupper(substr($channel?->name ?? 'S', 0, 1)) }}
            </div>
            <div>
                <h1 class="text-sm font-bold text-white flex items-center gap-1.5">
                    <span>{{ $channel?->name ?? 'Social Media Support' }}</span>
                    <span class="inline-flex items-center text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Verified</span>
                </h1>
                <p class="text-[11px] text-slate-400">Secure Omnichannel Voice & Video Call</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span id="call-badge-type" class="text-xs px-2.5 py-1 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 font-semibold flex items-center gap-1.5">
                {{ $callType === 'video' ? '📹 Video Call' : '📞 Audio Call' }}
            </span>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 flex flex-col items-center justify-center p-4 relative overflow-hidden">

        <!-- Background Glow Orbs -->
        <div class="absolute -top-32 -left-32 w-80 h-80 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-80 h-80 bg-emerald-600/20 rounded-full blur-3xl pointer-events-none"></div>

        <!-- STATE 1: INCOMING CALL RINGING SCREEN (FOR CUSTOMER) -->
        <div id="screen-incoming" class="flex flex-col items-center text-center max-w-sm w-full z-10 {{ $role === 'agent' ? 'hidden' : '' }}">
            <!-- Pulsing Avatar -->
            <div class="relative my-8">
                <div class="w-32 h-32 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-700 flex items-center justify-center text-white text-5xl font-black shadow-2xl border-4 border-slate-800 ring-pulse-emerald">
                    {{ strtoupper(substr($channel?->name ?? 'S', 0, 1)) }}
                </div>
                <div class="absolute -bottom-1 -right-1 w-9 h-9 rounded-full bg-emerald-500 border-4 border-slate-950 flex items-center justify-center text-white text-sm shadow-md">
                    {{ $callType === 'video' ? '📹' : '📞' }}
                </div>
            </div>

            <h2 class="text-xl font-bold text-white mb-1">{{ $channel?->name ?? 'Support Agent' }}</h2>
            <p id="ringing-status-label" class="text-xs font-semibold text-emerald-400 mb-6 flex items-center justify-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>Incoming {{ $callType === 'video' ? 'Video' : 'Audio' }} Call...</span>
            </p>

            <p class="text-xs text-slate-400 mb-10 px-4 leading-relaxed">
                Support agent is calling you regarding your inquiry. Tap <span class="text-emerald-400 font-semibold">Answer</span> to connect directly.
            </p>

            <!-- Call Answer & Decline Action Buttons -->
            <div class="flex items-center justify-center gap-8 w-full">
                <!-- Decline Button -->
                <button type="button" onclick="declineCall()" class="flex flex-col items-center gap-2 group">
                    <div class="w-16 h-16 rounded-full bg-rose-600 hover:bg-rose-500 text-white flex items-center justify-center text-2xl shadow-xl shadow-rose-600/40 transition-transform active:scale-95 group-hover:scale-105">
                        📞
                    </div>
                    <span class="text-xs font-semibold text-slate-400 group-hover:text-rose-400">Decline</span>
                </button>

                <!-- Accept Call Button -->
                <button type="button" onclick="acceptCall()" class="flex flex-col items-center gap-2 group">
                    <div class="w-16 h-16 rounded-full bg-emerald-500 hover:bg-emerald-400 text-white flex items-center justify-center text-2xl shadow-xl shadow-emerald-500/40 transition-transform active:scale-95 group-hover:scale-105">
                        <span class="animate-bounce">📞</span>
                    </div>
                    <span class="text-xs font-bold text-emerald-400 group-hover:text-emerald-300">Answer</span>
                </button>
            </div>
        </div>

        <!-- STATE 2: ACTIVE CALL LIVE SCREEN -->
        <div id="screen-active" class="w-full max-w-4xl h-full flex flex-col justify-between z-10 {{ $role === 'agent' ? '' : 'hidden' }}">
            
            <!-- Live Status & Call Timer -->
            <div class="flex items-center justify-between bg-slate-900/80 border border-slate-800 rounded-2xl px-4 py-2.5 mb-3 backdrop-blur-md">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-xs font-bold text-white">Call Connected</span>
                    <span class="text-[10px] text-slate-400 px-2 py-0.5 rounded bg-slate-800">HD Encrypted</span>
                </div>
                <div class="flex items-center gap-1.5 font-mono text-xs font-bold text-emerald-400 bg-emerald-500/10 px-3 py-1 rounded-xl border border-emerald-500/20" id="live-call-timer">
                    00:00
                </div>
            </div>

            <!-- Video / Audio Stream Container -->
            <div class="flex-1 w-full rounded-3xl overflow-hidden bg-slate-900 border border-slate-800 relative shadow-2xl min-h-[320px] flex items-center justify-center">
                <!-- Embedded Call Room Iframe -->
                <iframe id="call-iframe" 
                        allow="camera; microphone; fullscreen; display-capture; autoplay" 
                        src="" 
                        class="w-full h-full border-0 absolute inset-0">
                </iframe>
            </div>

            <!-- Floating Control Bar -->
            <div class="mt-4 flex items-center justify-center gap-4 py-3 bg-slate-900/90 border border-slate-800 rounded-2xl backdrop-blur-xl">
                <button type="button" onclick="toggleMute()" id="btn-user-mute" class="w-12 h-12 rounded-2xl bg-slate-800 hover:bg-slate-700 text-white text-lg flex items-center justify-center shadow-lg transition-transform active:scale-95" title="Mute/Unmute Mic">
                    🎙️
                </button>
                <button type="button" onclick="toggleCam()" id="btn-user-cam" class="w-12 h-12 rounded-2xl bg-slate-800 hover:bg-slate-700 text-white text-lg flex items-center justify-center shadow-lg transition-transform active:scale-95" title="Camera On/Off">
                    📹
                </button>
                <button type="button" onclick="hangUpCall()" class="px-6 h-12 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-sm shadow-xl shadow-rose-600/30 flex items-center gap-2 transition-transform active:scale-95">
                    <span>📞</span>
                    <span>End Call</span>
                </button>
            </div>
        </div>

        <!-- STATE 3: CALL ENDED SCREEN -->
        <div id="screen-ended" class="hidden flex flex-col items-center text-center max-w-sm w-full z-10 animate-in fade-in zoom-in-95 duration-200">
            <div class="w-20 h-20 rounded-full bg-slate-800/80 border border-slate-700 flex items-center justify-center text-3xl mb-4">
                👋
            </div>
            <h3 class="text-lg font-bold text-white mb-1">Call Ended</h3>
            <p class="text-xs text-slate-400 mb-6" id="ended-duration-text">Thank you for talking with us.</p>
            <button onclick="window.close()" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold transition-colors">
                Close Window
            </button>
        </div>

    </main>

    <!-- Footer -->
    <footer class="p-3 text-center text-[10px] text-slate-500 border-t border-slate-900 bg-slate-950/80">
        Secured by {{ config('app.name', 'Social Connector') }} • End-to-End Encrypted WebRTC Call
    </footer>

    <!-- Audio Synthesizer for Telephone Ringtone -->
    <script>
        const roomName = "{{ $room }}";
        const userRole = "{{ $role }}";
        const callType = "{{ $callType }}";

        let audioCtx = null;
        let ringInterval = null;
        let isRinging = false;
        let callTimerInterval = null;
        let callSeconds = 0;

        // Realistic telephone ringing tone generator
        function startRingtoneSound() {
            try {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                isRinging = true;

                function playBeepTone() {
                    if (!isRinging || !audioCtx) return;
                    const osc1 = audioCtx.createOscillator();
                    const osc2 = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();

                    osc1.type = 'sine';
                    osc1.frequency.value = 440; // US standard ring frequency 440Hz + 480Hz
                    osc2.type = 'sine';
                    osc2.frequency.value = 480;

                    gain.gain.setValueAtTime(0.08, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 1.6);

                    osc1.connect(gain);
                    osc2.connect(gain);
                    gain.connect(audioCtx.destination);

                    osc1.start();
                    osc2.start();
                    osc1.stop(audioCtx.currentTime + 1.6);
                    osc2.stop(audioCtx.currentTime + 1.6);
                }

                playBeepTone();
                ringInterval = setInterval(playBeepTone, 3000);
            } catch (e) {
                console.warn('Audio Context tone autoplay prevented:', e);
            }
        }

        function stopRingtoneSound() {
            isRinging = false;
            if (ringInterval) clearInterval(ringInterval);
            if (audioCtx) {
                try { audioCtx.close(); } catch(e) {}
                audioCtx = null;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (userRole === 'customer') {
                // Try ringtone on load (or user interaction)
                document.body.addEventListener('click', () => {
                    if (isRinging && !audioCtx) startRingtoneSound();
                }, { once: true });
                startRingtoneSound();
            } else {
                // Agent directly starts call
                connectCall();
            }
        });

        function acceptCall() {
            stopRingtoneSound();
            document.getElementById('screen-incoming').classList.add('hidden');
            document.getElementById('screen-active').classList.remove('hidden');
            connectCall();
        }

        function declineCall() {
            stopRingtoneSound();
            document.getElementById('screen-incoming').classList.add('hidden');
            document.getElementById('screen-ended').classList.remove('hidden');
            document.getElementById('ended-duration-text').innerText = 'You declined the incoming call.';
        }

        function connectCall() {
            const iframe = document.getElementById('call-iframe');
            // Connect to real-time WebRTC room (no login, no ads, straight HD audio/video)
            const jitsiConfig = callType === 'audio' 
                ? '#config.startWithVideoMuted=true&config.startWithAudioMuted=false' 
                : '#config.startWithVideoMuted=false&config.startWithAudioMuted=false';

            iframe.src = `https://meet.jit.si/${roomName}${jitsiConfig}`;

            // Start timer
            callSeconds = 0;
            clearInterval(callTimerInterval);
            callTimerInterval = setInterval(() => {
                callSeconds++;
                const mins = String(Math.floor(callSeconds / 60)).padStart(2, '0');
                const secs = String(callSeconds % 60).padStart(2, '0');
                document.getElementById('live-call-timer').innerText = `${mins}:${secs}`;
            }, 1000);
        }

        function hangUpCall() {
            clearInterval(callTimerInterval);
            const duration = document.getElementById('live-call-timer').innerText;
            document.getElementById('call-iframe').src = '';
            document.getElementById('screen-active').classList.add('hidden');
            document.getElementById('screen-ended').classList.remove('hidden');
            document.getElementById('ended-duration-text').innerText = `Call duration: ${duration}`;
        }

        function toggleMute() {
            const btn = document.getElementById('btn-user-mute');
            btn.classList.toggle('bg-rose-600');
        }

        function toggleCam() {
            const btn = document.getElementById('btn-user-cam');
            btn.classList.toggle('bg-rose-600');
        }
    </script>
</body>
</html>
