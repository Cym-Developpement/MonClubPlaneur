@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-tasks me-2"></i>
                        Todolist partagée du club
                    </h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                        <i class="fas fa-plus me-2"></i>Nouvelle tâche
                    </button>
                </div>
                <div class="card-body">
                    {{-- Affichage des notifications --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- Filtres --}}
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary active" data-filter="all">
                                    Toutes
                                </button>
                                <button type="button" class="btn btn-outline-secondary" data-filter="pending">
                                    En attente
                                </button>
                                <button type="button" class="btn btn-outline-secondary" data-filter="in_progress">
                                    En cours
                                </button>
                                <button type="button" class="btn btn-outline-secondary" data-filter="completed">
                                    Complétées
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Liste des tâches --}}
                    <div class="row" id="tasks-container">
                        @forelse($tasks as $task)
                            <div class="col-md-6 col-lg-4 mb-4 task-item" data-status="{{ $task->status }}">
                                <div class="card h-100 {{ $task->isOverdue() ? 'border-danger' : '' }}">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-{{ $task->getPriorityColor() }} me-2">
                                                {{ ucfirst($task->priority) }}
                                            </span>
                                            <span class="badge badge-{{ $task->getStatusColor() }}">
                                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                            </span>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @if($task->status !== 'completed')
                                                    <form method="POST" action="{{ route('todolist.complete', $task->id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-success">
                                                            <i class="fas fa-check me-2"></i>Marquer comme complétée
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                @if($task->status !== 'in_progress')
                                                    <form method="POST" action="{{ route('todolist.in-progress', $task->id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-primary">
                                                            <i class="fas fa-play me-2"></i>Marquer en cours
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                @if($task->status !== 'pending')
                                                    <form method="POST" action="{{ route('todolist.pending', $task->id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-secondary">
                                                            <i class="fas fa-pause me-2"></i>Marquer en attente
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                <div class="dropdown-divider"></div>
                                                
                                                <a href="{{ route('todolist.edit', $task->id) }}" class="dropdown-item">
                                                    <i class="fas fa-edit me-2"></i>Modifier
                                                </a>
                                                
                                                @if($task->created_by === Auth::id() || Auth::user()->can('admin'))
                                                    <form method="POST" action="{{ route('todolist.destroy', $task->id) }}" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $task->title }}</h6>
                                        @if($task->description)
                                            <p class="card-text text-muted small">{{ Str::limit($task->description, 100) }}</p>
                                        @endif
                                        
                                        <div class="task-meta">
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i>
                                                Créée par <strong>{{ $task->creator->name }}</strong>
                                            </small>
                                            @if($task->assigned_to)
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-user-check me-1"></i>
                                                    Assignée à <strong>{{ $task->assignee->name }}</strong>
                                                </small>
                                            @endif
                                            @if($task->due_date)
                                                <br>
                                                <small class="text-muted {{ $task->isOverdue() ? 'text-danger' : '' }}">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    Échéance : {{ $task->due_date->format('d/m/Y') }}
                                                </small>
                                            @endif
                                            @if($task->completed_at)
                                                <br>
                                                <small class="text-success">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    Complétée le {{ $task->completed_at->format('d/m/Y à H:i') }}
                                                    @if($task->completed_by)
                                                        par {{ $task->completer->name }}
                                                    @endif
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="text-center py-5">
                                    <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Aucune tâche pour le moment</h5>
                                    <p class="text-muted">Créez votre première tâche pour commencer !</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal pour ajouter une tâche --}}
<div class="modal fade" id="addTaskModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('todolist.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Nouvelle tâche
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">Titre de la tâche *</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="assigned_to">Assigner à</label>
                                <select class="form-control @error('assigned_to') is-invalid @enderror" 
                                        id="assigned_to" name="assigned_to">
                                    <option value="">Non assignée</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assigned_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="priority">Priorité *</label>
                                <select class="form-control @error('priority') is-invalid @enderror" 
                                        id="priority" name="priority" required>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Faible</option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Moyenne</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Élevée</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="due_date">Date limite</label>
                        <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                               id="due_date" name="due_date" value="{{ old('due_date') }}">
                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Créer la tâche
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtrage des tâches
    const filterButtons = document.querySelectorAll('[data-filter]');
    const taskItems = document.querySelectorAll('.task-item');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Mettre à jour les boutons actifs
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filtrer les tâches
            taskItems.forEach(item => {
                if (filter === 'all' || item.getAttribute('data-status') === filter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
</script>

@endsection