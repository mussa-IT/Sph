@extends('layouts.app')

@section('title', 'AI Chat')
@section('pageHeading', 'AI Workspace')
@section('pageTitle', 'Smart Chat Assistant')

@section('content')
@php
    $chatSessions = $sessions ?? collect();
    $activeSessionId = isset($chatSession) ? (int) $chatSession->id : null;
    $todaySessions = $chatSessions->filter(fn ($session) => $session->created_at?->isToday());
    $yesterdaySessions = $chatSessions->filter(fn ($session) => $session->created_at?->isYesterday());
    $olderSessions = $chatSessions->filter(fn ($session) => $session->created_at && ! $session->created_at->isToday() && ! $session->created_at->isYesterday());
@endphp

<style>
    @keyframes chatMessageEnter {
        from { opacity: 0; transform: translateY(8px) scale(0.99); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .message-enter {
        animation: chatMessageEnter 0.24s ease-out;
    }

    .chat-session-item {
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .chat-session-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 28px -22px rgba(10, 20, 35, 0.65);
    }

    .user-message-card,
    .ai-message-card {
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }

    .user-message-card:hover,
    .ai-message-card:hover {
        transform: translateY(-1px);
    }

    .user-message-card:hover {
        box-shadow: 0 14px 28px -20px rgba(17, 24, 39, 0.65);
    }

    .ai-message-card:hover {
        box-shadow: 0 14px 30px -24px rgba(15, 23, 42, 0.35);
    }

    #chat-messages {
        scroll-behavior: smooth;
    }

    #chat-messages::-webkit-scrollbar {
        width: 8px;
    }

    #chat-messages::-webkit-scrollbar-track {
        background: transparent;
    }

    #chat-messages::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.42);
        border-radius: 9999px;
    }

    #chat-messages::-webkit-scrollbar-thumb:hover {
        background: rgba(100, 116, 139, 0.56);
    }

    @media (max-width: 1023px) {
        #chat-messages {
            max-height: min(58vh, 520px) !important;
        }
    }
</style>

