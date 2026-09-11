@extends('admin.layout')

@section('title', 'Messenger')
@section('page-title', 'Messenger')
@section('page-subtitle', 'Review and respond to client conversations linked to service requests.')
@section('body-class', 'admin-messenger-page')

@section('head')
    <style>
        .admin-messenger-page .content-grid { width: 100%; max-width: 1540px; }
        .messenger-shell { display: grid; grid-template-columns: minmax(280px, 330px) minmax(0, 1fr); gap: 12px; min-height: calc(100vh - 150px); }
        .conversation-pane, .thread-pane { min-width: 0; overflow: hidden; border: 1px solid var(--line); border-radius: 10px; background: #fff; box-shadow: var(--shadow); }
        .conversation-pane { display: flex; flex-direction: column; }
        .conversation-head { padding: 12px; border-bottom: 1px solid var(--line); background: #fbfcfd; }
        .conversation-title-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .conversation-title-row h2 { margin: 0; color: var(--ink-900); font-size: 15px; }
        .conversation-total { color: var(--ink-500); font-size: 11px; font-weight: 700; }
        .conversation-search { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 6px; margin-top: 10px; }
        .conversation-search input { min-width: 0; min-height: 38px; padding: 7px 9px; font-size: 12px; }
        .conversation-search button { min-height: 38px; padding: 7px 10px; }
        .conversation-scopes { display: grid; grid-template-columns: repeat(3, 1fr); gap: 5px; margin-top: 8px; }
        .conversation-scope { padding: 6px 5px; border: 1px solid var(--line); border-radius: 6px; color: var(--ink-600); background: #fff; font-size: 10px; font-weight: 750; text-align: center; text-decoration: none; }
        .conversation-scope.active { border-color: var(--brand-700); color: var(--brand-900); background: var(--brand-100); }
        .conversation-list { display: grid; align-content: start; gap: 6px; max-height: calc(100vh - 342px); min-height: 260px; overflow-y: auto; padding: 8px; }
        .conversation-item { position: relative; width: 100%; padding: 10px; border: 1px solid transparent; border-radius: 8px; color: var(--ink-900); background: transparent; font: inherit; text-align: left; cursor: pointer; }
        .conversation-item:hover { background: #f5f8fa; }
        .conversation-item.active { border-color: #a9c7dc; background: var(--brand-100); }
        .conversation-item.unread { border-color: #bfd6e6; background: #f7fbfe; }
        .conversation-item:focus-visible { outline: 3px solid rgba(31,111,168,.28); outline-offset: 1px; }
        .conversation-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .conversation-name { min-width: 0; overflow: hidden; color: var(--ink-900); font-size: 12px; font-weight: 750; text-overflow: ellipsis; white-space: nowrap; }
        .conversation-control { margin-top: 3px; color: var(--brand-800); font-size: 10px; font-weight: 750; }
        .conversation-preview { margin-top: 5px; overflow: hidden; color: var(--ink-500); font-size: 11px; line-height: 1.35; text-overflow: ellipsis; white-space: nowrap; }
        .conversation-badges { display: flex; align-items: center; flex: 0 0 auto; gap: 4px; }
        .conversation-unread { min-width: 20px; padding: 2px 6px; border-radius: 999px; color: #fff; background: var(--brand-800); font-size: 10px; font-weight: 800; text-align: center; }
        .conversation-walkin { padding: 2px 5px; border-radius: 999px; color: #7c4a03; background: #fff3cd; font-size: 9px; font-weight: 750; }
        .conversation-pagination { padding: 8px 10px; border-top: 1px solid var(--line); }
        .thread-pane { display: grid; grid-template-rows: auto auto minmax(0, 1fr) auto; min-height: 650px; }
        .thread-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; min-height: 64px; padding: 10px 12px; border-bottom: 1px solid var(--line); background: #fbfcfd; }
        .thread-heading { min-width: 0; }
        .thread-heading h2 { margin: 0; overflow: hidden; color: var(--ink-900); font-size: 15px; text-overflow: ellipsis; white-space: nowrap; }
        .thread-heading p { margin: 3px 0 0; color: var(--ink-500); font-size: 11px; }
        .thread-head-actions { display: flex; align-items: center; flex: 0 0 auto; gap: 6px; }
        .thread-back { display: none; }
        .thread-context { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 14px; min-height: 35px; padding: 7px 12px; border-bottom: 1px solid var(--line); color: var(--ink-500); background: #fff; font-size: 10px; }
        .thread-context strong { color: var(--ink-700); }
        .thread-alert { margin-left: auto; color: #8a4b08; font-weight: 700; }
        .thread-messages { min-height: 300px; overflow-y: auto; padding: 12px; background: #f5f7f9; overscroll-behavior: contain; }
        .thread-empty, .thread-error { display: grid; place-items: center; min-height: 220px; padding: 20px; color: var(--ink-500); font-size: 12px; text-align: center; }
        .thread-error { color: #9f2f2f; }
        .load-older-wrap { display: flex; justify-content: center; margin-bottom: 10px; }
        .load-older { min-height: 30px; padding: 5px 9px; font-size: 10px; }
        .bubble-row { display: flex; margin-top: 8px; }
        .bubble-row.admin { justify-content: flex-end; }
        .bubble { max-width: min(76%, 620px); padding: 9px 11px; border: 1px solid var(--line); border-radius: 10px 10px 10px 3px; color: var(--ink-900); background: #fff; font-size: 12px; line-height: 1.45; overflow-wrap: anywhere; }
        .bubble-row.admin .bubble { border-color: var(--brand-900); border-radius: 10px 10px 3px 10px; color: #fff; background: var(--brand-900); }
        .bubble-meta { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 4px; color: var(--ink-500); font-size: 9px; font-weight: 700; }
        .bubble-row.admin .bubble-meta { color: rgba(255,255,255,.75); }
        .bubble-text { white-space: pre-wrap; }
        .bubble-attachments { display: grid; gap: 4px; margin-top: 7px; }
        .bubble-attachment { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 5px 7px; border: 1px solid rgba(127,146,164,.35); border-radius: 6px; color: inherit; font-size: 10px; text-decoration: none; }
        .bubble-seen { margin-top: 4px; color: var(--ink-400); font-size: 9px; text-align: right; }
        .compose { padding: 9px 11px 10px; border-top: 1px solid var(--line); background: #fff; }
        .quick-replies { display: flex; gap: 5px; overflow-x: auto; padding-bottom: 7px; }
        .quick-reply { flex: 0 0 auto; min-height: 28px; padding: 4px 8px; border: 1px solid var(--line); border-radius: 999px; color: var(--ink-600); background: #fff; font-size: 9px; font-weight: 700; cursor: pointer; }
        .quick-reply:hover { border-color: var(--brand-700); color: var(--brand-900); }
        .compose-main { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: end; gap: 7px; }
        .compose textarea { width: 100%; min-height: 68px; max-height: 150px; padding: 8px 10px; border: 1px solid var(--line); border-radius: 8px; font: inherit; font-size: 12px; resize: vertical; }
        .compose-side { display: grid; gap: 6px; }
        .attachment-label { display: flex; align-items: center; justify-content: center; min-height: 32px; padding: 5px 8px; border: 1px solid var(--line); border-radius: 7px; color: var(--ink-600); background: #fff; font-size: 10px; font-weight: 700; cursor: pointer; }
        .attachment-label input { position: absolute; width: 1px; height: 1px; opacity: 0; }
        .compose-status { min-height: 16px; margin-top: 5px; color: var(--ink-500); font-size: 10px; }
        .compose-status.error { color: #a52d2d; }
        .walkin-contact { padding: 10px; border: 1px solid #efd59a; border-radius: 8px; color: #704713; background: #fff9e9; font-size: 11px; line-height: 1.45; }
        [hidden] { display: none !important; }
        @media (max-width: 1050px) {
            .messenger-shell { grid-template-columns: minmax(260px, 300px) minmax(0, 1fr); }
            .conversation-list { max-height: calc(100vh - 330px); }
        }
        @media (max-width: 780px) {
            .messenger-shell { display: block; min-height: 0; }
            .conversation-pane, .thread-pane { min-height: calc(100vh - 145px); }
            .conversation-list { max-height: calc(100vh - 340px); }
            .thread-pane { display: none; }
            .messenger-shell.thread-open .conversation-pane { display: none; }
            .messenger-shell.thread-open .thread-pane { display: grid; }
            .thread-back { display: inline-flex; }
            .thread-pane { min-height: calc(100vh - 145px); height: calc(100vh - 145px); }
            .thread-messages { min-height: 0; }
        }
        @media (max-width: 390px) {
            .conversation-scopes { grid-template-columns: 1fr; }
            .thread-head { align-items: flex-start; }
            .thread-head-actions .btn-ghost:not(.thread-back) { display: none; }
            .bubble { max-width: 88%; }
            .compose-main { grid-template-columns: 1fr; }
            .compose-side { grid-template-columns: 1fr 1fr; }
        }
    </style>
@endsection

@section('content')
    @php
        $statusLabels = [
            'submitted' => 'New', 'pending' => 'Waiting for Assignment', 'processing' => 'In Progress',
            'awaiting_requester_info' => 'Waiting for User', 'awaiting_confirmation' => 'Waiting for Confirmation',
            'completed' => 'Completed', 'closed' => 'Closed', 'reopened' => 'Returned',
            'escalated' => 'Needs Review', 'cancelled' => 'Cancelled', 'rejected' => 'Rejected',
        ];
    @endphp

    <div class="messenger-shell" id="adminMessenger">
        <aside class="conversation-pane" aria-label="Conversations">
            <header class="conversation-head">
                <div class="conversation-title-row"><h2>Conversations</h2><span class="conversation-total">{{ $requestPage->total() }} total</span></div>
                <form class="conversation-search" method="GET" action="{{ route('admin.messenger') }}">
                    <input type="hidden" name="scope" value="{{ $scope }}">
                    <label class="sr-only" for="messengerSearch">Search conversations</label>
                    <input id="messengerSearch" name="search" value="{{ $search }}" placeholder="Requester or control number">
                    <button class="btn-primary" type="submit">Search</button>
                </form>
                <nav class="conversation-scopes" aria-label="Conversation filters">
                    <a class="conversation-scope {{ $scope === 'messages' ? 'active' : '' }}" href="{{ route('admin.messenger', array_filter(['scope' => 'messages', 'search' => $search])) }}">Messages</a>
                    <a class="conversation-scope {{ $scope === 'unread' ? 'active' : '' }}" href="{{ route('admin.messenger', array_filter(['scope' => 'unread', 'search' => $search])) }}">Unread</a>
                    <a class="conversation-scope {{ $scope === 'all' ? 'active' : '' }}" href="{{ route('admin.messenger', array_filter(['scope' => 'all', 'search' => $search])) }}">All Requests</a>
                </nav>
            </header>

            <div class="conversation-list" id="conversationList">
                @forelse($requests as $requestItem)
                    <button
                        type="button"
                        class="conversation-item {{ $requestItem['unread_count'] ? 'unread' : '' }}"
                        data-id="{{ $requestItem['id'] }}"
                        data-name="{{ $requestItem['requester_name'] }}"
                        data-control="{{ $requestItem['control_number'] }}"
                        data-status="{{ $statusLabels[$requestItem['status']] ?? str($requestItem['status'])->replace('_', ' ')->title() }}"
                        data-status-code="{{ $requestItem['status'] }}"
                        data-technician="{{ $requestItem['technician'] ?: 'Unassigned' }}"
                        data-office="{{ $requestItem['requester_office'] ?: 'No office provided' }}"
                        data-phone="{{ $requestItem['phone'] ?: 'No phone provided' }}"
                        data-request-url="{{ $requestItem['request_url'] }}"
                        data-can-message="{{ $requestItem['can_message'] ? '1' : '0' }}"
                        aria-pressed="false"
                    >
                        <span class="conversation-row"><span class="conversation-name">{{ $requestItem['requester_name'] }}</span><span class="conversation-badges">@if($requestItem['is_walk_in'])<span class="conversation-walkin">Walk-in</span>@endif @if($requestItem['unread_count'])<span class="conversation-unread">{{ $requestItem['unread_count'] }}</span>@endif</span></span>
                        <span class="conversation-control">{{ $requestItem['control_number'] }} · {{ $statusLabels[$requestItem['status']] ?? str($requestItem['status'])->replace('_', ' ')->title() }}</span>
                        <span class="conversation-preview">{{ $requestItem['last_message_text'] }}</span>
                    </button>
                @empty
                    <div class="empty-state">No conversations match this view.</div>
                @endforelse
            </div>
            <div class="conversation-pagination">{{ $requestPage->links() }}</div>
        </aside>

        <section class="thread-pane" aria-label="Active conversation">
            <header class="thread-head">
                <div class="thread-head-actions"><button type="button" class="btn-ghost thread-back" id="threadBack">Back</button><div class="thread-heading"><h2 id="threadTitle">Select a conversation</h2><p id="threadSubtitle">Choose a request to view its messages.</p></div></div>
                <div class="thread-head-actions"><a class="btn-ghost" id="viewRequestLink" href="{{ route('admin.requests.index') }}" hidden>Review Request</a></div>
            </header>
            <div class="thread-context" id="threadContext" hidden><span>Technician: <strong id="threadTechnician">—</strong></span><span>Office: <strong id="threadOffice">—</strong></span><span>Phone: <strong id="threadPhone">—</strong></span><span class="thread-alert" id="threadAlert" hidden></span></div>
            <div class="thread-messages" id="messageThread" aria-live="polite"><div class="thread-empty">Messages will appear here after selecting a conversation.</div></div>
            <div class="compose">
                <div class="walkin-contact" id="walkinContact" hidden>This is an account-free walk-in request. Contact the requester using the recorded phone number; Messenger replies are disabled.</div>
                <div id="messageComposer">
                    <div class="quick-replies" aria-label="Quick replies">
                        <button type="button" class="quick-reply" data-reply="MIS has received your request and is reviewing it.">Request received</button>
                        <button type="button" class="quick-reply" data-reply="Please provide the additional information needed to continue processing your request.">Need information</button>
                        <button type="button" class="quick-reply" data-reply="MIS is currently working on your request. We will update you when the work is completed.">Work in progress</button>
                        <button type="button" class="quick-reply" data-reply="The requested work has been completed. Please confirm whether your concern is resolved.">Work completed</button>
                    </div>
                    <div class="compose-main">
                        <div><label class="sr-only" for="messageInput">Message reply</label><textarea id="messageInput" maxlength="5000" placeholder="Write a concise response…" disabled></textarea><div class="compose-status" id="composeStatus">Select a conversation to reply.</div></div>
                        <div class="compose-side"><label class="attachment-label">Attach file<input id="messageAttachment" type="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" disabled></label><button type="button" class="btn-primary" id="sendMessageBtn" disabled>Send</button></div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        (() => {
            const shell = document.getElementById('adminMessenger');
            const thread = document.getElementById('messageThread');
            const title = document.getElementById('threadTitle');
            const subtitle = document.getElementById('threadSubtitle');
            const context = document.getElementById('threadContext');
            const technician = document.getElementById('threadTechnician');
            const office = document.getElementById('threadOffice');
            const phone = document.getElementById('threadPhone');
            const threadAlert = document.getElementById('threadAlert');
            const viewRequest = document.getElementById('viewRequestLink');
            const input = document.getElementById('messageInput');
            const attachment = document.getElementById('messageAttachment');
            const sendButton = document.getElementById('sendMessageBtn');
            const status = document.getElementById('composeStatus');
            const composer = document.getElementById('messageComposer');
            const walkinContact = document.getElementById('walkinContact');
            const conversationList = document.getElementById('conversationList');
            const items = Array.from(document.querySelectorAll('.conversation-item'));
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const messagesBase = @json(url('/admin/api/messages/request'));
            const sendUrl = @json(url('/admin/api/messages/send'));
            const preferredRequest = @json($selectedRequestId);
            let activeId = null;
            let activeItem = null;
            let lastId = 0;
            let oldestId = null;
            let hasMore = false;
            let pollTimer = null;
            let loading = false;
            let sendNeedsRetry = false;
            const renderedIds = new Set();

            function clearNode(node) { while (node.firstChild) node.removeChild(node.firstChild); }
            function setStatus(message, isError = false) { status.textContent = message || ''; status.classList.toggle('error', isError); }
            function showThreadMessage(message, isError = false) { clearNode(thread); const box = document.createElement('div'); box.className = isError ? 'thread-error' : 'thread-empty'; box.textContent = message; thread.appendChild(box); }
            function fileSize(bytes) { if (!bytes) return ''; if (bytes < 1024) return bytes + ' B'; if (bytes < 1048576) return Math.round(bytes / 1024) + ' KB'; return (bytes / 1048576).toFixed(1) + ' MB'; }

            function applyConversationSummary(payload) {
                const conversations = Array.isArray(payload?.conversations) ? payload.conversations : [];
                const byRequest = new Map(conversations.map(entry => [Number(entry.request_id), entry]));

                items.forEach(item => {
                    const summary = byRequest.get(Number(item.dataset.id));
                    if (!summary) return;

                    const unread = Number(summary.unread_count || 0);
                    item.classList.toggle('unread', unread > 0 && item !== activeItem);
                    let badge = item.querySelector('.conversation-unread');
                    if (unread > 0 && item !== activeItem) {
                        if (!badge) {
                            badge = document.createElement('span');
                            badge.className = 'conversation-unread';
                            item.querySelector('.conversation-badges')?.appendChild(badge);
                        }
                        badge.textContent = unread > 99 ? '99+' : String(unread);
                    } else {
                        badge?.remove();
                    }

                    const preview = item.querySelector('.conversation-preview');
                    if (preview && summary.last_message_text) preview.textContent = summary.last_message_text;
                    item.dataset.lastMessageId = String(summary.last_message_id || 0);
                });

                if (conversationList && items.length) {
                    const ordered = [];
                    conversations.forEach(summary => {
                        const item = items.find(entry => Number(entry.dataset.id) === Number(summary.request_id));
                        if (item && !ordered.includes(item)) ordered.push(item);
                    });
                    items.forEach(item => { if (!ordered.includes(item)) ordered.push(item); });
                    conversationList.replaceChildren(...ordered);
                }
            }

            async function fetchJson(url, options = {}) {
                const optionHeaders = options.headers || {};
                const requestOptions = { ...options };
                delete requestOptions.headers;
                const response = await fetch(url, { ...requestOptions, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...optionHeaders } });
                let data = {};
                try { data = await response.json(); } catch (_) {}
                const validationMessage = data.errors ? Object.values(data.errors).flat()[0] : null;
                if (!response.ok) {
                    const error = new Error(data.error || validationMessage || data.message || 'The request could not be completed.');
                    error.status = response.status;
                    error.retryAfter = Number(data.retry_after || response.headers.get('Retry-After') || 0);
                    throw error;
                }
                return data;
            }

            function createBubble(message) {
                const row = document.createElement('div'); row.className = 'bubble-row' + (message.is_admin ? ' admin' : ''); row.dataset.messageId = message.id;
                const bubble = document.createElement('article'); bubble.className = 'bubble';
                const meta = document.createElement('div'); meta.className = 'bubble-meta';
                const sender = document.createElement('span'); sender.textContent = message.sender_name || 'User';
                const time = document.createElement('span'); time.textContent = message.created_at || '';
                meta.append(sender, time);
                const text = document.createElement('div'); text.className = 'bubble-text'; text.textContent = message.message || '';
                bubble.append(meta, text);
                if (Array.isArray(message.attachments) && message.attachments.length) {
                    const files = document.createElement('div'); files.className = 'bubble-attachments';
                    message.attachments.forEach(file => { const link = document.createElement('a'); link.className = 'bubble-attachment'; link.href = file.download_url; const name = document.createElement('span'); name.textContent = file.name; const size = document.createElement('span'); size.textContent = fileSize(file.size); link.append(name, size); files.appendChild(link); });
                    bubble.appendChild(files);
                }
                if (message.is_admin) { const seen = document.createElement('div'); seen.className = 'bubble-seen'; seen.textContent = message.is_read ? 'Seen' : 'Sent'; bubble.appendChild(seen); }
                row.appendChild(bubble);
                return row;
            }

            function insertMessages(messages, prepend = false) {
                const fragment = document.createDocumentFragment();
                messages.forEach(message => { if (renderedIds.has(Number(message.id))) return; renderedIds.add(Number(message.id)); fragment.appendChild(createBubble(message)); lastId = Math.max(lastId, Number(message.id) || 0); });
                if (prepend) thread.insertBefore(fragment, thread.firstChild); else thread.appendChild(fragment);
            }

            function addLoadOlderButton() {
                thread.querySelector('.load-older-wrap')?.remove();
                if (!hasMore || !oldestId) return;
                const wrap = document.createElement('div'); wrap.className = 'load-older-wrap';
                const button = document.createElement('button'); button.type = 'button'; button.className = 'btn-ghost load-older'; button.textContent = 'Load older messages'; button.addEventListener('click', loadOlder);
                wrap.appendChild(button); thread.insertBefore(wrap, thread.firstChild);
            }

            async function loadConversation(item) {
                if (loading) return;
                loading = true; activeItem = item; activeId = Number(item.dataset.id); lastId = 0; oldestId = null; hasMore = false; renderedIds.clear();
                items.forEach(entry => { const active = entry === item; entry.classList.toggle('active', active); entry.setAttribute('aria-pressed', active ? 'true' : 'false'); });
                title.textContent = item.dataset.name; subtitle.textContent = item.dataset.control + ' · ' + item.dataset.status;
                technician.textContent = item.dataset.technician; office.textContent = item.dataset.office; phone.textContent = item.dataset.phone; context.hidden = false;
                viewRequest.href = item.dataset.requestUrl; viewRequest.hidden = false;
                const canMessage = item.dataset.canMessage === '1'; composer.hidden = !canMessage; walkinContact.hidden = canMessage; threadAlert.hidden = canMessage; threadAlert.textContent = canMessage ? '' : 'No client account';
                input.disabled = !canMessage; attachment.disabled = !canMessage; sendButton.disabled = !canMessage; setStatus(canMessage ? 'Ctrl + Enter sends the message.' : '');
                shell.classList.add('thread-open'); showThreadMessage('Loading conversation…'); updateUrl(activeId);
                try {
                    const data = await fetchJson(messagesBase + '/' + activeId + '?limit=50');
                    clearNode(thread); const messages = Array.isArray(data.messages) ? data.messages : [];
                    if (messages.length) { insertMessages(messages); oldestId = data.oldest_id; hasMore = Boolean(data.has_more); addLoadOlderButton(); thread.scrollTop = thread.scrollHeight; }
                    else showThreadMessage(canMessage ? 'No messages yet. Send the first reply below.' : 'This walk-in record has no client conversation.');
                    item.classList.remove('unread'); item.querySelector('.conversation-unread')?.remove();
                    startPolling();
                } catch (error) { showThreadMessage(error.message, true); setStatus(error.message, true); }
                finally { loading = false; }
            }

            async function loadOlder() {
                if (!activeId || !oldestId) return;
                const previousHeight = thread.scrollHeight;
                try { const data = await fetchJson(messagesBase + '/' + activeId + '?limit=50&before_id=' + oldestId); const messages = data.messages || []; insertMessages(messages, true); oldestId = data.oldest_id || oldestId; hasMore = Boolean(data.has_more); addLoadOlderButton(); thread.scrollTop += thread.scrollHeight - previousHeight; }
                catch (error) { setStatus(error.message, true); }
            }

            async function poll() {
                if (!activeId || document.hidden || loading) return;
                try { const data = await fetchJson(messagesBase + '/' + activeId + '/new?after_id=' + lastId); if (data.messages?.length) { thread.querySelector('.thread-empty')?.remove(); insertMessages(data.messages); thread.scrollTop = thread.scrollHeight; } if (Array.isArray(data.read_message_ids)) { data.read_message_ids.forEach(id => { const seen = thread.querySelector('[data-message-id="' + Number(id) + '"] .bubble-seen'); if (seen) seen.textContent = 'Seen'; }); } }
                catch (_) { /* Keep polling quiet; the next successful request recovers. */ }
            }
            function startPolling() { clearInterval(pollTimer); pollTimer = setInterval(poll, 4000); }
            function updateUrl(id) { const url = new URL(window.location.href); url.searchParams.set('request', id); history.replaceState({}, '', url); }

            async function sendMessage() {
                const message = input.value.trim(); if (!activeId || !message || sendButton.disabled) return;
                sendButton.disabled = true;
                sendButton.textContent = sendNeedsRetry ? 'Retrying...' : 'Sending...';
                setStatus(sendNeedsRetry ? 'Retrying message...' : 'Sending message...');
                const form = new FormData(); form.append('service_request_id', activeId); form.append('message', message); if (attachment.files[0]) form.append('attachment', attachment.files[0]);
                try {
                    const data = await fetchJson(sendUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf }, body: form });
                    thread.querySelector('.thread-empty')?.remove(); insertMessages([data.message]); input.value = ''; attachment.value = '';
                    sendNeedsRetry = false; sendButton.textContent = 'Send'; setStatus('Message sent.'); thread.scrollTop = thread.scrollHeight;
                }
                catch (error) {
                    sendNeedsRetry = true;
                    sendButton.textContent = 'Retry';
                    const waitMessage = error.status === 429 && error.retryAfter
                        ? ' Please retry in about ' + error.retryAfter + ' seconds.'
                        : ' Your draft and attachment were kept.';
                    setStatus((error.message || 'Unable to send the message.') + waitMessage, true);
                }
                finally { sendButton.disabled = false; }
            }

            items.forEach(item => item.addEventListener('click', () => loadConversation(item)));
            document.getElementById('threadBack').addEventListener('click', () => shell.classList.remove('thread-open'));
            sendButton.addEventListener('click', sendMessage);
            input.addEventListener('input', () => { if (sendNeedsRetry) { sendNeedsRetry = false; sendButton.textContent = 'Send'; } });
            input.addEventListener('keydown', event => { if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') { event.preventDefault(); sendMessage(); } });
            document.querySelectorAll('.quick-reply').forEach(button => button.addEventListener('click', () => { input.value = button.dataset.reply; input.focus(); }));
            attachment.addEventListener('change', () => { if (attachment.files[0]) setStatus('Attached: ' + attachment.files[0].name); });
            document.addEventListener('visibilitychange', () => { if (!document.hidden) poll(); });
            window.addEventListener('admin-messenger-summary', event => applyConversationSummary(event.detail));
            window.addEventListener('beforeunload', () => clearInterval(pollTimer));

            const initial = items.find(item => Number(item.dataset.id) === Number(preferredRequest)) || items[0];
            if (initial && (preferredRequest || !window.matchMedia('(max-width: 780px)').matches)) loadConversation(initial);
        })();
    </script>
@endsection
