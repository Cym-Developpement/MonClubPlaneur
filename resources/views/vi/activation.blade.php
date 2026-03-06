@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header text-center">
                    <strong><i class="fas fa-plane me-2"></i>Vol d'initiation</strong>
                </div>
                <div class="card-body">
                    @php $logo = \App\Models\parametre::getValue('club-logo', ''); @endphp
                    @if($logo)
                        <div class="text-center mb-4">
                            <img src="{{ $logo }}" alt="Logo du club" style="max-height:80px;max-width:200px;">
                        </div>
                    @endif

                    {{-- Alertes --}}
                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        </div>
                    @endif
                    @if(session('info'))
                        <div class="alert alert-info">{{ session('info') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Informations du bon --}}
                    <div class="alert alert-secondary py-2 mb-4">
                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <span><strong>Code :</strong> <code>{{ $vi->code }}</code></span>
                            @if($vi->type)
                                <span><strong>Type :</strong> {{ $vi->type }}</span>
                            @endif
                            <span><strong>Prix :</strong> {{ $vi->prix_eur }}</span>
                        </div>
                    </div>

                    @if($vi->actif && !session('success'))
                        {{-- Déjà activé --}}
                        <div class="alert alert-info text-center">
                            <i class="fas fa-check-circle me-2 fs-4"></i><br>
                            <strong>Bon déjà activé — merci !</strong><br>
                            <span class="text-muted small">Nous vous contacterons prochainement pour fixer la date de votre vol.</span>
                        </div>
                    @elseif(!$vi->actif)
                        {{-- Formulaire d'activation --}}
                        <p class="text-muted small mb-3">
                            Merci de remplir vos informations ci-dessous pour activer votre bon de vol.
                            Ces informations sont nécessaires pour organiser votre vol en toute sécurité.
                        </p>

                        <form method="POST" action="{{ route('vi.activation.store', $vi->code) }}">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                                           value="{{ old('nom') }}" required>
                                    @error('nom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" name="prenom" class="form-control @error('prenom') is-invalid @enderror"
                                           value="{{ old('prenom') }}" required>
                                    @error('prenom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date de naissance <span class="text-danger">*</span></label>
                                    <input type="date" name="date_naissance" class="form-control @error('date_naissance') is-invalid @enderror"
                                           value="{{ old('date_naissance') }}" required>
                                    @error('date_naissance')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Téléphone</label>
                                    <input type="tel" name="telephone" class="form-control"
                                           value="{{ old('telephone') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Adresse</label>
                                    <input type="text" name="adresse" class="form-control"
                                           value="{{ old('adresse') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Code postal</label>
                                    <input type="text" name="cp" class="form-control"
                                           value="{{ old('cp') }}">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Ville</label>
                                    <input type="text" name="ville" class="form-control"
                                           value="{{ old('ville') }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Vos disponibilités</label>
                                    <p class="text-muted small mb-1">
                                        Pour organiser votre vol au mieux, indiquez-nous vos disponibilités (jours, périodes, contraintes éventuelles).
                                    </p>
                                    <textarea name="disponibilites" class="form-control" rows="3"
                                              placeholder="Ex : disponible le week-end, de préférence le matin, pas avant juin…">{{ old('disponibilites') }}</textarea>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-check me-1"></i>Activer mon bon de vol
                                </button>
                            </div>
                        </form>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
