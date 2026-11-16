(function () {
    const scriptTag = document.currentScript || (function () {
        const scripts = document.getElementsByTagName('script');
        return scripts[scripts.length - 1];
    })();

    const baseUrl = scriptTag ? scriptTag.src.replace(/\/widget\.js.*$/, '') : window.location.origin;
    const channelId = scriptTag ? scriptTag.getAttribute('data-channel') : null;

    // Unique session ID stored in localStorage
    let sessionId = localStorage.getItem('omni_chat_session');
    if (!sessionId) {
        sessionId = 'web_' + Math.random().toString(36).substring(2, 12);
        localStorage.setItem('omni_chat_session', sessionId);
    }

    // Styles for Widget
    const style = document.createElement('style');
    style.innerHTML = `
        .omni-widget-bubble {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.5), 0 8px 10px -6px rgba(79, 70, 229, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            cursor: pointer;
            z-index: 999999;
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .omni-widget-bubble:hover {
            transform: scale(1.08);
        }
        .omni-widget-box {
            position: fixed;
            bottom: 96px;
            right: 24px;
            width: 380px;
            max-width: calc(100vw - 48px);
            height: 560px;
            max-height: calc(100vh - 120px);
            background: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            display: none;
            flex-direction: column;
            overflow: hidden;
            z-index: 999999;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .omni-widget-box.open {
            display: flex;
        }
        .omni-widget-header {
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            padding: 16px 20px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .omni-widget-messages {
            flex: 1;
            padding: 16px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
            background: #090d16;
        }
        .omni-msg {
            max-width: 80%;
            padding: 10px 14px;
            border-radius: 14px;
            font-size: 14px;
            line-height: 1.45;
            word-break: break-word;
        }
        .omni-msg-inbound {
            align-self: flex-end;
            background: #4f46e5;
            color: #ffffff;
            border-bottom-right-radius: 4px;
        }
        .omni-msg-outbound {
            align-self: flex-start;
            background: #1e293b;
            color: #f1f5f9;
            border-bottom-left-radius: 4px;
            border: 1px solid rgba(255,255,255,0.08);
        }
        .omni-widget-input-bar {
            padding: 12px;
            background: #0f172a;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            gap: 8px;
        }
        .omni-widget-input {
            flex: 1;
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 10px;
            color: white;
            padding: 10px 14px;
            font-size: 14px;
            outline: none;
        }
        .omni-widget-send-btn {
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0 16px;
            font-weight: 600;
            cursor: pointer;
        }
    `;
    document.head.appendChild(style);

    // Create DOM Elements
    const bubble = document.createElement('div');
    bubble.className = 'omni-widget-bubble';
    bubble.innerHTML = `<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 21 1.9-5.7a8.5 8.5 0 1 1 3.8 3.8z"/></svg>`;

    const box = document.createElement('div');
    box.className = 'omni-widget-box';
    box.innerHTML = `
        <div class="omni-widget-header">
            <div>
                <h4 style="margin:0; font-size:15px; font-weight:600;">Customer Support</h4>
                <span style="font-size:12px; opacity:0.85;">⚡ Powered by Omni AI Agent</span>
            </div>
            <button id="omni-close-btn" style="background:none; border:none; color:white; font-size:20px; cursor:pointer;">&times;</button>
        </div>
        <div class="omni-widget-messages" id="omni-messages-container">
            <div class="omni-msg omni-msg-outbound">
                👋 Hello! How can we help you today? Ask about our plans, pricing, or request a human agent anytime!
            </div>
        </div>
        <form class="omni-widget-input-bar" id="omni-form">
            <input type="text" class="omni-widget-input" id="omni-input" placeholder="Type your message..." autocomplete="off" />
            <button type="submit" class="omni-widget-send-btn">Send</button>
        </form>
    `;

    document.body.appendChild(bubble);
    document.body.appendChild(box);

    bubble.addEventListener('click', () => {
        box.classList.toggle('open');
        if (box.classList.contains('open')) {
            loadMessages();
        }
    });

    document.getElementById('omni-close-btn').addEventListener('click', () => {
        box.classList.remove('open');
    });

    const messagesContainer = document.getElementById('omni-messages-container');
    const form = document.getElementById('omni-form');
    const input = document.getElementById('omni-input');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;

        // Append locally
        appendMessage(text, 'omni-msg-inbound');
        input.value = '';

        try {
            await fetch(`${baseUrl}/api/webchat/send`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    session_id: sessionId,
                    content: text,
                    channel_id: channelId,
                }),
            });
            setTimeout(loadMessages, 1000);
        } catch (err) {
            console.error('Failed to send message', err);
        }
    });

    async function loadMessages() {
        try {
            const res = await fetch(`${baseUrl}/api/webchat/messages?session_id=${sessionId}`);
            const data = await res.json();
            if (data && data.messages && data.messages.length > 0) {
                messagesContainer.innerHTML = '';
                data.messages.forEach(msg => {
                    const cls = msg.direction === 'inbound' ? 'omni-msg-inbound' : 'omni-msg-outbound';
                    const prefix = msg.sender_type === 'ai_agent' ? '🤖 ' : (msg.sender_type === 'human_agent' ? '👤 ' : '');
                    appendMessage(prefix + msg.content, cls);
                });
            }
        } catch (err) {
            console.error('Error fetching messages', err);
        }
    }

    function appendMessage(text, className) {
        const div = document.createElement('div');
        div.className = `omni-msg ${className}`;
        div.innerText = text;
        messagesContainer.appendChild(div);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Auto poll every 4 seconds when chat open
    setInterval(() => {
        if (box.classList.contains('open')) {
            loadMessages();
        }
    }, 4000);
})();
