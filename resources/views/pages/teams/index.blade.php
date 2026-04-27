@extends('layouts.app')

@section('title', 'Teams')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl md:text-4xl font-bold text-foreground dark:text-foreground-dark">Teams</h1>
            <p class="text-muted mt-2">Collaborate with your team members on projects</p>
        </div>
        
        @if(auth()->user()->canCreateMoreTeams())
            <button onclick="showCreateTeamModal()" class="btn-brand">
                <span class="mr-2">+</span>
                Create Team
            </button>
        @endif
    </div>

    <!-- Pending Invitations -->
    @if($pendingInvitations->count() > 0)
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark mb-4">Pending Invitations</h2>
            <div class="grid gap-4">
                @foreach($pendingInvitations as $invitation)
                    <div class="surface-card interactive-lift p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold">
                                    {{ substr($invitation->team->name, 0, 1) }}
                                </div>
                                <div>
                                    <h3 class="font-semibold text-foreground dark:text-foreground-dark">{{ $invitation->team->name }}</h3>
                                    <p class="text-sm text-muted dark:text-muted-dark">
                                        Invited by {{ $invitation->invitedBy->name }} as {{ $invitation->getRoleLabel() }}
                                    </p>
                                    @if($invitation->message)
                                        <p class="text-sm text-muted dark:text-muted-dark mt-1">"{{ $invitation->message }}"</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <button onclick="acceptInvitation('{{ $invitation->token }}')" class="btn-brand text-sm">
                                    Accept
                                </button>
                                <button onclick="declineInvitation('{{ $invitation->token }}')" class="btn-brand-muted text-sm">
                                    Decline
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Your Teams -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark mb-4">Your Teams</h2>
        
        @if($teams->count() > 0)
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($teams as $team)
                    <div class="surface-card interactive-lift p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold">
                                    {{ substr($team->name, 0, 1) }}
                                </div>
                                <div>
                                    <h3 class="font-semibold text-foreground dark:text-foreground-dark">{{ $team->name }}</h3>
                                    <p class="text-sm text-muted dark:text-muted-dark">{{ $team->getMemberCount() }} members</p>
                                </div>
                            </div>
                            @if($team->isOwner(auth()->user()))
                                <span class="inline-flex px-2 py-1 rounded-full bg-warning/10 text-warning text-xs font-medium">
                                    Owner
                                </span>
                            @endif
                        </div>
                        
                        @if($team->description)
                            <p class="text-sm text-muted dark:text-muted-dark mb-4">{{ $team->description }}</p>
                        @endif
                        
                        <div class="flex items-center justify-between">
                            <div class="flex -space-x-2">
                                @foreach($team->getActiveMembers()->take(4) as $member)
                                    <div class="w-8 h-8 rounded-full bg-muted/20 border-2 border-background dark:border-background-dark flex items-center justify-center text-xs font-medium">
                                        {{ substr($member->user->name, 0, 1) }}
                                    </div>
                                @endforeach
                                @if($team->getMemberCount() > 4)
                                    <div class="w-8 h-8 rounded-full bg-muted/20 border-2 border-background dark:border-background-dark flex items-center justify-center text-xs font-medium">
                                        +{{ $team->getMemberCount() - 4 }}
                                    </div>
                                @endif
                            </div>
                            
                            <a href="{{ route('teams.show', $team) }}" class="btn-brand-muted text-sm">
                                View Team
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="surface-card p-12 text-center">
                <div class="text-4xl mb-4">👥</div>
                <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark mb-2">No Teams Yet</h3>
                <p class="text-sm text-muted dark:text-muted-dark mb-6">
                    Create your first team to start collaborating with others.
                </p>
                @if(auth()->user()->canCreateMoreTeams())
                    <button onclick="showCreateTeamModal()" class="btn-brand">
                        Create Your First Team
                    </button>
                @else
                    <a href="{{ route('pricing') }}" class="btn-brand">
                        Upgrade to Create Teams
                    </a>
                @endif
            </div>
        @endif
    </div>

    <!-- Owned Teams -->
    @if($ownedTeams->count() > 0)
        <div>
            <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark mb-4">Teams You Own</h2>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach($ownedTeams as $team)
                    <div class="surface-card interactive-lift p-6 border-l-4 border-warning">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-warning to-danger flex items-center justify-center text-white font-bold">
                                    👑
                                </div>
                                <div>
                                    <h3 class="font-semibold text-foreground dark:text-foreground-dark">{{ $team->name }}</h3>
                                    <p class="text-sm text-muted dark:text-muted-dark">{{ $team->getMemberCount() }} members</p>
                                </div>
                            </div>
                            <span class="inline-flex px-2 py-1 rounded-full bg-warning/10 text-warning text-xs font-medium">
                                Owner
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-sm text-muted dark:text-muted-dark">
                                <span>{{ $team->getPendingInvitationsCount() }} pending</span>
                            </div>
                            
                            <a href="{{ route('teams.show', $team) }}" class="btn-brand text-sm">
                                Manage Team
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Create Team Modal -->
<div id="create-team-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-background dark:bg-background-dark rounded-3xl border border-muted/20 p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark mb-4">Create New Team</h3>
        
        <form id="create-team-form">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-1">
                        Team Name
                    </label>
                    <input type="text" name="name" required
                           class="input-brand w-full" 
                           placeholder="Enter team name">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-1">
                        Description (Optional)
                    </label>
                    <textarea name="description" rows="3"
                              class="input-brand w-full" 
                              placeholder="What's this team about?"></textarea>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="hideCreateTeamModal()" 
                        class="flex-1 btn-brand-muted text-sm">
                    Cancel
                </button>
                <button type="submit" class="flex-1 btn-brand text-sm">
                    Create Team
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Create team form
    const createTeamForm = document.getElementById('create-team-form');
    const createTeamModal = document.getElementById('create-team-modal');
    
    createTeamForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin inline-block mr-2"></span>Creating...';
        
        try {
            const response = await fetch('{{ route("teams.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    name: formData.get('name'),
                    description: formData.get('description'),
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                window.location.href = result.redirect_url;
            } else {
                alert(result.message || 'Failed to create team.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while creating the team.');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    });
});

function showCreateTeamModal() {
    document.getElementById('create-team-modal').classList.remove('hidden');
}

function hideCreateTeamModal() {
    document.getElementById('create-team-modal').classList.add('hidden');
    document.getElementById('create-team-form').reset();
}

async function acceptInvitation(token) {
    try {
        const response = await fetch(`/teams/invitations/${token}/accept`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            window.location.href = result.redirect_url;
        } else {
            alert(result.message || 'Failed to accept invitation.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while accepting the invitation.');
    }
}

async function declineInvitation(token) {
    if (!confirm('Are you sure you want to decline this invitation?')) {
        return;
    }
    
    try {
        const response = await fetch(`/teams/invitations/${token}/decline`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            window.location.reload();
        } else {
            alert(result.message || 'Failed to decline invitation.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while declining the invitation.');
    }
}
</script>
@endsection
