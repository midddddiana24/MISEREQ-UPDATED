@php
$user = auth()->user();
$wvsuLogo = file_exists(public_path('images/wvsu-janiuay-logo.png'))
? asset('images/wvsu-janiuay-logo.png')
: asset('images/wvsu-logo.png');
$requests = isset($requests) ? $requests : [];
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Messages - MIS Service Request System</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            background: #f4f8fc;
            overflow-x: hidden;
        }

        .site-header {
            background: #1f6fa8;
            border-bottom: 4px solid #f4c430;
            color: #fff;
        }

        .header-inner {
            max-width: 1180px;
            margin: 0 auto;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand img {
            width: 64px;
            height: 64px;
            object-fit: contain;
            background: #fff;
            border-radius: 50%;
            padding: 4px;
        }

        .brand small {
            display: block;
            font: 700 15px Georgia, "Times New Roman", serif;
        }

        .brand h1 {
            font: 700 clamp(20px, 3vw, 32px) Georgia, "Times New Roman", serif;
            text-transform: uppercase;
            line-height: 1.05;
        }

        .brand p {
            font: 700 16px Georgia, "Times New Roman", serif;
        }

        .top-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .button,
        button.button {
            display: inline-block;
            padding: 9px 13px;
            border: 1px solid #1f6fa8;
            border-radius: 3px;
            background: #1f6fa8;
            color: #fff;
            font: 700 14px Arial, sans-serif;
            text-decoration: none;
            cursor: pointer;
        }

        .button.secondary {
            background: #fff;
            color: #1f6fa8;
        }

        .button:hover {
            background: #164875;
        }

        .button.secondary:hover {
            background: #f0f0f0;
        }

        .page {
            max-width: 1180px;
            margin: 0 auto;
            padding: 22px clamp(14px, 4vw, 20px) 48px;
        }

        .nav-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .nav-shell {
            margin-bottom: 18px;
        }

        .nav-toggle {
            display: none;
            align-items: center;
            gap: 10px;
            min-height: 44px;
            padding: 10px 14px;
            border: 1px solid #cfd8e3;
            border-radius: 10px;
            background: #fff;
            color: #1f3b57;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
            margin-bottom: 12px;
        }

        .nav-toggle-icon {
            display: inline-grid;
            gap: 4px;
        }

        .nav-toggle-icon span {
            display: block;
            width: 18px;
            height: 2px;
            border-radius: 999px;
            background: #1f6fa8;
        }

        .nav-tabs a {
            padding: 8px 14px;
            border-radius: 3px;
            color: #2d123d;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
        }

        .nav-tabs a.active {
            background: #2f7fbd;
            color: #fff;
        }

        .nav-tabs a:hover {
            background: #e5e7eb;
        }

        .panel {
            padding: 22px;
            background: #fff;
            border: 1px solid #d7dce2;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
        }

        .section {
            margin-bottom: 28px;
        }

        .section h3 {
            margin-bottom: 16px;
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }

        .section-header {
            background: #f3f4f6;
            padding: 16px 22px;
            margin: -22px -22px 16px -22px;
            border-radius: 6px 6px 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .section-header h1 {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }

        .message-list {
            display: grid;
            gap: 12px;
        }

        .message-item {
            background: #f9fafb;
            padding: 18px;
            border-radius: 8px;
            border-left: 6px solid #1f6fa8;
            display: grid;
            gap: 10px;
            transition: all 0.15s ease;
        }

        .message-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 28px rgba(17, 24, 39, 0.08);
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            flex-wrap: wrap;
            gap: 8px;
        }

        .message-from {
            font-weight: 700;
            color: #111827;
            font-size: 14px;
        }

        .message-time {
            font-size: 12px;
            color: #6b7280;
        }

        .message-subject {
            font-weight: 600;
            color: #1f6fa8;
            font-size: 14px;
        }

        .message-preview {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.5;
            max-height: 3em;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 6px;
        }

        .message-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-link {
            display: inline-block;
            padding: 8px 14px;
            background: #1f6fa8;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            min-width: 110px;
            text-align: center;
        }

        .action-link:hover {
            background: #164875;
        }

        .action-link.delete {
            background: #ef4444;
        }

        .action-link.delete:hover {
            background: #dc2626;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .empty-state h3 {
            font-size: 18px;
            margin-bottom: 8px;
            color: #374151;
        }

        .search-box {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-box input {
            flex: 1;
            max-width: 400px;
            padding: 10px 14px;
            border: 1px solid #d7dce2;
            border-radius: 3px;
            font-size: 14px;
        }

        .search-box button {
            padding: 10px 16px;
            background: #1f6fa8;
            color: #fff;
            border: none;
            border-radius: 3px;
            font-weight: 600;
            cursor: pointer;
        }

        .search-box button:hover {
            background: #164875;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 16px;
            border-left: 4px solid;
        }

        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border-color: #3b82f6;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 28px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 4px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        #chatPanel {
            inset: auto 20px 20px auto;
            width: min(420px, calc(100% - 40px)) !important;
            height: min(560px, calc(100vh - 40px)) !important;
        }

        .btn-secondary {
            background: #fff;
            color: #1f6fa8;
            border: 1px solid #1f6fa8;
        }

        .btn-secondary:hover {
            background: #f0f0f0;
        }

        @media (max-width: 1024px) {
            .panel {
                padding: 20px;
            }
        }

        @media (max-width: 860px) {
            .header-inner {
                flex-direction: column;
                align-items: flex-start;
            }

            .nav-toggle {
                display: inline-flex;
            }

            .nav-tabs {
                display: none;
                flex-direction: column;
                gap: 6px;
                padding: 12px;
                border: 1px solid #d7dce2;
                border-radius: 12px;
                background: #fff;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
                margin-bottom: 0;
            }

            .nav-tabs.open {
                display: flex;
            }

            .nav-tabs a {
                width: 100%;
            }

            .message-header {
                flex-direction: column;
            }

            .search-box form {
                max-width: 100% !important;
                width: 100%;
            }

            .search-box input {
                max-width: 100%;
            }

            .message-actions {
                flex-direction: column;
            }

            .action-link {
                width: 100%;
            }
        }

        @media (max-width: 640px) {
            .panel {
                padding: 18px;
            }

            .section-header {
                padding: 14px 18px;
                margin: -18px -18px 16px -18px;
            }

            #chatPanel {
                left: 12px !important;
                right: 12px !important;
                bottom: 12px !important;
                width: auto !important;
                max-width: none !important;
                height: calc(100vh - 24px) !important;
            }

            #chatForm {
                flex-direction: column;
            }

            #chatForm button {
                width: 100%;
            }
        }
    </style>
    @include('user.partials.theme')
    <style>
        .message-list {
            max-height: min(58vh, 580px);
            padding: 2px;
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            align-items: start;
            gap: 9px !important;
            overflow-y: auto;
            overscroll-behavior: contain;
            scrollbar-gutter: stable;
        }
        .message-item { height: 100%; }
        .message-item.unread { border-color: #9fc3dc !important; background: #f4f9fc !important; }
        .client-unread { display: inline-flex; min-width: 20px; height: 20px; padding: 0 6px; align-items: center; justify-content: center; color: #fff; border-radius: 999px; background: #185982; font-size: 10px; font-weight: 800; }
        .message-actions { flex-direction: row !important; }
        .message-actions .action-link { width: auto !important; min-width: 0; flex: 1 1 auto; min-height: 38px; padding: 7px 9px; }
        .search-box form { width: min(100%, 520px); max-width: none !important; display: grid !important; grid-template-columns: minmax(0, 1fr) auto; }
        .search-box form input { width: 100%; min-width: 0; max-width: none !important; }
        .chat-compose { padding: 10px 12px; border-top: 1px solid #e3e9ed; background: #fff; }
        .chat-compose-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: end; gap: 8px; }
        .chat-compose textarea { width: 100%; min-height: 44px; max-height: 110px; padding: 10px 12px; border: 1px solid #cbd7df; border-radius: 7px; font: inherit; font-size: 13px; resize: vertical; }
        .chat-compose button[type="submit"] { min-height: 44px; padding: 9px 14px; border: 0; border-radius: 7px; color: #fff; background: #1f6fa8; font-weight: 700; cursor: pointer; }
        .chat-compose-meta { margin-top: 7px; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .chat-attachment { position: relative; min-height: 34px; padding: 6px 10px; display: inline-flex; align-items: center; color: #185982; border: 1px solid #c7d5de; border-radius: 6px; background: #fff; font-size: 11px; font-weight: 700; cursor: pointer; }
        .chat-attachment input { position: absolute; width: 1px; height: 1px; opacity: 0; }
        .chat-status { min-width: 0; color: #687985; font-size: 11px; overflow-wrap: anywhere; text-align: right; }
        .chat-status.error { color: #a33131; }

        @media (max-width: 1024px) {
            #chatPanel {
                width: min(390px, calc(100% - 28px)) !important;
                height: min(62vh, 500px) !important;
                max-height: calc(100vh - 80px) !important;
                right: 14px !important;
                bottom: 14px !important;
            }
        }

        @media (max-width: 700px) {
            .message-list { max-height: min(54vh, 480px); grid-template-columns: 1fr; }
            .message-item { padding: 13px !important; }
            .message-header { gap: 5px !important; }
            .message-preview { margin: 8px 0 !important; }
            .search-box form { grid-template-columns: 1fr; }
            .search-box form button { width: 100%; min-height: 42px; }
            #chatPanel {
                inset: auto 10px 10px 10px !important;
                width: auto !important;
                max-width: none !important;
                height: min(68vh, 480px) !important;
                max-height: calc(100vh - 86px) !important;
                border-radius: 10px !important;
            }
            .chat-compose-row { grid-template-columns: 1fr; }
            .chat-compose button[type="submit"] { width: 100% !important; }
            .chat-compose-meta { align-items: flex-start; flex-direction: column; }
            .chat-status { text-align: left; }
        }

        @media (max-width: 380px) {
            #chatPanel { height: min(64vh, 430px) !important; }
        }
    </style>
</head>

<body>
    @include('partials.header')

    <main class="page">
        @include('user.partials.navigation')

        <div class="panel">
            <div class="section-header">
                <div>
                    <h1>Messages</h1>
                    <small>Conversations connected to your service requests &middot; West Visayas State University</small>
                </div>
            </div>

            <div class="alert alert-info">
                <strong>Private request conversations.</strong> Use Messages to provide details or respond to the MIS Office about an existing service request.
            </div>

            <!-- Search -->
            <div class="search-box">
                <form style="display: flex; gap: 8px; flex: 1; max-width: 400px;">
                    <input type="text" name="search" placeholder="Search control number or message" value="{{ request('search') }}" style="flex: 1;">
                    <button type="submit">Search</button>
                </form>
            </div>

            <!-- Messages List -->
            <div class="section">
                @if(count($requests) > 0)
                <h3>Conversations</h3>
                <div class="message-list">
                    @foreach($requests as $request)
                    @php($latestMessage = $request->messages->first())
                    <div class="message-item {{ $request->unread_messages_count ? 'unread' : '' }}" data-conversation-card="{{ $request->id }}">
                        <div class="message-header">
                            <div>
                                <div class="message-from">
                                    Request {{ $request->control_number ?? 'N/A' }}
                                    @if($request->unread_messages_count)<span class="client-unread">{{ $request->unread_messages_count }}</span>@endif
                                </div>
                                <div class="message-subject">
                                    {{ $latestMessage ? ($latestMessage->is_admin ? 'MIS Office replied' : 'You sent a message') : 'Conversation ready' }}
                                </div>
                            </div>
                            <div class="message-time">
                                {{ ($latestMessage?->created_at ?? $request->created_at)?->format('M d, Y h:i A') }}
                            </div>
                        </div>
                        <div class="message-preview">
                            {{ $latestMessage ? \Illuminate\Support\Str::limit($latestMessage->message, 120) : 'Open this conversation to send the first message about your request.' }}
                        </div>
                        <div class="message-actions">
                            <a href="{{ route('requests.show', $request->id) }}" class="action-link">View Request</a>
                            <a href="#" class="action-link open-chat" data-request-id="{{ $request->id }}" data-control="{{ $request->control_number }}">Open Chat</a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="empty-state">
                    <h3>No Conversations</h3>
                    <p>Submit a service request to start a private conversation with the MIS Office.</p>
                    <br>
                    <a href="{{ route('requests.create') }}" class="button">+ Submit a Service Request</a>
                </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">&larr; Back to Dashboard</a>
            </div>
        </div>
    </main>

    <!-- Chat Panel -->
    <aside id="chatPanel" style="position:fixed;right:20px;bottom:20px;width:420px;max-width:calc(100% - 40px);height:560px;background:#fff;border-radius:12px;box-shadow:0 10px 40px rgba(2,6,23,0.12);display:none;flex-direction:column;overflow:hidden;z-index:1200;">
        <div style="padding:14px 16px;border-bottom:1px solid #eef2f7;display:flex;align-items:center;justify-content:space-between;gap:8px;background:#f8fafc;">
            <div>
                <strong id="chatTitle">Conversation</strong>
                <div id="chatSubtitle" style="font-size:12px;color:#6b7280;">Messages</div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <button id="closeChat" style="background:transparent;border:none;font-weight:700;cursor:pointer;color:#374151;">Close</button>
            </div>
        </div>
        <div id="chatMessages" style="flex:1;overflow:auto;padding:12px;display:flex;flex-direction:column;gap:10px;background:#fbfcfe;"></div>
        <form id="chatForm" class="chat-compose">
            <div class="chat-compose-row">
                <label class="visually-hidden" for="chatInput">Message</label>
                <textarea id="chatInput" maxlength="5000" placeholder="Write a message to the MIS Office..." required></textarea>
                <button type="submit" id="chatSendButton">Send</button>
            </div>
            <div class="chat-compose-meta">
                <label class="chat-attachment">Attach file<input id="chatAttachment" type="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"></label>
                <span class="chat-status" id="chatStatus">PDF, document, or image up to the configured upload limit.</span>
            </div>
        </form>
    </aside>
</body>

</html>

<script>
    (function() {
        const chatPanel = document.getElementById('chatPanel');
        const chatMessages = document.getElementById('chatMessages');
        const chatTitle = document.getElementById('chatTitle');
        const chatSubtitle = document.getElementById('chatSubtitle');
        const chatForm = document.getElementById('chatForm');
        const chatInput = document.getElementById('chatInput');
        const chatAttachment = document.getElementById('chatAttachment');
        const chatStatus = document.getElementById('chatStatus');
        const chatSendButton = document.getElementById('chatSendButton');
        const closeChat = document.getElementById('closeChat');
        const requestedConversation = new URLSearchParams(window.location.search).get('request');
        let activeRequestId = null;
        let pollInterval = null;
        let lastId = 0;
        let sendNeedsRetry = false;

        function setChatStatus(message, isError = false) {
            chatStatus.textContent = message;
            chatStatus.classList.toggle('error', isError);
        }

        function el(html) {
            const div = document.createElement('div');
            div.innerHTML = html.trim();
            return div.firstChild;
        }

        function formatMessage(msg) {
            const isAdmin = msg.is_admin;
            const align = isAdmin ? 'flex-start' : 'flex-end';
            const bg = isAdmin ? '#eef2ff' : '#e6ffef';
            const color = '#111827';
            const files = Array.isArray(msg.attachments) ? msg.attachments.map(file => `
                <a href="${escapeHtml(file.download_url)}" style="display:flex;justify-content:space-between;gap:8px;margin-top:6px;padding:6px 8px;border:1px solid #cbd5e1;border-radius:6px;color:#1f3b57;text-decoration:none;font-size:11px;">
                    <span>${escapeHtml(file.name)}</span><span>${formatFileSize(file.size)}</span>
                </a>
            `).join('') : '';
            return `
            <div style="display:flex;justify-content:${align};">
                <div style="max-width:78%;background:${bg};padding:10px 12px;border-radius:10px;color:${color};font-size:13px;">
                    <div style="font-weight:700;margin-bottom:6px;">${escapeHtml(msg.sender_name)}</div>
                    <div style="white-space:pre-wrap;">${escapeHtml(msg.message)}</div>
                    ${files}
                    <div style="font-size:11px;color:#6b7280;margin-top:6px;text-align:right;">${escapeHtml(msg.created_at)}</div>
                </div>
            </div>
        `;
        }

        function formatFileSize(bytes) {
            const size = Number(bytes || 0);
            if (!size) return '';
            if (size < 1024) return size + ' B';
            if (size < 1048576) return Math.round(size / 1024) + ' KB';
            return (size / 1048576).toFixed(1) + ' MB';
        }

        function escapeHtml(str) {
            if (str == null) return '';
            return String(str).replace(/[&<>"]/g, function(c) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;'
                } [c];
            });
        }

        function updateConversationCard(requestId, message) {
            const card = document.querySelector(`[data-conversation-card="${requestId}"]`);
            if (!card || !message) return;

            const subject = card.querySelector('.message-subject');
            const preview = card.querySelector('.message-preview');
            const time = card.querySelector('.message-time');
            if (subject) subject.textContent = message.is_admin ? 'MIS Office replied' : 'You sent a message';
            if (preview) preview.textContent = message.message || 'Attachment sent';
            if (time) time.textContent = message.created_at || '';

            const list = card.closest('.message-list');
            if (list && list.firstElementChild !== card) list.prepend(card);
        }

        async function loadMessages(requestId) {
            setChatStatus('Loading conversation...');
            try {
                const res = await fetch(`/api/messages/request/${requestId}?limit=100`, {
                    headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
                });
                const data = await res.json();
                if (!res.ok || !data.success) throw new Error(data.error || data.message || 'Unable to load this conversation.');
                chatMessages.innerHTML = '';
                data.messages.forEach(m => chatMessages.appendChild(el(formatMessage(m))));
                if (data.messages.length) updateConversationCard(requestId, data.messages[data.messages.length - 1]);
                lastId = Number(data.last_id || 0);
                const card = document.querySelector(`[data-conversation-card="${requestId}"]`);
                card?.classList.remove('unread');
                card?.querySelector('.client-unread')?.remove();
                chatMessages.scrollTop = chatMessages.scrollHeight;
                setChatStatus(data.messages.length ? 'Conversation loaded.' : 'No messages yet. Send the first message below.');
            } catch (e) {
                setChatStatus(e.message || 'Unable to load this conversation.', true);
            }
        }

        async function pollNew(requestId) {
            if (!requestId || document.hidden) return;
            try {
                const res = await fetch(`/api/messages/request/${requestId}/new?after_id=${lastId}`, {
                    headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
                });
                const data = await res.json();
                if (!res.ok || !data.success) return;
                if (data.messages && data.messages.length) {
                    data.messages.forEach(m => chatMessages.appendChild(el(formatMessage(m))));
                    updateConversationCard(requestId, data.messages[data.messages.length - 1]);
                    lastId = Number(data.last_id || data.messages[data.messages.length - 1].id || lastId);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            } catch (e) {}
        }

        document.querySelectorAll('.open-chat').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.dataset.requestId;
                const control = this.dataset.control || 'Conversation';
                activeRequestId = id;
                lastId = 0;
                chatTitle.textContent = `Conversation • ${control}`;
                chatSubtitle.textContent = `Request #${control}`;
                chatPanel.style.display = 'flex';
                loadMessages(id);
                if (pollInterval) clearInterval(pollInterval);
                pollInterval = setInterval(() => pollNew(id), 4000);
            });
        });

        closeChat.addEventListener('click', function() {
            chatPanel.style.display = 'none';
            activeRequestId = null;
            lastId = 0;
            chatAttachment.value = '';
            if (pollInterval) clearInterval(pollInterval);
        });

        chatForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (!activeRequestId) return;
            const text = chatInput.value.trim();
            if (!text) return;
            chatSendButton.disabled = true;
            chatSendButton.textContent = sendNeedsRetry ? 'Retrying...' : 'Sending...';
            setChatStatus(sendNeedsRetry ? 'Retrying message...' : 'Sending message...');
            try {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const form = new FormData();
                form.append('service_request_id', activeRequestId);
                form.append('message', text);
                if (chatAttachment.files[0]) form.append('attachment', chatAttachment.files[0]);
                const res = await fetch('/api/messages/send', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token
                    },
                    body: form
                });
                const data = await res.json();
                const validationMessage = data.errors ? Object.values(data.errors).flat()[0] : null;
                if (!res.ok || !data.success) {
                    const error = new Error(data.error || validationMessage || data.message || 'Unable to send the message.');
                    error.status = res.status;
                    error.retryAfter = Number(data.retry_after || res.headers.get('Retry-After') || 0);
                    throw error;
                }
                chatMessages.appendChild(el(formatMessage(data.message)));
                updateConversationCard(activeRequestId, data.message);
                lastId = Math.max(lastId, Number(data.message.id || 0));
                chatInput.value = '';
                chatAttachment.value = '';
                sendNeedsRetry = false;
                chatSendButton.textContent = 'Send';
                setChatStatus('Message sent.');
                chatMessages.scrollTop = chatMessages.scrollHeight;
            } catch (err) {
                sendNeedsRetry = true;
                chatSendButton.textContent = 'Retry';
                const waitMessage = err.status === 429 && err.retryAfter
                    ? ` Please retry in about ${err.retryAfter} seconds.`
                    : ' Your draft and attachment were kept.';
                setChatStatus((err.message || 'Network error. Please try again.') + waitMessage, true);
            } finally {
                chatSendButton.disabled = false;
            }
        });

        chatInput.addEventListener('input', function() {
            if (sendNeedsRetry) {
                sendNeedsRetry = false;
                chatSendButton.textContent = 'Send';
            }
        });

        chatAttachment.addEventListener('change', function() {
            setChatStatus(this.files[0] ? `Attached: ${this.files[0].name}` : 'PDF, document, or image up to the configured upload limit.');
        });

        document.addEventListener('visibilitychange', function() {
            if (!document.hidden && activeRequestId) pollNew(activeRequestId);
        });

        if (requestedConversation) {
            const target = document.querySelector(`.open-chat[data-request-id="${requestedConversation}"]`);
            if (target) {
                target.click();
            }
        }

    })();
</script>
