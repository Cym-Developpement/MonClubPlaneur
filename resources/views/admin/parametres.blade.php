@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card">
                <div class="card-header"><i class="fas fa-cog me-2"></i>Paramètres du club</div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="post" action="/admin/parametres" enctype="multipart/form-data">
                        @csrf

                        <h6 class="text-muted text-uppercase small fw-bold mb-3">Identité du club</h6>

                        <div class="mb-3">
                            <label class="form-label">Nom court</label>
                            <input type="text" name="club-nom_court" class="form-control" value="{{ $params['club-nom_court'] }}" placeholder="CVVT">
                            <div class="form-text">Utilisé comme titre court dans les emails et documents.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nom complet</label>
                            <input type="text" name="club-nom_complet" class="form-control" value="{{ $params['club-nom_complet'] }}" placeholder="Club de Vol à Voile de Thionville">
                        </div>

                        <hr>
                        <h6 class="text-muted text-uppercase small fw-bold mb-3">Contact trésorerie</h6>

                        <div class="mb-3">
                            <label class="form-label">Nom du trésorier</label>
                            <input type="text" name="club-tresorier" class="form-control" value="{{ $params['club-tresorier'] }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email de contact</label>
                            <input type="email" name="club-email" class="form-control" value="{{ $params['club-email'] }}">
                        </div>

                        <hr>
                        <h6 class="text-muted text-uppercase small fw-bold mb-3">Logo</h6>

                        @if($params['club-logo'])
                            <div class="mb-3">
                                <img src="{{ $params['club-logo'] }}" alt="Logo actuel" style="max-height:80px;max-width:200px;" class="border rounded p-1">
                                <div class="form-text mt-1">Logo actuel — importer un nouveau fichier pour le remplacer.</div>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label class="form-label">Importer un logo (PNG, JPG, SVG)</label>
                            <input type="file" name="club-logo" class="form-control" accept="image/*">
                            <div class="form-text">Le logo sera converti en base64 et stocké en base de données.</div>
                        </div>

                        <hr>
                        <h6 class="text-muted text-uppercase small fw-bold mb-3"><i class="fas fa-archive me-2"></i>Sauvegardes</h6>

                        <div class="mb-4">
                            <label class="form-label">Nombre maximum de sauvegardes automatiques</label>
                            <input type="number" name="backup-purge_auto" class="form-control" style="max-width:120px;"
                                   value="{{ $params['backup-purge_auto'] }}" min="0" step="1">
                            <div class="form-text">Les plus anciennes sauvegardes automatiques sont supprimées au-delà de ce seuil. <strong>0 = désactivé.</strong></div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                    </form>
                </div>
            </div>

            @if(!$autresParams->isEmpty())
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-sliders-h me-2"></i>Autres paramètres
                    <small class="text-muted ms-2">(lecture seule — gérés par l'application)</small>
                </div>

                <div class="card-body p-0">
                    @foreach($autresParams as $categorie => $items)
                        <div class="px-3 pt-3 pb-1">
                            <h6 class="text-muted text-uppercase small fw-bold mb-1">{{ $categorie }}</h6>
                        </div>
                        <table class="table table-sm table-hover mb-0">
                            <tbody>
                                @foreach($items as $p)
                                    @php
                                        $parts = explode('-', $p->nom, 2);
                                        $label = count($parts) > 1 ? trim($parts[1]) : $p->nom;
                                    @endphp
                                    <tr>
                                        <td class="ps-3" style="width:38%">
                                            <span class="fw-semibold">{{ $label }}</span>
                                            @if($p->description)
                                                <br><small class="text-muted">{{ $p->description }}</small>
                                            @endif
                                        </td>
                                        <td style="width:12%">
                                            <span class="badge bg-secondary">{{ $p->type }}</span>
                                            @if($p->monetary)
                                                <span class="badge bg-info text-dark">€</span>
                                            @endif
                                        </td>
                                        <td class="font-monospace">{{ $p->value }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if(!$loop->last)
                            <hr class="my-0">
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