<div class="grid min-h-[calc(100vh-14rem)] gap-4 lg:gap-6 lg:grid-cols-[340px_minmax(0,1fr)]">
    <aside class="rounded-[2rem] border border-muted/10 bg-background-secondary p-4 shadow-card backdrop-blur-xl dark:border-muted-dark/10 dark:bg-background-secondary-dark lg:sticky lg:top-5 lg:h-[calc(100vh-12rem)] lg:p-5">
        <div class="flex h-full flex-col gap-4">
            <button id="new-chat-button" type="button" class="inline-flex min-h-[46px] items-center justify-center gap-2 rounded-2xl bg-primary px-4 py-3 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition duration-200 hover:-translate-y-0.5 hover:bg-primary/90 active:translate-y-0">
                <span class="text-base leading-none">+</span>
                <span>New Chat</span>
            </button>

            <div class="rounded-3xl border border-muted/10 bg-background p-4 dark:border-muted-dark/10 dark:bg-background-dark">
                <label for="chat-search" class="mb-2 block text-xs uppercase tracking-[.24em] text-muted dark:text-muted-dark">Search chats</label>
                <input id="chat-search" type="text" placeholder="Search conversations..." class="w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark" />
            </div>

            <div class="flex-1 overflow-hidden rounded-[2rem] border border-muted/10 bg-background p-4 dark:border-muted-dark/10 dark:bg-background-dark">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-[.24em] text-muted dark:text-muted-dark">Chat History</h2>
                    <span class="rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary">{{ $chatSessions->count() }}</span>
                </div>

                <div id="chat-session-list" class="h-[42vh] space-y-5 overflow-y-auto pr-1 lg:h-[56vh]">
                    @if($chatSessions->isEmpty())
                        <x-empty-state
                            icon="💬"
                            title="No chats yet"
                            message="Start a conversation with AI to get help with your projects, tasks, and more."
                            actionText="New Chat"
                            actionHref="#"
                            :showAction="false"
                        />
                    @else
                        @foreach([
                            'Today' => $todaySessions,
                            'Yesterday' => $yesterdaySessions,
                            'Older' => $olderSessions,
                        ] as $groupLabel => $groupSessions)
                            @if($groupSessions->isNotEmpty())
                                <div class="space-y-2">
                                    <p class="text-xs font-semibold uppercase tracking-[.22em] text-muted dark:text-muted-dark">{{ $groupLabel }}</p>
                                    @foreach($groupSessions as $session)
                                        @php
                                            $isActive = $activeSessionId === (int) $session->id;
                                            $messageCount = (int) ($session->messages_count ?? 0);
                                        @endphp
                                        <article class="chat-session-item group rounded-2xl border {{ $isActive ? 'border-primary/30 bg-primary/5 shadow-md shadow-primary/10' : 'border-transparent bg-muted/5 hover:border-primary/20 hover:bg-primary/5 dark:bg-muted-dark/20 dark:hover:bg-primary/10' }} px-3 py-3 transition"
                                            data-chat-title="{{ strtolower($session->title) }}"
                                            data-chat-id="{{ $session->id }}">
                                            <div class="flex items-start gap-2">
                                                <a href="{{ route('chat.sessions.show', $session) }}" class="min-w-0 flex-1">
                                                    <p class="truncate text-sm font-semibold text-foreground dark:text-foreground-dark">{{ $session->title }}</p>
                                                    <p class="mt-1 text-xs text-muted dark:text-muted-dark">
                                                        {{ $session->updated_at?->format('M d, g:i A') }}
                                                        <span class="mx-1">-</span>
                                                        {{ $messageCount }} {{ \Illuminate\Support\Str::plural('message', $messageCount) }}
                                                    </p>
                                                </a>
                                                <div class="relative">
                                                    <button type="button" class="session-actions-toggle inline-flex h-8 w-8 items-center justify-center rounded-xl border border-muted/20 bg-background text-muted transition hover:border-primary/30 hover:text-foreground dark:border-muted-dark/20 dark:bg-background-dark dark:text-muted-dark dark:hover:text-foreground-dark"
                                                        data-chat-id="{{ $session->id }}">
                                                        ...
                                                    </button>
                                                    <div class="session-actions-menu absolute right-0 top-10 z-20 hidden min-w-[140px] rounded-xl border border-muted/20 bg-background p-1.5 shadow-xl dark:border-muted-dark/20 dark:bg-background-dark">
                                                        <button type="button" class="rename-session-button flex w-full items-center rounded-lg px-3 py-2 text-left text-sm text-foreground transition hover:bg-primary/10 dark:text-foreground-dark"
                                                            data-chat-id="{{ $session->id }}"
                                                            data-chat-title="{{ $session->title }}">
                                                            Rename
                                                        </button>
                                                        <button type="button" class="delete-session-button flex w-full items-center rounded-lg px-3 py-2 text-left text-sm text-danger transition hover:bg-danger/10"
                                                            data-chat-id="{{ $session->id }}"
                                                            data-chat-title="{{ $session->title }}">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </aside>

    <main class="relative flex min-h-[calc(100vh-14rem)] flex-col overflow-hidden rounded-[2rem] border border-muted/10 bg-background-secondary shadow-card backdrop-blur-xl dark:border-muted-dark/10 dark:bg-background-secondary-dark">
        <div class="flex-1 overflow-hidden p-4 sm:p-6 lg:p-8">
            @if(isset($chatSession))
                <div class="flex h-full flex-col rounded-[2rem] border border-muted/10 bg-background p-4 shadow-sm dark:border-muted-dark/10 dark:bg-background-dark sm:p-5 lg:p-6">
                    <div class="mb-4 flex items-center justify-between gap-4 border-b border-muted/10 pb-4 dark:border-muted-dark/10">
                        <div>
                            <p class="text-sm uppercase tracking-[.2em] text-muted dark:text-muted-dark">Active Session</p>
                            <h2 class="mt-1 text-xl font-semibold text-foreground dark:text-foreground-dark sm:text-2xl">{{ $chatSession->title }}</h2>
                        </div>
                        <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">{{ (int) ($chatSession->messages_count ?? 0) }} messages</span>
                    </div>

                    <div id="chat-messages" class="flex-1 space-y-4 overflow-y-auto pr-1 pb-2 sm:pr-2 sm:pb-4" style="max-height: calc(100vh - 26rem);">
                        @foreach($chatSession->messages as $message)
                            @if($message->sender === 'user')
                                <article class="user-message-card message-enter ml-auto w-fit max-w-[92%] rounded-[1.5rem] bg-primary px-4 py-3 text-sm text-primary-foreground shadow-lg sm:max-w-[82%] sm:px-5 sm:py-4">
                                    <p class="whitespace-pre-line text-sm sm:text-base leading-6 font-medium">{{ $message->message }}</p>
                                    <p class="mt-2 text-right text-xs text-primary-foreground/80 font-medium">{{ $message->created_at?->format('g:i A') }}</p>
                                </article>
                            @else
                                <article class="ai-message-card message-enter flex max-w-[95%] items-start gap-3 rounded-[1.5rem] border border-muted/15 bg-gradient-to-br from-muted/10 to-background p-3 shadow-sm sm:max-w-[86%] sm:p-4 dark:border-muted-dark/15 dark:from-muted-dark/25 dark:to-background-dark">
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-primary/10 text-xs font-semibold text-primary flex-shrink-0">AI</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="whitespace-pre-line text-sm sm:text-base leading-6 font-medium text-foreground dark:text-foreground-dark">{{ $message->message }}</p>
                                        <p class="mt-2 text-xs text-muted dark:text-muted-dark font-medium">{{ $message->created_at?->format('g:i A') }}</p>
                                    </div>
                                </article>
                            @endif
                        @endforeach
                    </div>
                </div>
            @else
                <div class="flex h-full items-center justify-center">
                    <div class="max-w-lg rounded-[2rem] border border-muted/10 bg-background p-8 text-center shadow-sm dark:border-muted-dark/10 dark:bg-background-dark">
                        <p class="text-sm uppercase tracking-[.2em] text-muted dark:text-muted-dark">AI Workspace</p>
                        <h2 class="mt-2 text-3xl font-semibold text-foreground dark:text-foreground-dark">Pick a chat from the sidebar</h2>
                        <p class="mt-3 text-sm leading-7 text-muted dark:text-muted-dark">Open any previous session, or create a new one to continue your project conversation.</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="sticky bottom-0 border-t border-muted/10 bg-background-secondary px-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 sm:px-4 sm:pt-4 dark:border-muted-dark/10 dark:bg-background-secondary-dark">
            @if(isset($chatSession))
                <form id="chat-input-form" class="flex flex-col gap-2.5 sm:flex-row sm:items-end sm:gap-3">
                    <div class="relative flex-1">
                        <textarea id="chat-input" rows="1" placeholder="Type your message..." class="w-full min-h-[62px] resize-none rounded-3xl border border-muted/20 bg-background px-4 py-3 text-[15px] text-foreground outline-none transition duration-200 focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark sm:min-h-[68px] sm:text-sm"></textarea>
                    </div>
                    <button id="chat-send-button" type="button" class="inline-flex h-12 min-h-[46px] w-full items-center justify-center rounded-3xl bg-primary px-6 text-sm font-semibold text-primary-foreground shadow-lg shadow-primary/20 transition duration-200 hover:-translate-y-0.5 hover:bg-primary/90 active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-60 sm:h-14 sm:w-auto">
                        Send
                    </button>
                </form>
                <p id="chat-feedback" class="mt-2 hidden rounded-xl border px-3 py-2 text-xs"></p>
            @else
                <div class="rounded-2xl border border-dashed border-muted/30 px-4 py-3 text-sm text-muted dark:border-muted-dark/30 dark:text-muted-dark">
                    Create or open a chat to start sending messages.
                </div>
            @endif
        </div>
    </main>
