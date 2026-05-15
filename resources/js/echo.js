import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? 'https';
const reverbPort = Number(import.meta.env.VITE_REVERB_PORT) || (reverbScheme === 'https' ? 443 : 80);
let echoClient = null;

function getEchoClient () {
    if (echoClient) {
        return echoClient;
    }

    echoClient = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: reverbPort,
        wssPort: reverbPort,
        forceTLS: reverbScheme === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
                Accept: 'application/json',
            },
        },
    });

    window.Echo = echoClient;

    return echoClient;
}

// ── Avatar color palette (must match blade) ──
const avatarPalette = ['#e9d5ff','#dbeafe','#d1fae5','#fef9c3','#fee2e2','#e0f2fe','#fce7f3'];

function getAvatarColor (name) {
    const initial = (name || 'U')[0].toUpperCase();
    const idx = (initial.charCodeAt(0) - 65 + avatarPalette.length) % avatarPalette.length;
    return avatarPalette[idx];
}

// ── Auto-resize a textarea to its content ──
function autoResize (textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = Math.min(textarea.scrollHeight, 130) + 'px';
}

function setupChatCompose () {
    document.querySelectorAll('form[data-office-chat-ajax]').forEach((form) => {
        const textarea = form.querySelector('.chat-compose-input, [name="body"]');
        if (! textarea) return;

        // Auto-resize on input
        textarea.addEventListener('input', () => autoResize(textarea));

        // Enter to submit; Shift+Enter for newline
        textarea.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            }
        });
    });
}

function setupOfficeChat () {
    const scroll = document.getElementById('live-chat-scroll');
    if (! scroll) {
        return;
    }

    const officeId      = scroll.dataset.officeId;
    const citizenUserId = scroll.dataset.citizenUserId;
    const portal        = scroll.dataset.chatPortal || 'office';
    const currentUserId = scroll.dataset.currentUserId;

    const echo = getEchoClient();

    window.appendLiveChatMessage = function (payload) {
        appendIncomingMessage(payload, portal, currentUserId);
        scroll.scrollTop = scroll.scrollHeight;
    };

    echo.private(`office-chat.${officeId}.${citizenUserId}`)
        .listen('.message.sent', (payload) => {
            appendIncomingMessage(payload, portal, currentUserId);
            scroll.scrollTop = scroll.scrollHeight;
        });

    document.querySelectorAll('form[data-office-chat-ajax]').forEach((form) => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const errEl    = document.getElementById('live-chat-ajax-error');
            const submitBtn = form.querySelector('[type="submit"]');
            const bodyInput = form.querySelector('[name="body"]');

            if (errEl) {
                errEl.classList.add('d-none');
                errEl.textContent = '';
            }
            if (submitBtn) submitBtn.disabled = true;

            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
                    },
                    body: new FormData(form),
                });

                const data = await res.json().catch(() => ({}));

                if (! res.ok) {
                    const msg = data.message
                        ?? (data.errors?.body?.[0])
                        ?? `Could not send (${res.status}).`;
                    if (errEl) {
                        errEl.textContent = typeof msg === 'string' ? msg : 'Could not send message.';
                        errEl.classList.remove('d-none');
                    }
                    return;
                }

                if (data.message && window.appendLiveChatMessage) {
                    window.appendLiveChatMessage(data.message);
                }
                if (bodyInput) {
                    bodyInput.value = '';
                    autoResize(bodyInput);
                }
            } catch {
                if (errEl) {
                    errEl.textContent = 'Network error. Please try again.';
                    errEl.classList.remove('d-none');
                }
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    });
}

function setupAppointmentLiveUpdates () {
    document.querySelectorAll('[data-appointments-live][data-office-id]').forEach((root) => {
        const officeId = root.dataset.officeId;
        if (!officeId) {
            return;
        }

        const echo = getEchoClient();
        echo.private(`appointments.office.${officeId}`)
            .listen('.appointments.updated', (payload) => {
                window.dispatchEvent(new CustomEvent('appointments-updated', {
                    detail: payload,
                }));
            });
    });
}

