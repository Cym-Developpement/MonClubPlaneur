@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-pencil-alt me-2"></i>Modifier VI — <code>{{ $vi->code }}</code></span>
                    <a href="{{ route('admin.vi.show', $vi->id) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Retour
                    </a>
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

                    {{-- Bloc lecture seule --}}
                    <div class="alert alert-secondary py-2 small">
                        <strong>Code :</strong> <code>{{ $vi->code }}</code>
                        &nbsp;|&nbsp;
                        <strong>Source :</strong> {{ $vi->source }}
                        @if($vi->helloasso_order_id)
                            &nbsp;|&nbsp;
                            <strong>HA Order :</strong> <code>{{ $vi->helloasso_order_id }}</code>
                        @endif
                        @if($vi->helloasso_payment_id)
                            &nbsp;|&nbsp;
                            <strong>HA Payment :</strong> <code>{{ $vi->helloasso_payment_id }}</code>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('admin.vi.update', $vi->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Source</label>
                                <select name="source" class="form-select" required>
                                    <option value="admin"      {{ old('source', $vi->source) === 'admin'      ? 'selected' : '' }}>Admin</option>
                                    <option value="offert"     {{ old('source', $vi->source) === 'offert'     ? 'selected' : '' }}>Offert</option>
                                    <option value="helloasso"  {{ old('source', $vi->source) === 'helloasso'  ? 'selected' : '' }}>HelloAsso</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Type de vol</label>
                                <select name="type" class="form-select">
                                    <option value="">— Aucun type —</option>
                                    @foreach($types as $t)
                                        <option value="{{ $t['label'] }}"
                                                {{ old('type', $vi->type) === $t['label'] ? 'selected' : '' }}>
                                            {{ $t['label'] }} ({{ number_format($t['prix_cts'] / 100, 2, ',', ' ') }} €)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Disponibilités (renseignées par le bénéficiaire)</label>
                                <textarea name="disponibilites" class="form-control" rows="2">{{ old('disponibilites', $vi->disponibilites) }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes internes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $vi->notes) }}</textarea>
                            </div>

                            <div class="col-12"><hr><h6>Statut</h6></div>

                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="actif" value="0">
                                    <input type="checkbox" class="form-check-input" id="actif" name="actif" value="1"
                                           {{ old('actif', $vi->actif) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="actif">Activé</label>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="realise" value="0">
                                    <input type="checkbox" class="form-check-input" id="realise" name="realise" value="1"
                                           {{ old('realise', $vi->realise) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="realise">Réalisé</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Date de réalisation</label>
                                <input type="date" name="date_realisation" class="form-control"
                                       value="{{ old('date_realisation', $vi->date_realisation ? $vi->date_realisation->format('Y-m-d') : '') }}">
                            </div>

                            <div class="col-12"><hr><h6>Informations bénéficiaire</h6></div>

                            <div class="col-md-6">
                                <label class="form-label">Nom</label>
                                <input type="text" name="nom" class="form-control"
                                       value="{{ old('nom', $vi->nom) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prénom</label>
                                <input type="text" name="prenom" class="form-control"
                                       value="{{ old('prenom', $vi->prenom) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date de naissance</label>
                                <input type="date" name="date_naissance" class="form-control"
                                       value="{{ old('date_naissance', $vi->date_naissance ? $vi->date_naissance->format('Y-m-d') : '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="telephone" class="form-control"
                                       value="{{ old('telephone', $vi->telephone) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Adresse</label>
                                <input type="text" name="adresse" class="form-control"
                                       value="{{ old('adresse', $vi->adresse) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Code postal</label>
                                <input type="text" name="cp" class="form-control"
                                       value="{{ old('cp', $vi->cp) }}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Ville</label>
                                <input type="text" name="ville" class="form-control"
                                       value="{{ old('ville', $vi->ville) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control"
                                       value="{{ old('email', $vi->email) }}">
                            </div>

                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Enregistrer
                            </button>
                            <a href="{{ route('admin.vi.show', $vi->id) }}" class="btn btn-secondary">Annuler</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
