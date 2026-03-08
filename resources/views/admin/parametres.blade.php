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
                        <h6 class="text-muted text-uppercase small fw-bold mb-3">Paiements</h6>

                        <div class="mb-3">
                            <label class="form-label">IBAN du club</label>
                            <input type="text" name="paiement-iban" class="form-control" value="{{ $params['paiement-iban'] }}" placeholder="FR76 XXXX XXXX XXXX XXXX XXXX XXX">
                            <div class="form-text">Affiché sur les factures et extraits de compte PDF.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lien de paiement en ligne (CB)</label>
                            <input type="text" name="paiement-cb_url" class="form-control" value="{{ $params['paiement-cb_url'] }}" placeholder="https://...">
                            <div class="form-text">URL complète vers la page de paiement par carte bancaire (HelloAsso ou autre). Affiché sur les PDF.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label d-block">Moyens de paiement activés</label>
                            <div class="d-flex gap-4 flex-wrap">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="paiement-cb_actif" id="paiement-cb_actif" value="1" {{ $params['paiement-cb_actif'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="paiement-cb_actif">Carte bancaire (CB)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="paiement-virement_actif" id="paiement-virement_actif" value="1" {{ $params['paiement-virement_actif'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="paiement-virement_actif">Virement bancaire</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="paiement-cheque_actif" id="paiement-cheque_actif" value="1" {{ $params['paiement-cheque_actif'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="paiement-cheque_actif">Chèque</label>
                                </div>
                            </div>
                            <div class="form-text">Seuls les moyens activés apparaissent dans le formulaire de paiement et sur les documents PDF.</div>
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
                <div class="card-header"><i class="fas fa-sliders-h me-2"></i>Autres paramètres</div>

                <div class="card-body p-0">
                    <form method="post" action="/admin/parametres/autres">
                        @csrf

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
                                            <td>
                                                @if($p->type === 'boolean')
                                                    <input type="hidden"   name="autres[{{ $p->id }}]" value="0">
                                                    <input type="checkbox" name="autres[{{ $p->id }}]" value="1"
                                                           class="form-check-input" {{ $p->value ? 'checked' : '' }}>
                                                @elseif($p->type === 'integer')
                                                    <input type="number" step="1"
                                                           name="autres[{{ $p->id }}]" value="{{ $p->value }}"
                                                           class="form-control form-control-sm" style="max-width:140px;">
                                                @elseif($p->type === 'double')
                                                    <input type="number" step="any"
                                                           name="autres[{{ $p->id }}]" value="{{ $p->value }}"
                                                           class="form-control form-control-sm" style="max-width:140px;">
                                                @else
                                                    <input type="text"
                                                           name="autres[{{ $p->id }}]" value="{{ $p->value }}"
                                                           class="form-control form-control-sm">
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @if(!$loop->last)
                                <hr class="my-0">
                            @endif
                        @endforeach

                        <div class="px-3 py-3">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save me-2"></i>Enregistrer les autres paramètres
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
