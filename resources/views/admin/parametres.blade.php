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
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show">
                            <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
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
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="paiement-especes_actif" id="paiement-especes_actif" value="1" {{ $params['paiement-especes_actif'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="paiement-especes_actif">Espèces</label>
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

                        <div class="mb-3">
                            <label class="form-label">Importer un logo (PNG, JPG, SVG)</label>
                            <input type="file" name="club-logo" class="form-control" accept="image/*">
                            <div class="form-text">Le logo sera converti en base64 et stocké en base de données.</div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="pwa-utiliser_logo_club" id="pwa-utiliser_logo_club" value="1" {{ $params['pwa-utiliser_logo_club'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="pwa-utiliser_logo_club">Utiliser le logo du club comme icône de l'application (PWA)</label>
                            </div>
                            <div class="form-text">Les icônes PWA (favicon, écran d'accueil) seront régénérées à chaque sauvegarde lorsque cette option est active.</div>
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

            @php
                $cbConfig = \App\Services\CarteBarService::config();
                $cbLayout = \App\Services\CarteBarService::layout($cbConfig);
            @endphp
            <div class="card mt-4" id="cartes-bar">
                <div class="card-header"><i class="fas fa-id-card me-2"></i>Cartes de bar</div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Définissez le format d'une carte et celui de la planche imprimable : la planche
                        assemble automatiquement le maximum de cartes, séparées par des pointillés de découpe.
                        Le contenu des cartes reste vierge pour l'instant.
                    </p>

                    <form method="post" action="{{ route('admin.cartebar.config') }}">
                        @csrf

                        <h6 class="text-muted text-uppercase small fw-bold mb-3">Format d'une carte</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-auto">
                                <label class="form-label">Modèle</label>
                                <select class="form-select" id="cartebar-preset">
                                    <option value="">Personnalisé…</option>
                                    <option value="105x148">A6 — 105 × 148 mm</option>
                                    <option value="74x105">A7 — 74 × 105 mm</option>
                                    <option value="86x54">Carte bancaire — 86 × 54 mm</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <label class="form-label">Largeur (mm)</label>
                                <input type="number" min="20" max="420" step="1" name="largeur_mm" id="cartebar-largeur"
                                       class="form-control" style="max-width:120px;" value="{{ $cbConfig['largeur_mm'] }}">
                            </div>
                            <div class="col-auto">
                                <label class="form-label">Hauteur (mm)</label>
                                <input type="number" min="20" max="420" step="1" name="hauteur_mm" id="cartebar-hauteur"
                                       class="form-control" style="max-width:120px;" value="{{ $cbConfig['hauteur_mm'] }}">
                            </div>
                        </div>

                        <h6 class="text-muted text-uppercase small fw-bold mb-3">Planche imprimable</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-auto">
                                <label class="form-label">Format</label>
                                <select class="form-select" name="format_page" style="max-width:110px;">
                                    @foreach(\App\Services\CarteBarService::formats() as $fmt)
                                        <option value="{{ $fmt }}" {{ $cbConfig['format_page'] === $fmt ? 'selected' : '' }}>{{ $fmt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <label class="form-label">Orientation</label>
                                <select class="form-select" name="orientation" style="max-width:150px;">
                                    <option value="portrait" {{ $cbConfig['orientation'] === 'portrait' ? 'selected' : '' }}>Portrait</option>
                                    <option value="paysage"  {{ $cbConfig['orientation'] === 'paysage' ? 'selected' : '' }}>Paysage</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <label class="form-label">Marge (mm)</label>
                                <input type="number" min="0" max="50" step="1" name="marge_mm"
                                       class="form-control" style="max-width:110px;" value="{{ $cbConfig['marge_mm'] }}">
                            </div>
                            <div class="col-auto">
                                <label class="form-label">Espacement (mm)</label>
                                <input type="number" min="0" max="50" step="1" name="espacement_mm"
                                       class="form-control" style="max-width:130px;" value="{{ $cbConfig['espacement_mm'] }}">
                            </div>
                        </div>

                        <div class="alert alert-secondary py-2 small mb-3">
                            <i class="fas fa-th me-1"></i>
                            @if($cbLayout['count'] > 0)
                                Disposition enregistrée : <strong>{{ $cbLayout['count'] }}</strong> carte(s) par planche
                                ({{ $cbLayout['cols'] }} colonne(s) × {{ $cbLayout['rows'] }} ligne(s))
                                sur {{ $cbConfig['format_page'] }} {{ $cbConfig['orientation'] }}.
                            @else
                                Aucune carte ne tient sur la planche avec ce format — réduisez les dimensions ou la marge.
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer le format
                        </button>
                        <a href="{{ route('admin.cartebar.download') }}" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-file-pdf me-2"></i>Télécharger le modèle (PDF)
                        </a>
                        <div class="form-text mt-2">Le PDF utilise le dernier format enregistré : pensez à enregistrer avant de télécharger.</div>
                    </form>
                </div>
            </div>

            <script>
            document.getElementById('cartebar-preset').addEventListener('change', function () {
                if (!this.value) return;
                const [w, h] = this.value.split('x');
                document.getElementById('cartebar-largeur').value = w;
                document.getElementById('cartebar-hauteur').value = h;
            });
            </script>

            <div class="card mt-4">
                <div class="card-header"><i class="fas fa-clock me-2"></i>Tâches planifiées (Cron)</div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Pour activer le planificateur Laravel, ajoutez cette ligne dans le crontab du serveur :</p>
                    <div class="input-group mb-3">
                        <code class="form-control font-monospace bg-light" style="font-size:0.8rem;" id="crontabLine">* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</code>
                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="navigator.clipboard.writeText(document.getElementById('crontabLine').textContent).then(()=>{this.innerHTML='<i class=\'fas fa-check\'></i>';setTimeout(()=>this.innerHTML='<i class=\'fas fa-copy\'></i>',1500)})">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>

                    @if(!empty($cronEvents))
                    <ul class="list-group mb-3">
                        @foreach($cronEvents as $event)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div>{{ $event['label'] }}</div>
                                <code class="text-muted small">{{ $event['expression'] }}</code>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm cron-task-btn"
                                    data-key="{{ $event['key'] }}" onclick="runCron('{{ $event['key'] }}', this)">
                                <i class="fas fa-play me-1"></i>Exécuter
                            </button>
                        </li>
                        @endforeach
                    </ul>
                    @endif

                    <button type="button" class="btn btn-primary btn-sm" id="btnRunCron" onclick="runCron(null, this)">
                        <i class="fas fa-play me-1"></i>Exécuter toutes les tâches
                    </button>

                    <div id="cronResult" class="mt-3 d-none">
                        <pre class="bg-dark text-light p-3 rounded mb-0" style="font-size:0.8rem; max-height:300px; overflow-y:auto; white-space:pre-wrap;" id="cronOutput"></pre>
                    </div>
                </div>
            </div>

            <script>
            function runCron(key, btn) {
                const resultDiv = document.getElementById('cronResult');
                const outputPre = document.getElementById('cronOutput');
                const originalHtml = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Exécution…';
                resultDiv.classList.add('d-none');

                const baseUrl = '{{ route("admin.parametres.cron") }}';
                const url = key ? baseUrl + '/' + encodeURIComponent(key) : baseUrl;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                })
                .then(r => r.json())
                .then(data => {
                    outputPre.textContent = data.output || '(aucune sortie)';
                    resultDiv.classList.remove('d-none');
                })
                .catch(err => {
                    outputPre.textContent = 'Erreur : ' + err.message;
                    resultDiv.classList.remove('d-none');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                });
            }
            </script>

            @if($helloAssoPending->isNotEmpty())
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-credit-card me-2"></i>Paiements HelloAsso en attente
                    <span class="badge bg-warning text-dark ms-2">{{ $helloAssoPending->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Payeur</th>
                                    <th>Montant</th>
                                    <th>Reçu le</th>
                                    <th>Statut</th>
                                    <th>Tentatives</th>
                                    <th>Dernière erreur</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($helloAssoPending as $p)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold">{{ $p->payer_name ?: '—' }}</div>
                                        <small class="text-muted">{{ $p->payer_email }}</small><br>
                                        <small class="text-muted">paiement {{ $p->payment_id }} / commande {{ $p->order_id }}</small>
                                        @if($p->checkout_intent_id)
                                            <br><small class="text-muted">checkout intent {{ $p->checkout_intent_id }}</small>
                                        @endif
                                    </td>
                                    <td><strong>{{ number_format($p->amount / 100, 2, ',', ' ') }} €</strong></td>
                                    <td><small>{{ $p->created_at?->format('d/m/Y H:i') }}</small></td>
                                    <td>
                                        @if($p->status === 'failed')
                                            <span class="badge bg-danger">Échec</span>
                                        @else
                                            <span class="badge bg-warning text-dark">En attente</span>
                                        @endif
                                    </td>
                                    <td>{{ $p->attempts }}/5</td>
                                    <td><small class="text-muted">{{ $p->error_message ?: '—' }}</small></td>
                                    <td class="text-end pe-3" style="white-space:nowrap;">
                                        <form method="post" action="{{ route('admin.parametres.helloasso.validate', $p->id) }}" style="display:inline;"
                                              onsubmit="return confirm('Créditer {{ number_format($p->amount / 100, 2, ',', ' ') }} € à {{ $p->payer_email }} ?');">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm" title="Créditer manuellement">
                                                <i class="fas fa-check me-1"></i>Créditer
                                            </button>
                                        </form>
                                        <form method="post" action="{{ route('admin.parametres.helloasso.delete', $p->id) }}" style="display:inline;"
                                              onsubmit="return confirm('Supprimer ce paiement de la file ? Aucun crédit ne sera fait.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

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
