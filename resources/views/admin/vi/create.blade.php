@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-plane me-2"></i>Nouveau bon de vol d'initiation
                </div>
                <div class="card-body">

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.vi.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="source" class="form-label">Source</label>
                            <select name="source" id="source" class="form-select" required>
                                <option value="admin" {{ old('source', 'admin') === 'admin' ? 'selected' : '' }}>
                                    Admin (créé manuellement)
                                </option>
                                <option value="offert" {{ old('source') === 'offert' ? 'selected' : '' }}>
                                    Offert (cadeau / lot)
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Type de vol</label>
                            <select name="type" id="type" class="form-select">
                                <option value="">— Aucun type —</option>
                                @foreach($types as $t)
                                    <option value="{{ $t['label'] }}"
                                            data-prix="{{ $t['prix_cts'] }}"
                                            {{ old('type') === $t['label'] ? 'selected' : '' }}>
                                        {{ $t['label'] }}
                                        ({{ number_format($t['prix_cts'] / 100, 2, ',', ' ') }} €)
                                    </option>
                                @endforeach
                            </select>
                            @if($types->isEmpty())
                                <div class="form-text text-muted">
                                    Aucun type configuré. Ajoutez des paramètres <code>vi-[Nom]</code> dans
                                    <a href="{{ route('admin.parametres') }}">Paramètres du club</a>.
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes internes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3"
                                      placeholder="Informations complémentaires...">{{ old('notes') }}</textarea>
                        </div>

                        <div class="alert alert-info py-2 small">
                            <i class="fas fa-info-circle me-1"></i>
                            Le code unique 8 caractères sera généré automatiquement.
                            Le bénéficiaire remplira ses informations personnelles via la page d'activation.
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Créer le bon
                            </button>
                            <a href="{{ route('admin.vi.index') }}" class="btn btn-secondary">Annuler</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
