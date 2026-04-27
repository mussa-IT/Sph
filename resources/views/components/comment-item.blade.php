@php
    use App\Models\Comment;
@endphp

<div class="space-y-4" style="margin-left: {{ $level * 2 }}rem;">
    <div class="flex items-start gap-3">
        <!-- User Avatar -->
        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
            {{ $comment->user->name->charAt(0) }}
        </div>
        
        <!-- Comment Content -->
        <div class="flex-1">
            <!-- Comment Header -->
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="font-medium text-foreground dark:text-foreground-dark">
                        {{ $comment->user->name }}
                    </span>
                    <span class="text-xs text-muted dark:text-muted-dark">
                        {{ $comment->getTimeAgo() }}
                        @if($comment->isEdited())
                            <span class="text-muted">(edited)</span>
                        @endif
                    </span>
                    
                    <!-- Role Badge -->
                    @if($comment->project->user_id === $comment->user_id)
                        <span class="inline-flex px-2 py-0.5 rounded-full bg-warning/10 text-warning text-xs font-medium">
                            Author
                        </span>
                    @endif
                </div>
                
                <!-- Action Buttons -->
                @if(auth()->check() && ($comment->user_id === auth()->user()->id || $comment->project->user_id === auth()->user()->id))
                    <div class="flex items-center gap-2">
                        @if($comment->user_id === auth()->user()->id)
                            <button onclick="editComment({{ $comment->id }}, '{{ $comment->content }}')" 
                                    class="text-xs text-muted hover:text-primary transition-colors">
                                Edit
                            </button>
                        @endif
                        
                        <button onclick="deleteComment({{ $comment->id }})" 
                                class="text-xs text-danger hover:text-danger/80 transition-colors">
                            Delete
                        </button>
                    </div>
                @endif
            </div>
            
            <!-- Comment Body -->
            <div class="text-sm text-foreground dark:text-foreground-dark mb-3 leading-relaxed">
                {!! $comment->getFormattedContent() !!}
            </div>
            
            <!-- Comment Actions -->
            <div class="flex items-center gap-4">
                @if(auth()->check() && $comment->project->userCanComment(auth()->user()))
                    <button onclick="showCommentForm({{ $comment->id }})" 
                            class="text-xs text-muted hover:text-primary transition-colors flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.003 9.003 0 01-4.951-4.95l-2.051-2.05a9.001 9.001 0 00-6.097-3.485 9.001 9.001 0 01-3.485-6.097l-2.051-2.05A9.001 9.001 0 0112 3z"/>
                        </svg>
                        Reply
                    </button>
                @endif
                
                @if($comment->getRepliesCount() > 0)
                    <button onclick="toggleReplies({{ $comment->id }})" 
                            class="text-xs text-muted hover:text-primary transition-colors flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5z"/>
                        </svg>
                        {{ $comment->getRepliesCount() }} {{ $comment->getRepliesCount() === 1 ? 'Reply' : 'Replies' }}
                    </button>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Replies -->
    @if($comment->replies->count() > 0)
        <div class="replies-container space-y-4" id="replies-{{ $comment->id }}">
            @foreach($comment->replies as $reply)
                @include('components.comment-item', ['comment' => $reply, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>