function setupEchoClient() {
    // Always initialize Echo so window.Echo is available for notification subscriptions
    // on every page, not just chat/appointments pages.
    getEchoClient();
    setupOfficeChat();
    setupChatCompose();
    setupAppointmentLiveUpdates();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupEchoClient);
} else {
    setupEchoClient();
}

/**
 * @param {{ id: number, sender_id: number, body: string, sender?: { name?: string }, created_at?: string }} payload
 */
function appendIncomingMessage (payload, portal, currentUserId) {
    const root = document.getElementById('live-chat-messages');
    if (! root || ! payload?.id) {
        return;
    }

    if (root.querySelector(`[data-message-id="${payload.id}"]`)) {
        return;
    }

    document.getElementById('live-chat-empty')?.remove();

    const mine       = Number(payload.sender_id) === Number(currentUserId);
    const senderName = payload.sender?.name ?? 'User';

    // Bubble colour / style
    const mineBubbleOffice  = 'bg-success bg-opacity-10 border border-success border-opacity-25 text-dark';
    const mineBubbleCitizen = 'text-white border-0';
    const mineStyleCitizen  = 'background: linear-gradient(135deg, var(--accent-mid), var(--accent));';
    const theirBubble       = 'bg-white border';

    let bubbleClass = theirBubble;
    let extraBubbleStyle = '';
    if (mine) {
        if (portal === 'office') {
            bubbleClass = mineBubbleOffice;
        } else {
            bubbleClass = mineBubbleCitizen;
            extraBubbleStyle = mineStyleCitizen;
        }
    }

    // Timestamp
    let timeText = '';
    let timeISO  = '';
    if (payload.created_at) {
        timeISO = payload.created_at;
        try {
            timeText = new Date(payload.created_at).toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' });
        } catch {
            timeText = payload.created_at;
        }
    }

    // Row
    const row = document.createElement('div');
    row.className = `d-flex mb-2 chat-message-row align-items-end gap-2 ${mine ? 'justify-content-end' : 'justify-content-start'}`;
    row.dataset.messageId = String(payload.id);

    // Avatar (non-mine only)
    if (! mine) {
        const avatarDiv = document.createElement('div');
        avatarDiv.className = 'chat-avatar';
        avatarDiv.title = senderName;
        avatarDiv.style.cssText = `background:${getAvatarColor(senderName)};color:#374151;`;
        avatarDiv.textContent = senderName[0].toUpperCase();
        row.appendChild(avatarDiv);
    }

    // Bubble
    const bubble = document.createElement('div');
    bubble.className = `rounded-3 px-3 py-2 shadow-sm chat-message-bubble ${bubbleClass}`;
    let bubbleStyle = 'max-width: min(78%, 28rem);';
    if (mine && portal === 'citizen') bubbleStyle += extraBubbleStyle;
    bubble.setAttribute('style', bubbleStyle);

    // Body
    const bodyEl = document.createElement('div');
    bodyEl.className = 'small mb-0 chat-message-body';
    bodyEl.style.cssText = 'white-space: pre-wrap; line-height: 1.5;';
    bodyEl.textContent = payload.body ?? '';

    // Time
    const timeWrap = document.createElement('div');
    timeWrap.className = `chat-message-time ${mine ? 'text-end' : ''}`;
    if (mine && portal === 'citizen') {
        timeWrap.style.color = 'rgba(255,255,255,.75)';
    } else {
        timeWrap.classList.add('text-muted');
    }

    const timeEl = document.createElement('time');
    if (timeISO) timeEl.dateTime = timeISO;
    timeEl.textContent = timeText;

    timeWrap.appendChild(timeEl);

    if (mine) {
        const checkIcon = document.createElement('i');
        checkIcon.className = 'bi bi-check2 ms-1';
        checkIcon.style.fontSize = '.6rem';
        timeWrap.appendChild(checkIcon);
    }

    bubble.appendChild(bodyEl);
    bubble.appendChild(timeWrap);
    row.appendChild(bubble);
    root.appendChild(row);
}
