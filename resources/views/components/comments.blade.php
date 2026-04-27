@php
    use App\Models\Comment;
    use App\Models\User;
@endphp

<!-- Comments Section -->
<div class="surface-card interactive-lift mt-8">
    <div class="p-6 border-b border-muted/10">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark">
                Comments 
                <span class="text-sm text-muted dark:text-muted-dark ml-2">
                    ({{ $comments->count() }})
                </span>
            </h2>
            
            @if($project->userCanComment(auth()->user()))
                <button onclick="showCommentForm()" class="btn-brand text-sm">
                    <span class="mr-2">+</span>
                    Add Comment
                </button>
            @endif
        </div>
    </div>

    <!-- Comment Form -->
    <div id="comment-form" class="p-6 border-b border-muted/10 hidden">
        <form id="new-comment-form" class="space-y-4">
            <div>
                <textarea 
                    id="comment-content"
                    name="content" 
                    rows="3" 
                    placeholder="Share your thoughts about this project..."
                    class="input-brand w-full resize-none"
                    onkeyup="handleMentionInput(this)"
                    onkeydown="handleCommentKeydown(event)"
                ></textarea>
                <div id="mention-suggestions" class="hidden absolute z-50 w-full max-w-sm bg-background dark:bg-background-dark border border-muted/20 rounded-xl shadow-xl max-h-48 overflow-y-auto">
                    <!-- Mention suggestions will be inserted here -->
                </div>
            </div>
            
            <div class="flex items-center justify-between">
                <div class="text-xs text-muted dark:text-muted-dark">
                    <span id="char-count">0</span>/2000 characters
                </div>
                
                <div class="flex gap-2">
                    <button type="button" onclick="hideCommentForm()" class="btn-brand-muted text-sm">
                        Cancel
                    </button>
                    <button type="submit" class="btn-brand text-sm" id="submit-comment-btn">
                        Post Comment
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Comments List -->
    <div id="comments-container" class="p-6">
        @if($comments->count() > 0)
            <div class="space-y-6" id="comments-list">
                @foreach($comments as $comment)
                    @include('components.comment-item', ['comment' => $comment, 'level' => 0])
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <div class="text-4xl mb-4">💬</div>
                <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark mb-2">No Comments Yet</h3>
                <p class="text-sm text-muted dark:text-muted-dark mb-4">
                    Be the first to share your thoughts about this project.
                </p>
                @if($project->userCanComment(auth()->user()))
                    <button onclick="showCommentForm()" class="btn-brand text-sm">
                        Start the Conversation
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>

<script>
let currentEditingComment = null;
let currentReplyToComment = null;
let mentionTimeout = null;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize comment form
    const commentForm = document.getElementById('new-comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', handleCommentSubmit);
    }

    // Initialize character counter
    const commentContent = document.getElementById('comment-content');
    if (commentContent) {
        commentContent.addEventListener('input', updateCharCount);
    }
});

function showCommentForm(replyToComment = null) {
    currentReplyToComment = replyToComment;
    const form = document.getElementById('comment-form');
    const content = document.getElementById('comment-content');
    
    form.classList.remove('hidden');
    content.focus();
    
    if (replyToComment) {
        content.placeholder = `Replying to ${replyToComment->user->name}...`;
        content.value = `@${replyToComment->user->name} `;
    }
    
    updateCharCount();
}

function hideCommentForm() {
    const form = document.getElementById('comment-form');
    const content = document.getElementById('comment-content');
    
    form.classList.add('hidden');
    content.value = '';
    content.placeholder = 'Share your thoughts about this project...';
    currentReplyToComment = null;
    currentEditingComment = null;
    
    hideMentionSuggestions();
}

function updateCharCount() {
    const content = document.getElementById('comment-content');
    const charCount = document.getElementById('char-count');
    
    if (content && charCount) {
        const count = content.value.length;
        charCount.textContent = count;
        
        if (count > 2000) {
            charCount.classList.add('text-danger');
        } else {
            charCount.classList.remove('text-danger');
        }
    }
}

function handleCommentKeydown(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        document.getElementById('new-comment-form').dispatchEvent(new Event('submit'));
    }
}