</div>

<div id="session-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
    <div class="w-full max-w-md rounded-3xl border border-muted/20 bg-background p-6 shadow-2xl dark:border-muted-dark/20 dark:bg-background-dark">
        <h3 id="session-modal-title" class="text-lg font-semibold text-foreground dark:text-foreground-dark">New Chat</h3>
        <p id="session-modal-subtitle" class="mt-1 text-sm text-muted dark:text-muted-dark">Choose a title for this session.</p>
        <input id="session-modal-input" type="text" maxlength="150" class="mt-4 w-full rounded-2xl border border-muted/20 bg-background px-4 py-3 text-sm text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 dark:border-muted-dark/20 dark:bg-background-dark dark:text-foreground-dark" />
        <div class="mt-5 flex justify-end gap-2">
            <button id="session-modal-cancel" type="button" class="inline-flex min-h-[44px] items-center justify-center rounded-2xl border border-muted/20 px-4 py-2 text-sm font-semibold text-foreground transition hover:bg-muted/10 dark:border-muted-dark/20 dark:text-foreground-dark dark:hover:bg-muted-dark/10">Cancel</button>
            <button id="session-modal-save" type="button" class="inline-flex min-h-[44px] items-center justify-center rounded-2xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">Save</button>
        </div>
    </div>
