@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-plane me-2"></i>Bon VI — <code>{{ $vi->code }}</code></span>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.vi.edit', $vi->id) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-pencil-alt me-1"></i>Modifier
                        </a>
                        <a href="{{ route('admin.vi.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Liste
                        </a>
                        <form action="{{ route('admin.vi.destroy', $vi->id) }}" method="POST"
                              onsubmit="return confirm('Supprimer définitivement ce bon VI ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash me-1"></i>Supprimer
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <dl class="row">
                        <dt class="col-sm-4">Code</dt>
                        <dd class="col-sm-8"><code>{{ $vi->code }}</code></dd>

                        <dt class="col-sm-4">Lien d'activation</dt>
                        <dd class="col-sm-8">
                            <a href="{{ url('/vi/' . $vi->code) }}" target="_blank">
                                {{ url('/vi/' . $vi->code) }}
                            </a>
                        </dd>

                        <dt class="col-sm-4">Source</dt>
                        <dd class="col-sm-8">
                            @if($vi->source === 'helloasso')
                                <span class="badge bg-primary">HelloAsso</span>
                            @elseif($vi->source === 'offert')
                                <span class="badge bg-success">Offert</span>
                            @else
                                <span class="badge bg-secondary">Admin</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Type</dt>
                        <dd class="col-sm-8">{{ $vi->type ?? '—' }}</dd>

                        <dt class="col-sm-4">Prix</dt>
                        <dd class="col-sm-8">{{ $vi->prix_eur }}</dd>

                        <dt class="col-sm-4">Activé</dt>
                        <dd class="col-sm-8">
                            @if($vi->actif)
                                <span class="badge bg-success">Oui</span>
                            @else
                                <span class="badge bg-warning text-dark">Non</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Réalisé</dt>
                        <dd class="col-sm-8">
                            @if($vi->realise)
                                <span class="badge bg-success">Oui</span>
                                @if($vi->date_realisation)
                                    — {{ $vi->date_realisation->format('d/m/Y') }}
                                @endif
                            @else
                                <span class="badge bg-secondary">Non</span>
                            @endif
                        </dd>
                    </dl>

                    @if($vi->actif)
                    <hr>
                    <h6>Informations bénéficiaire</h6>
                    <dl class="row">
                        <dt class="col-sm-4">Nom / Prénom</dt>
                        <dd class="col-sm-8">{{ $vi->prenom }} {{ $vi->nom }}</dd>

                        <dt class="col-sm-4">Date de naissance</dt>
                        <dd class="col-sm-8">{{ $vi->date_naissance ? $vi->date_naissance->format('d/m/Y') : '—' }}</dd>

                        <dt class="col-sm-4">Adresse</dt>
                        <dd class="col-sm-8">
                            {{ $vi->adresse }}<br>
                            {{ $vi->cp }} {{ $vi->ville }}
                        </dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8"><a href="mailto:{{ $vi->email }}">{{ $vi->email }}</a></dd>

                        <dt class="col-sm-4">Téléphone</dt>
                        <dd class="col-sm-8">{{ $vi->telephone ?? '—' }}</dd>
                    </dl>
                    @endif

                    @if($vi->disponibilites)
                    <hr>
                    <h6>Disponibilités</h6>
                    <p class="text-muted">{{ $vi->disponibilites }}</p>
                    @endif

                    @if($vi->notes)
                    <hr>
                    <h6>Notes</h6>
                    <p class="text-muted">{{ $vi->notes }}</p>
                    @endif

                    @if($vi->helloasso_order_id || $vi->helloasso_payment_id)
                    <hr>
                    <h6>HelloAsso</h6>
                    <dl class="row">
                        <dt class="col-sm-4">Order ID</dt>
                        <dd class="col-sm-8"><code>{{ $vi->helloasso_order_id ?? '—' }}</code></dd>
                        <dt class="col-sm-4">Payment ID</dt>
                        <dd class="col-sm-8"><code>{{ $vi->helloasso_payment_id ?? '—' }}</code></dd>
                    </dl>
                    @endif

                    @if(!$vi->realise)
                    <hr>
                    <form action="{{ route('admin.vi.realise', $vi->id) }}" method="POST"
                          onsubmit="return confirm('Marquer ce vol comme réalisé ?')">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i>Marquer comme réalisé
                        </button>
                    </form>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