async function handleCommentSubmit(event) {
    event.preventDefault();
    
    const content = document.getElementById('comment-content').value.trim();
    const submitBtn = document.getElementById('submit-comment-btn');
    
    if (!content) {
        return;
    }
    
    if (content.length > 2000) {
        alert('Comment cannot exceed 2000 characters.');
        return;
    }
    
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin inline-block mr-2"></span>Posting...';
    
    try {
        const url = currentEditingComment 
            ? `/comments/${currentEditingComment}`
            : '/comments';
        
        const method = currentEditingComment ? 'PATCH' : 'POST';
        
        const data = {
            content: content,
            project_id: {{ $project->id }},
        };
        
        if (currentReplyToComment) {
            data.parent_id = currentReplyToComment.id;
        }
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            if (currentEditingComment) {
                updateCommentInDOM(result.comment);
            } else {
                addCommentToDOM(result.comment);
            }
            
            hideCommentForm();
        } else {
            alert(result.message || 'Failed to post comment.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while posting the comment.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

function addCommentToDOM(comment) {
    const commentsList = document.getElementById('comments-list');
    const noCommentsMessage = document.querySelector('.text-center.py-8');
    
    if (noCommentsMessage) {
        noCommentsMessage.remove();
    }
    
    const commentHTML = `
        <div class="space-y-6" id="comment-${comment.id}">
            ${createCommentHTML(comment, 0)}
        </div>
    `;
    
    if (currentReplyToComment) {
        // Add as reply to existing comment
        const repliesContainer = document.querySelector(`#comment-${currentReplyToComment.id} .replies-container`);
        if (repliesContainer) {
            repliesContainer.insertAdjacentHTML('beforeend', createCommentHTML(comment, 1));
        }
    } else {
        // Add as top-level comment
        commentsList.insertAdjacentHTML('afterbegin', commentHTML);
    }
}

function updateCommentInDOM(comment) {
    const commentElement = document.querySelector(`#comment-${comment.id}`);
    if (commentElement) {
        commentElement.innerHTML = createCommentHTML(comment, comment.parent_id ? 1 : 0);
    }
}

function createCommentHTML(comment, level = 0) {
    const marginLeft = level > 0 ? 'ml-8' : '';
    const isOwn = comment.user.id === {{ auth()->user()->id }};
    
    return `
        <div class="${marginLeft} space-y-4" id="comment-${comment.id}">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold text-sm">
                    ${comment.user.name.charAt(0)}
                </div>
                
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-foreground dark:text-foreground-dark">
                                ${comment.user.name}
                            </span>
                            <span class="text-xs text-muted dark:text-muted-dark">
                                ${comment.time_ago}
                                ${comment.is_edited ? ' (edited)' : ''}
                            </span>
                        </div>
                        
                        ${isOwn ? `
                            <div class="flex items-center gap-2">
                                <button onclick="editComment(${comment.id}, '${comment.content.replace(/'/g, "\\'")}')" class="text-xs text-muted hover:text-foreground">
                                    Edit
                                </button>
                                <button onclick="deleteComment(${comment.id})" class="text-xs text-danger hover:text-danger/80">
                                    Delete
                                </button>
                            </div>
                        ` : ''}
                    </div>
                    
                    <div class="text-sm text-foreground dark:text-foreground-dark mb-3">
                        ${comment.formatted_content}
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <button onclick="showCommentForm(${comment.id})" class="text-xs text-muted hover:text-primary">
                            Reply
                        </button>
                        ${comment.replies_count > 0 ? `
                            <button onclick="toggleReplies(${comment.id})" class="text-xs text-muted hover:text-primary">
                                ${comment.replies_count} ${comment.replies_count === 1 ? 'Reply' : 'Replies'}
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
            
            <div class="replies-container space-y-4">
                ${comment.replies ? comment.replies.map(reply => createCommentHTML(reply, level + 1)).join('') : ''}
            </div>
        </div>
    `;
}

function editComment(commentId, content) {
    currentEditingComment = commentId;
    const form = document.getElementById('comment-form');
    const commentContent = document.getElementById('comment-content');
    const submitBtn = document.getElementById('submit-comment-btn');
    
    form.classList.remove('hidden');
    commentContent.value = content;
    commentContent.focus();
    submitBtn.textContent = 'Update Comment';
    
    updateCharCount();
}

async function deleteComment(commentId) {
    if (!confirm('Are you sure you want to delete this comment?')) {
        return;
    }
    
    try {
        const response = await fetch(`/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            const commentElement = document.querySelector(`#comment-${commentId}`);
            if (commentElement) {
                commentElement.remove();
            }
        } else {
            alert(result.message || 'Failed to delete comment.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while deleting the comment.');
    }
}

function toggleReplies(commentId) {
    const repliesContainer = document.querySelector(`#comment-${commentId} .replies-container`);
    if (repliesContainer) {
        repliesContainer.classList.toggle('hidden');
    }
}

// Mention functionality
function handleMentionInput(textarea) {
    const content = textarea.value;
    const cursorPos = textarea.selectionStart;
    const textBeforeCursor = content.substring(0, cursorPos);
    
    // Check if we're currently typing a mention
    const mentionMatch = textBeforeCursor.match(/@(\w*)$/);
    
    if (mentionMatch) {
        const searchTerm = mentionMatch[1];
        
        // Clear existing timeout
        if (mentionTimeout) {
            clearTimeout(mentionTimeout);
        }
        
        // Debounce search
        mentionTimeout = setTimeout(() => {
            if (searchTerm.length >= 2) {
                searchMentions(searchTerm, textarea);
            } else {
                hideMentionSuggestions();
            }
        }, 300);
    } else {
        hideMentionSuggestions();
    }
}

async function searchMentions(searchTerm, textarea) {
    try {
        const response = await fetch(`/comments/search-users?query=${searchTerm}`);
        const result = await response.json();
        
        if (result.success) {
            showMentionSuggestions(result.users, textarea);
        }
    } catch (error) {
        console.error('Error searching users:', error);
    }
}

function showMentionSuggestions(users, textarea) {
    const suggestionsDiv = document.getElementById('mention-suggestions');
    const rect = textarea.getBoundingClientRect();
    
    suggestionsDiv.innerHTML = users.map(user => `
        <div class="mention-suggestion px-4 py-2 hover:bg-muted/10 cursor-pointer flex items-center gap-3" onclick="selectMention('${user.name}', '${user.id}')">
            <div class="w-6 h-6 rounded-full bg-muted/20 flex items-center justify-center text-xs font-medium">
                ${user.name.charAt(0)}
            </div>
            <div>
                <div class="text-sm font-medium text-foreground dark:text-foreground-dark">${user.name}</div>
                <div class="text-xs text-muted dark:text-muted-dark">${user.email}</div>
            </div>
        </div>
    `).join('');
    
    suggestionsDiv.style.top = `${rect.bottom + window.scrollY + 5}px`;
    suggestionsDiv.style.left = `${rect.left + window.scrollX}px`;
    suggestionsDiv.classList.remove('hidden');
}

function hideMentionSuggestions() {
    const suggestionsDiv = document.getElementById('mention-suggestions');
    suggestionsDiv.classList.add('hidden');
    suggestionsDiv.innerHTML = '';
}

function selectMention(username, userId) {
    const textarea = document.getElementById('comment-content');
    const content = textarea.value;
    const cursorPos = textarea.selectionStart;
    
    // Find the @mention and replace it
    const textBeforeCursor = content.substring(0, cursorPos);
    const textAfterCursor = content.substring(cursorPos);
    
    const mentionMatch = textBeforeCursor.match(/@(\w*)$/);
    if (mentionMatch) {
        const newContent = textBeforeCursor.replace(/@(\w*)$/, `@${username} `) + textAfterCursor;
        textarea.value = newContent;
        
        // Set cursor position after the mention
        const newCursorPos = textBeforeCursor.length - mentionMatch[0].length + username.length + 2;
        textarea.setSelectionRange(newCursorPos, newCursorPos);
    }
    
    hideMentionSuggestions();
    updateCharCount();
    textarea.focus();
}
</script>