</div>

<div id="delete-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
    <div class="w-full max-w-md rounded-3xl border border-danger/20 bg-background p-6 shadow-2xl dark:bg-background-dark">
        <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Delete Session</h3>
        <p id="delete-modal-copy" class="mt-2 text-sm text-muted dark:text-muted-dark"></p>
        <div class="mt-5 flex justify-end gap-2">
            <button id="delete-modal-cancel" type="button" class="inline-flex min-h-[44px] items-center justify-center rounded-2xl border border-muted/20 px-4 py-2 text-sm font-semibold text-foreground transition hover:bg-muted/10 dark:border-muted-dark/20 dark:text-foreground-dark dark:hover:bg-muted-dark/10">Cancel</button>
            <button id="delete-modal-confirm" type="button" class="inline-flex min-h-[44px] items-center justify-center rounded-2xl bg-danger px-4 py-2 text-sm font-semibold text-white transition hover:bg-danger/90">Delete</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const sessionModal = document.getElementById('session-modal');
        const sessionModalTitle = document.getElementById('session-modal-title');
        const sessionModalSubtitle = document.getElementById('session-modal-subtitle');
        const sessionModalInput = document.getElementById('session-modal-input');
        const sessionModalCancel = document.getElementById('session-modal-cancel');
        const sessionModalSave = document.getElementById('session-modal-save');
        const deleteModal = document.getElementById('delete-modal');
        const deleteModalCopy = document.getElementById('delete-modal-copy');
        const deleteModalCancel = document.getElementById('delete-modal-cancel');
        const deleteModalConfirm = document.getElementById('delete-modal-confirm');
        const searchInput = document.getElementById('chat-search');
        const newChatButton = document.getElementById('new-chat-button');
        let modalMode = 'create';
        let targetSessionId = null;

        const routeTemplates = {
            create: @json(route('chat.sessions.store')),
            show: @json(route('chat.sessions.show', ['chatSession' => '__ID__'])),
            rename: @json(route('chat.sessions.rename', ['chatSession' => '__ID__'])),
            delete: @json(route('chat.sessions.delete', ['chatSession' => '__ID__'])),
            sendMessage: @json(isset($chatSession) ? route('chat.messages.send', ['chatSession' => $chatSession->id]) : null),
        };

        function withId(template, id) {
            return template.replace('__ID__', String(id));
        }

        function openSessionModal(mode, id, title) {
            modalMode = mode;
            targetSessionId = id || null;
            sessionModalTitle.textContent = mode === 'rename' ? 'Rename Session' : 'New Chat';
            sessionModalSubtitle.textContent = mode === 'rename'
                ? 'Update this chat title.'
                : 'Choose a title for your new chat.';
            sessionModalInput.value = title || '';
            sessionModal.classList.remove('hidden');
            sessionModal.classList.add('flex');
            window.setTimeout(() => sessionModalInput.focus(), 50);
        }

        function closeSessionModal() {
            sessionModal.classList.add('hidden');
            sessionModal.classList.remove('flex');
            targetSessionId = null;
            sessionModalInput.value = '';
        }

        function openDeleteModal(id, title) {
            targetSessionId = id;
            deleteModalCopy.textContent = 'Delete "' + title + '" permanently? This cannot be undone.';
            deleteModal.classList.remove('hidden');
            deleteModal.classList.add('flex');
        }

        function closeDeleteModal() {
            deleteModal.classList.add('hidden');
            deleteModal.classList.remove('flex');
            targetSessionId = null;
        }

        function closeAllMenus() {
            document.querySelectorAll('.session-actions-menu').forEach((menu) => {
                menu.classList.add('hidden');
            });
        }

        function wireSessionActions() {
            document.querySelectorAll('.session-actions-toggle').forEach((button) => {
                button.addEventListener('click', function (event) {
                    event.stopPropagation();
                    const menu = button.nextElementSibling;
                    const wasHidden = menu.classList.contains('hidden');
                    closeAllMenus();
                    if (wasHidden) {
                        menu.classList.remove('hidden');
                    }
                });
            });

            document.querySelectorAll('.rename-session-button').forEach((button) => {
                button.addEventListener('click', function () {
                    closeAllMenus();
                    openSessionModal('rename', button.dataset.chatId, button.dataset.chatTitle);
                });
            });

            document.querySelectorAll('.delete-session-button').forEach((button) => {
                button.addEventListener('click', function () {
                    closeAllMenus();
                    openDeleteModal(button.dataset.chatId, button.dataset.chatTitle);
                });
            });
        }

        document.addEventListener('click', function () {
            closeAllMenus();
        });

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = searchInput.value.trim().toLowerCase();
                document.querySelectorAll('.chat-session-item').forEach((item) => {
                    const title = item.dataset.chatTitle || '';
                    item.classList.toggle('hidden', query.length > 0 && !title.includes(query));
                });
            });
        }

        if (newChatButton) {
            newChatButton.addEventListener('click', function () {
                openSessionModal('create', null, '');
            });
        }

        sessionModalCancel.addEventListener('click', closeSessionModal);
        deleteModalCancel.addEventListener('click', closeDeleteModal);

        sessionModalSave.addEventListener('click', async function () {
            const title = sessionModalInput.value.trim();
            if (!title) {
                sessionModalInput.focus();
                return;
            }

            sessionModalSave.disabled = true;
            sessionModalSave.textContent = 'Saving...';

            try {
                const url = modalMode === 'rename'
                    ? withId(routeTemplates.rename, targetSessionId)
                    : routeTemplates.create;
                const method = modalMode === 'rename' ? 'PATCH' : 'POST';

                const response = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ title }),
                });

                if (!response.ok) {
                    throw new Error('Request failed');
                }

                const payload = await response.json();
                const targetId = modalMode === 'rename' ? targetSessionId : payload.data.id;
                window.location.href = withId(routeTemplates.show, targetId);
            } catch (error) {
                alert('Unable to save session. Please try again.');
            } finally {
                sessionModalSave.disabled = false;
                sessionModalSave.textContent = 'Save';
            }
        });

        deleteModalConfirm.addEventListener('click', async function () {
            if (!targetSessionId) {
                return;
            }

            deleteModalConfirm.disabled = true;
            deleteModalConfirm.textContent = 'Deleting...';

            try {
                const response = await fetch(withId(routeTemplates.delete, targetSessionId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Request failed');
                }

                window.location.href = @json(route('chat'));
            } catch (error) {
                alert('Unable to delete session. Please try again.');
            } finally {
                deleteModalConfirm.disabled = false;
                deleteModalConfirm.textContent = 'Delete';
            }
        });

        wireSessionActions();

        const form = document.getElementById('chat-input-form');
        const input = document.getElementById('chat-input');
        const sendButton = document.getElementById('chat-send-button');
        const messages = document.getElementById('chat-messages');
        const feedback = document.getElementById('chat-feedback');
        let slowResponseTimer = null;

        if (form && input && sendButton && messages && routeTemplates.sendMessage) {
            function appendUserMessage(text) {
                const row = document.createElement('article');
                row.className = 'user-message-card message-enter ml-auto w-fit max-w-[92%] rounded-[1.5rem] bg-primary px-4 py-3 text-sm text-primary-foreground shadow-lg sm:max-w-[82%] sm:px-5 sm:py-4';
                row.innerHTML = '<p class="whitespace-pre-line text-sm sm:text-base leading-6 font-medium"></p><p class="mt-2 text-right text-xs text-primary-foreground/80 font-medium"></p>';
                row.querySelector('p').textContent = text;
                row.querySelectorAll('p')[1].textContent = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
                messages.appendChild(row);
            }

            function appendAiMessage(text) {
                const row = document.createElement('article');
                row.className = 'ai-message-card message-enter flex max-w-[95%] items-start gap-3 rounded-[1.5rem] border border-muted/15 bg-gradient-to-br from-muted/10 to-background p-3 shadow-sm sm:max-w-[86%] sm:p-4 dark:border-muted-dark/15 dark:from-muted-dark/25 dark:to-background-dark';
                row.innerHTML = '<span class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-primary/10 text-xs font-semibold text-primary flex-shrink-0">AI</span><div class="min-w-0 flex-1"><p class="whitespace-pre-line text-sm sm:text-base leading-6 font-medium text-foreground dark:text-foreground-dark"></p><p class="mt-2 text-xs text-muted dark:text-muted-dark font-medium"></p></div>';
                row.querySelector('p').textContent = text;
                row.querySelectorAll('p')[1].textContent = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
                messages.appendChild(row);
            }

            function showFeedback(message, type = 'error') {
                if (!feedback) {
                    return;
                }

                const typeClasses = type === 'info'
                    ? 'border-primary/20 bg-primary/10 text-primary'
                    : 'border-danger/20 bg-danger/10 text-danger';

                feedback.className = 'mt-2 rounded-xl border px-3 py-2 text-xs ' + typeClasses;
                feedback.textContent = message;
                feedback.classList.remove('hidden');
            }

            function clearFeedback() {
                if (!feedback) {
                    return;
                }

                feedback.classList.add('hidden');
                feedback.textContent = '';
            }

            function scrollMessagesToBottom(smooth = true) {
                messages.scrollTo({
                    top: messages.scrollHeight,
                    behavior: smooth ? 'smooth' : 'auto',
                });
            }

            function resizeTextarea() {
                input.style.height = 'auto';
                input.style.height = Math.min(input.scrollHeight, 220) + 'px';
            }

            input.addEventListener('input', resizeTextarea);
            resizeTextarea();
            scrollMessagesToBottom(false);

            sendButton.addEventListener('click', async function () {
                const message = input.value.trim();
                if (!message) {
                    showFeedback('Please enter a message before sending.', 'error');
                    input.focus();
                    return;
                }

                clearFeedback();
                sendButton.disabled = true;
                sendButton.textContent = 'Sending...';
                slowResponseTimer = window.setTimeout(() => {
                    showFeedback('Response is taking longer than usual. Still working on it...', 'info');
                }, 6000);

                try {
                    const response = await fetch(routeTemplates.sendMessage, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ message }),
                    });

                    if (!response.ok) {
                        if (response.status === 429) {
                            throw new Error('RATE_LIMIT');
                        }
                        if (response.status >= 500 || response.status === 503) {
                            throw new Error('API_UNAVAILABLE');
                        }
                        throw new Error('REQUEST_FAILED');
                    }

                    const payload = await response.json();
                    if (payload?.success === false) {
                        throw new Error('API_UNAVAILABLE');
                    }

                    const userText = payload?.data?.user_message?.message || message;
                    const aiText = payload?.data?.ai_message?.message || 'I could not generate a response right now. Please try again.';

                    appendUserMessage(userText);
                    appendAiMessage(aiText);
                    scrollMessagesToBottom(true);
                    input.value = '';
                    resizeTextarea();
                    clearFeedback();
                } catch (error) {
                    if (error?.message === 'RATE_LIMIT') {
                        showFeedback('You are sending messages too quickly. Please wait a moment and try again.', 'error');
                    } else if (error?.message === 'API_UNAVAILABLE') {
                        showFeedback('AI service is temporarily unavailable. Please try again shortly.', 'error');
                    } else if (error instanceof TypeError) {
                        showFeedback('Network error detected. Check your connection and retry.', 'error');
                    } else {
                        showFeedback('Unable to send your message right now. Please try again.', 'error');
                    }
                } finally {
                    if (slowResponseTimer) {
                        window.clearTimeout(slowResponseTimer);
                        slowResponseTimer = null;
                    }
                    sendButton.disabled = false;
                    sendButton.textContent = 'Send';
                    input.focus();
                }
            });
        }
    });
</script>
@endsection
