import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? 'https';
const reverbPort = Number(import.meta.env.VITE_REVERB_PORT) || (reverbScheme === 'https' ? 443 : 80);

function setupOfficeChat () {
    const scroll = document.getElementById('live-chat-scroll');
    if (! scroll) {
        return;
    }

    const officeId = scroll.dataset.officeId;
    const citizenUserId = scroll.dataset.citizenUserId;
    const portal = scroll.dataset.chatPortal || 'office';
    const currentUserId = scroll.dataset.currentUserId;

    window.Echo = new Echo({
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

    window.appendLiveChatMessage = function (payload) {
        appendIncomingMessage(payload, portal, currentUserId);
        scroll.scrollTop = scroll.scrollHeight;
    };

    window.Echo.private(`office-chat.${officeId}.${citizenUserId}`)
        .listen('.message.sent', (payload) => {
            appendIncomingMessage(payload, portal, currentUserId);
            scroll.scrollTop = scroll.scrollHeight;
        });

    document.querySelectorAll('form[data-office-chat-ajax]').forEach((form) => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const errEl = document.getElementById('live-chat-ajax-error');
            const submitBtn = form.querySelector('[type="submit"]');
            if (errEl) {
                errEl.classList.add('d-none');
                errEl.textContent = '';
            }

            const bodyInput = form.querySelector('[name="body"]');
            if (submitBtn) {
                submitBtn.disabled = true;
            }

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
                }
            } catch {
                if (errEl) {
                    errEl.textContent = 'Network error. Please try again.';
                    errEl.classList.remove('d-none');
                }
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                }
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupOfficeChat);
} else {
    setupOfficeChat();
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

    const mine = Number(payload.sender_id) === Number(currentUserId);
    const mineBubbleOffice = 'bg-success bg-opacity-10 border border-success border-opacity-25 text-dark';
    const mineBubbleCitizen = 'text-white border-0';
    const mineStyleCitizen = 'background: linear-gradient(135deg, var(--accent-mid), var(--accent));';
    const theirBubble = 'bg-white border';

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

    const row = document.createElement('div');
    row.className = `d-flex mb-3 chat-message-row ${mine ? 'justify-content-end' : 'justify-content-start'}`;
    row.dataset.messageId = String(payload.id);

    const bubble = document.createElement('div');
    bubble.className = `rounded-3 px-3 py-2 shadow-sm chat-message-bubble ${bubbleClass}`;
    let bubbleStyle = 'max-width: min(92%, 28rem);';
    if (mine && portal === 'citizen') {
        bubbleStyle += extraBubbleStyle;
    }
    bubble.setAttribute('style', bubbleStyle);

    const header = document.createElement('div');
    header.className = 'd-flex justify-content-between align-items-baseline gap-2 flex-wrap';

    const nameEl = document.createElement('span');
    nameEl.className = 'fw-semibold small chat-message-sender';
    nameEl.textContent = payload.sender?.name ?? 'User';

    const timeEl = document.createElement('time');
    timeEl.className = 'text-muted small chat-message-time';
    timeEl.style.fontSize = '0.7rem';
    if (payload.created_at) {
        timeEl.dateTime = payload.created_at;
        try {
            timeEl.textContent = new Date(payload.created_at).toLocaleString(undefined, {
                dateStyle: 'medium',
                timeStyle: 'short',
            });
        } catch {
            timeEl.textContent = payload.created_at;
        }
    }

    header.appendChild(nameEl);
    header.appendChild(timeEl);

    const bodyEl = document.createElement('div');
    bodyEl.className = 'small mt-1 mb-0 chat-message-body';
    bodyEl.style.whiteSpace = 'pre-wrap';
    bodyEl.textContent = payload.body ?? '';

    bubble.appendChild(header);
    bubble.appendChild(bodyEl);
    row.appendChild(bubble);
    root.appendChild(row);
}
