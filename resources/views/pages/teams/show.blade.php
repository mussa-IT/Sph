@extends('layouts.app')

@section('title', $team->name . ' - Team')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- Team Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold text-2xl">
                    {{ substr($team->name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-foreground dark:text-foreground-dark">{{ $team->name }}</h1>
                    <p class="text-muted dark:text-muted-dark">
                        {{ $team->getMemberCount() }} members • Created {{ $team->created_at->format('M j, Y') }}
                    </p>
                    @if($team->isOwner(auth()->user()))
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-warning/10 text-warning text-xs font-medium mt-1">
                            <span class="w-2 h-2 rounded-full bg-warning"></span>
                            Team Owner
                        </span>
                    @endif
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                @if($canManageTeam)
                    <button onclick="showEditTeamModal()" class="btn-brand-muted text-sm">
                        Edit Team
                    </button>
                @endif
                @if($canInviteMembers)
                    <button onclick="showInviteModal()" class="btn-brand text-sm">
                        <span class="mr-2">+</span>
                        Invite Member
                    </button>
                @endif
                @if(!$team->isOwner(auth()->user()))
                    <button onclick="confirmLeaveTeam()" class="btn-brand-muted text-sm text-danger">
                        Leave Team
                    </button>
                @endif
            </div>
        </div>
        
        @if($team->description)
            <p class="text-muted dark:text-muted-dark">{{ $team->description }}</p>
        @endif
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Team Stats -->
            <div class="surface-card interactive-lift p-6">
                <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark mb-4">Team Overview</h2>
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="text-center p-4 rounded-xl bg-muted/10">
                        <div class="text-2xl font-bold text-foreground dark:text-foreground-dark">{{ $team->getMemberCount() }}</div>
                        <div class="text-sm text-muted dark:text-muted-dark">Members</div>
                    </div>
                    <div class="text-center p-4 rounded-xl bg-muted/10">
                        <div class="text-2xl font-bold text-foreground dark:text-foreground-dark">{{ $team->projects->count() }}</div>
                        <div class="text-sm text-muted dark:text-muted-dark">Projects</div>
                    </div>
                    <div class="text-center p-4 rounded-xl bg-muted/10">
                        <div class="text-2xl font-bold text-foreground dark:text-foreground-dark">{{ $team->getPendingInvitationsCount() }}</div>
                        <div class="text-sm text-muted dark:text-muted-dark">Pending Invites</div>
                    </div>
                </div>
            </div>

            <!-- Team Projects -->
            <div class="surface-card interactive-lift">
                <div class="p-6 border-b border-muted/10">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark">Team Projects</h2>
                        @if($team->hasPermission(auth()->user(), 'manage_projects'))
                            <a href="{{ route('projects.create') }}?team_id={{ $team->id }}" class="btn-brand text-sm">
                                <span class="mr-2">+</span>
                                New Project
                            </a>
                        @endif
                    </div>
                </div>
                
                <div class="p-6">
                    @if($team->projects->count() > 0)
                        <div class="space-y-4">
                            @foreach($team->projects as $project)
                                <div class="flex items-center justify-between p-4 rounded-xl border border-muted/10 hover:bg-muted/5 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold">
                                            {{ substr($project->title, 0, 1) }}
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-foreground dark:text-foreground-dark">{{ $project->title }}</h3>
                                            <p class="text-sm text-muted dark:text-muted-dark">
                                                {{ $project->user->name }} • {{ $project->created_at->format('M j, Y') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex px-2 py-1 rounded-full bg-{{ $project->status === 'completed' ? 'success' : ($project->status === 'in_progress' ? 'warning' : 'muted') }}/10 text-{{ $project->status === 'completed' ? 'success' : ($project->status === 'in_progress' ? 'warning' : 'muted') }} text-xs font-medium">
                                            {{ ucfirst($project->status) }}
                                        </span>
                                        <a href="{{ route('projects.show', $project) }}" class="btn-brand-muted text-sm">
                                            View
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-4xl mb-4">📁</div>
                            <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark mb-2">No Projects Yet</h3>
                            <p class="text-sm text-muted dark:text-muted-dark mb-4">
                                Create your first team project to start collaborating.
                            </p>
                            @if($team->hasPermission(auth()->user(), 'manage_projects'))
                                <a href="{{ route('projects.create') }}?team_id={{ $team->id }}" class="btn-brand text-sm">
                                    Create Project
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Activity Feed -->
            <div class="surface-card interactive-lift">
                <div class="p-6 border-b border-muted/10">
                    <h2 class="text-xl font-semibold text-foreground dark:text-foreground-dark">Recent Activity</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-success/10 flex items-center justify-center text-success text-sm">
                                ✓
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-foreground dark:text-foreground-dark">
                                    <strong>{{ $team->owner->name }}</strong> created the team
                                </p>
                                <p class="text-xs text-muted dark:text-muted-dark">{{ $team->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        
                        @foreach($team->members->take(3) as $member)
                            @if($member->user_id !== $team->owner_id)
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-info/10 flex items-center justify-center text-info text-sm">
                                        +
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-foreground dark:text-foreground-dark">
                                            <strong>{{ $member->user->name }}</strong> joined as {{ $member->getRoleLabel() }}
                                        </p>
                                        <p class="text-xs text-muted dark:text-muted-dark">{{ $member->joined_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Team Members -->
            <div class="surface-card interactive-lift">
                <div class="p-6 border-b border-muted/10">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Members</h2>
                        @if($canInviteMembers)
                            <button onclick="showInviteModal()" class="text-xs text-primary hover:text-primary/80">
                                Invite
                            </button>
                        @endif
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="space-y-3">
                        @foreach($team->getActiveMembers() as $member)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-muted/20 border-2 border-background dark:border-background-dark flex items-center justify-center text-xs font-medium">
                                        {{ substr($member->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-foreground dark:text-foreground-dark">
                                            {{ $member->user->name }}
                                        </p>
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex px-1.5 py-0.5 rounded-full bg-{{ $member->getRoleColor() }}/10 text-{{ $member->getRoleColor() }} text-xs">
                                                {{ $member->getRoleIcon() }} {{ $member->getRoleLabel() }}
                                            </span>
                                            @if($member->joined_at)
                                                <span class="text-xs text-muted dark:text-muted-dark">
                                                    Joined {{ $member->joined_at->diffForHumans() }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                @if($canManageTeam && $member->user_id !== auth()->user()->id)
                                    <div class="relative">
                                        <button onclick="showMemberMenu({{ $member->user_id }}, '{{ $member->role }}')" class="text-muted hover:text-foreground">
                                            ⋮
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Pending Invitations -->
            @if($canInviteMembers && $team->getPendingInvitationsCount() > 0)
                <div class="surface-card interactive-lift">
                    <div class="p-6 border-b border-muted/10">
                        <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Pending Invitations</h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="space-y-3">
                            @foreach($team->invitations()->pending()->get() as $invitation)
                                <div class="flex items-center justify-between p-3 rounded-xl border border-muted/10">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-foreground dark:text-foreground-dark">
                                            {{ $invitation->email }}
                                        </p>
                                        <p class="text-xs text-muted dark:text-muted-dark">
                                            {{ $invitation->getRoleLabel() }} • {{ $invitation->getDaysUntilExpiry() }} days left
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button onclick="resendInvitation({{ $invitation->id }})" class="text-xs text-primary hover:text-primary/80">
                                            Resend
                                        </button>
                                        <button onclick="cancelInvitation({{ $invitation->id }})" class="text-xs text-danger hover:text-danger/80">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Team Settings -->
            @if($canManageTeam)
                <div class="surface-card interactive-lift">
                    <div class="p-6 border-b border-muted/10">
                        <h2 class="text-lg font-semibold text-foreground dark:text-foreground-dark">Team Settings</h2>
                    </div>
                    
                    <div class="p-6 space-y-3">
                        <button onclick="showEditTeamModal()" class="w-full btn-brand-muted text-sm text-left">
                            Edit Team Info
                        </button>
                        @if($team->isOwner(auth()->user()))
                            <button onclick="showTransferOwnershipModal()" class="w-full btn-brand-muted text-sm text-left">
                                Transfer Ownership
                            </button>
                            <button onclick="confirmDeleteTeam()" class="w-full btn-brand-muted text-sm text-left text-danger">
                                Delete Team
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Invite Member Modal -->
<div id="invite-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-background dark:bg-background-dark rounded-3xl border border-muted/20 p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark mb-4">Invite Team Member</h3>
        
        <form id="invite-form">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-1">
                        Email Address
                    </label>
                    <input type="email" name="email" required
                           class="input-brand w-full" 
                           placeholder="colleague@example.com">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-1">
                        Role
                    </label>
                    <select name="role" class="input-brand w-full">
                        <option value="viewer">Viewer - Can view projects</option>
                        <option value="editor">Editor - Can manage projects</option>
                        @if($team->isOwner(auth()->user()))
                            <option value="admin">Admin - Can manage team</option>
                        @endif
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-1">
                        Message (Optional)
                    </label>
                    <textarea name="message" rows="3"
                              class="input-brand w-full" 
                              placeholder="Add a personal message..."></textarea>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="hideInviteModal()" 
                        class="flex-1 btn-brand-muted text-sm">
                    Cancel
                </button>
                <button type="submit" class="flex-1 btn-brand text-sm">
                    Send Invitation
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Team Modal -->
<div id="edit-team-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-background dark:bg-background-dark rounded-3xl border border-muted/20 p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-foreground dark:text-foreground-dark mb-4">Edit Team</h3>
        
        <form id="edit-team-form">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-1">
                        Team Name
                    </label>
                    <input type="text" name="name" value="{{ $team->name }}" required
                           class="input-brand w-full">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-foreground dark:text-foreground-dark mb-1">
                        Description
                    </label>
                    <textarea name="description" rows="3"
                              class="input-brand w-full">{{ $team->description }}</textarea>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="hideEditTeamModal()" 
                        class="flex-1 btn-brand-muted text-sm">
                    Cancel
                </button>
                <button type="submit" class="flex-1 btn-brand text-sm">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const teamId = {{ $team->id }};
const canManageTeam = {{ $canManageTeam ? 'true' : 'false' }};

document.addEventListener('DOMContentLoaded', function() {
    // Invite form
    const inviteForm = document.getElementById('invite-form');
    if (inviteForm) {
        inviteForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin inline-block mr-2"></span>Sending...';
            
            try {
                const response = await fetch(`/teams/${teamId}/invite`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        email: formData.get('email'),
                        role: formData.get('role'),
                        message: formData.get('message'),
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    hideInviteModal();
                    location.reload();
                } else {
                    alert(result.message || 'Failed to send invitation.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while sending the invitation.');
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        });
    }

    // Edit team form
    const editTeamForm = document.getElementById('edit-team-form');
    if (editTeamForm) {
        editTeamForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="w-4 h-4 border-2 border-current border-t-transparent rounded-full animate-spin inline-block mr-2"></span>Saving...';
            
            try {
                const response = await fetch(`/teams/${teamId}`, {
                    method: 'PATCH',
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
                    hideEditTeamModal();
                    location.reload();
                } else {
                    alert(result.message || 'Failed to update team.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while updating the team.');
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        });
    }
});

function showInviteModal() {
    document.getElementById('invite-modal').classList.remove('hidden');
}

function hideInviteModal() {
    document.getElementById('invite-modal').classList.add('hidden');
    document.getElementById('invite-form').reset();
}

function showEditTeamModal() {
    document.getElementById('edit-team-modal').classList.remove('hidden');
}

function hideEditTeamModal() {
    document.getElementById('edit-team-modal').classList.add('hidden');
}

function showMemberMenu(userId, currentRole) {
    // Implementation for member role management
    console.log('Show menu for user:', userId, 'Current role:', currentRole);
}

async function resendInvitation(invitationId) {
    try {
        const response = await fetch(`/teams/invitations/${invitationId}/resend`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Invitation resent successfully!');
        } else {
            alert(result.message || 'Failed to resend invitation.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while resending the invitation.');
    }
}

async function cancelInvitation(invitationId) {
    if (!confirm('Are you sure you want to cancel this invitation?')) {
        return;
    }
    
    try {
        const response = await fetch(`/teams/invitations/${invitationId}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert(result.message || 'Failed to cancel invitation.');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred while cancelling the invitation.');
    }
}

function confirmLeaveTeam() {
    if (!confirm('Are you sure you want to leave this team?')) {
        return;
    }
    
    fetch(`/teams/${teamId}/leave`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            window.location.href = result.redirect_url;
        } else {
            alert(result.message || 'Failed to leave team.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while leaving the team.');
    });
}

function confirmDeleteTeam() {
    if (!confirm('Are you sure you want to delete this team? This action cannot be undone.')) {
        return;
    }
    
    fetch(`/teams/${teamId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            window.location.href = result.redirect_url;
        } else {
            alert(result.message || 'Failed to delete team.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the team.');
    });
}
</script>
@endsection
