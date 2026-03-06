@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-plane me-2"></i>Vols d'initiation</span>
                    @can('admin:vi')
                    <a href="{{ route('admin.vi.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Nouveau VI
                    </a>
                    @endcan
                </div>
                <div class="card-body">

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    {{-- Filtres rapides --}}
                    <div class="btn-group mb-3" role="group">
                        <a href="{{ route('admin.vi.index') }}"
                           class="btn btn-sm {{ $filtre === 'tous' ? 'btn-primary' : 'btn-outline-primary' }}">
                            Tous ({{ \App\Models\VolInitiation::count() }})
                        </a>
                        <a href="{{ route('admin.vi.index', ['filtre' => 'non_actifs']) }}"
                           class="btn btn-sm {{ $filtre === 'non_actifs' ? 'btn-warning' : 'btn-outline-warning' }}">
                            Non activés ({{ \App\Models\VolInitiation::where('actif', false)->count() }})
                        </a>
                        <a href="{{ route('admin.vi.index', ['filtre' => 'actifs_non_realises']) }}"
                           class="btn btn-sm {{ $filtre === 'actifs_non_realises' ? 'btn-info' : 'btn-outline-info' }}">
                            Activés / non réalisés ({{ \App\Models\VolInitiation::where('actif', true)->where('realise', false)->count() }})
                        </a>
                        <a href="{{ route('admin.vi.index', ['filtre' => 'realises']) }}"
                           class="btn btn-sm {{ $filtre === 'realises' ? 'btn-success' : 'btn-outline-success' }}">
                            Réalisés ({{ \App\Models\VolInitiation::where('realise', true)->count() }})
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Type</th>
                                    <th>Source</th>
                                    <th>Prix</th>
                                    <th>Activé</th>
                                    <th>Réalisé</th>
                                    <th>Date réalisation</th>
                                    <th>Bénéficiaire</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vis as $vi)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.vi.show', $vi->id) }}">
                                            <code>{{ $vi->code }}</code>
                                        </a>
                                    </td>
                                    <td>{{ $vi->type ?? '—' }}</td>
                                    <td>
                                        @if($vi->source === 'helloasso')
                                            <span class="badge bg-primary">HelloAsso</span>
                                        @elseif($vi->source === 'offert')
                                            <span class="badge bg-success">Offert</span>
                                        @else
                                            <span class="badge bg-secondary">Admin</span>
                                        @endif
                                    </td>
                                    <td>{{ $vi->prix_eur }}</td>
                                    <td>
                                        @if($vi->actif)
                                            <span class="badge bg-success">Oui</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Non</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($vi->realise)
                                            <span class="badge bg-success">Oui</span>
                                        @else
                                            <span class="badge bg-secondary">Non</span>
                                        @endif
                                    </td>
                                    <td>{{ $vi->date_realisation ? $vi->date_realisation->format('d/m/Y') : '—' }}</td>
                                    <td>
                                        @if($vi->nom || $vi->prenom)
                                            {{ $vi->prenom }} {{ $vi->nom }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.vi.edit', $vi->id) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Modifier">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                        @if(!$vi->realise)
                                        <form action="{{ route('admin.vi.realise', $vi->id) }}" method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Marquer ce vol comme réalisé ?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Marquer réalisé">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        @endif
                                        <a href="{{ url('/vi/' . $vi->code) }}" target="_blank"
                                           class="btn btn-sm btn-outline-info" title="Lien d'activation">
                                            <i class="fas fa-link"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Aucun bon de vol d'initiation.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
