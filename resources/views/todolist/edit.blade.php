@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Modifier la tâche
                    </h4>
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

                    <form method="POST" action="{{ route('todolist.update', $task->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label for="title">Titre de la tâche *</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title', $task->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4">{{ old('description', $task->description) }}</textarea>
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
                                            <option value="{{ $user->id }}" 
                                                    {{ old('assigned_to', $task->assigned_to) == $user->id ? 'selected' : '' }}>
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
                                        <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>Faible</option>
                                        <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>Moyenne</option>
                                        <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>Élevée</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Statut *</label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="pending" {{ old('status', $task->status) == 'pending' ? 'selected' : '' }}>En attente</option>
                                        <option value="in_progress" {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>En cours</option>
                                        <option value="completed" {{ old('status', $task->status) == 'completed' ? 'selected' : '' }}>Complétée</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="due_date">Date limite</label>
                                    <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                                           id="due_date" name="due_date" 
                                           value="{{ old('due_date', $task->due_date ? $task->due_date->format('Y-m-d') : '') }}">
                                    @error('due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        {{-- Informations sur la tâche --}}
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title">Informations sur la tâche</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <strong>Créée par :</strong> {{ $task->creator->name }}<br>
                                            <strong>Date de création :</strong> {{ $task->created_at->format('d/m/Y à H:i') }}
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        @if($task->completed_at)
                                            <small class="text-muted">
                                                <strong>Complétée le :</strong> {{ $task->completed_at->format('d/m/Y à H:i') }}<br>
                                                @if($task->completed_by)
                                                    <strong>Par :</strong> {{ $task->completer->name }}
                                                @endif
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Boutons --}}
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('todolist.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection