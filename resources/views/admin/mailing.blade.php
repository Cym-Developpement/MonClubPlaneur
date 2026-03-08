@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Formulaire principal --}}
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-envelope-open-text me-2"></i>Emailing groupé
                </div>
                <div class="card-body">
                    <form method="POST" action="/admin/mailing/send">
                        @csrf
                        <div class="mb-3">
                            <label for="subject" class="form-label">Sujet</label>
                            <input type="text" class="form-control" id="subject" name="subject"
                                   maxlength="200" value="{{ old('subject') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="body" class="form-label">Message</label>
                            <textarea class="form-control" id="body" name="body" rows="12"
                                      placeholder="Rédigez votre message en texte simple…" required>{{ old('body') }}</textarea>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="filter" class="form-label">Destinataires</label>
                                <select class="form-select" id="filter" name="filter">
                                    <option value="active" @selected(old('filter', 'active') === 'active')>Membres actifs</option>
                                    <option value="all"    @selected(old('filter') === 'all')>Tous les membres</option>
                                    @foreach(range(date('Y'), 2022) as $year)
                                        <option value="year:{{ $year }}" @selected(old('filter') === 'year:' . $year)>
                                            Adhérents {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="exclude_technique"
                                           name="exclude_technique" value="1"
                                           @checked(old('exclude_technique', '1') === '1')>
                                    <label class="form-check-label" for="exclude_technique">
                                        Exclure les comptes techniques
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div id="recipients-panel" class="mt-3">
                            <div class="d-flex align-items-center gap-2">
                                <span id="recipients-count" class="text-muted small"></span>
                                <button class="btn btn-sm btn-outline-secondary" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#recipients-list">
                                    Voir la liste
                                </button>
                            </div>
                            <div class="collapse mt-2" id="recipients-list">
                                <ul id="recipients-ul" class="list-group list-group-flush small"
                                    style="max-height:260px;overflow-y:auto;border:1px solid #dee2e6;border-radius:4px;"></ul>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Envoyer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Envoi de test --}}
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-vial me-2"></i>Envoi de test
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Remplissez le formulaire ci-dessus, puis envoyez un test à l'adresse de votre choix avant l'envoi groupé.
                    </p>
                    <form method="POST" action="/admin/mailing/test">
                        @csrf
                        <input type="hidden" name="subject" id="test_subject">
                        <input type="hidden" name="body"    id="test_body">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-8">
                                <label for="test_email" class="form-label">Email de test</label>
                                <input type="email" class="form-control" id="test_email" name="test_email"
                                       value="{{ auth()->user()->email }}" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-flask me-2"></i>Envoyer un test
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Historique --}}
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history me-2"></i>Historique des envois
                </div>
                <div class="card-body p-0">
                    @if($history->isEmpty())
                        <p class="text-muted p-3 mb-0">Aucun envoi pour le moment.</p>
                    @else
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Sujet</th>
                                    <th>Filtre</th>
                                    <th class="text-center">Dest.</th>
                                    <th>Envoyé par</th>
                                    <th class="text-center">Test</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($history as $log)
                                <tr>
                                    <td class="text-nowrap small text-muted">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="#" class="text-decoration-none"
                                           data-bs-toggle="modal" data-bs-target="#msgModal"
                                           data-subject="{{ e($log->subject) }}"
                                           data-body="{{ e($log->body) }}">
                                            {{ $log->subject }}
                                        </a>
                                    </td>
                                    <td><span class="badge bg-secondary">{{ $log->filter }}</span></td>
                                    <td class="text-center">{{ $log->recipient_count }}</td>
                                    <td>{{ $log->sentBy?->name ?? '—' }}</td>
                                    <td class="text-center">
                                        @if($log->test_email)
                                            <span class="badge bg-info text-dark" title="{{ $log->test_email }}">Oui</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Modal lecture du message --}}
<div class="modal fade" id="msgModal" tabindex="-1" aria-labelledby="msgModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="msgModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="msgModalBody" class="mb-0" style="white-space:pre-wrap;font-family:inherit;font-size:0.95rem;"></pre>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('msgModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('msgModalLabel').textContent = btn.dataset.subject;
    document.getElementById('msgModalBody').textContent  = btn.dataset.body;
});

// Prévisualisation des destinataires
function fetchRecipients() {
    const filter = document.getElementById('filter').value;
    const exclude = document.getElementById('exclude_technique').checked ? '1' : '0';
    fetch('/admin/mailing/recipients?filter=' + encodeURIComponent(filter) + '&exclude_technique=' + exclude)
        .then(r => r.json())
        .then(data => {
            document.getElementById('recipients-count').textContent = data.count + ' destinataire(s)';
            const ul = document.getElementById('recipients-ul');
            ul.innerHTML = '';
            data.users.forEach(u => {
                const li = document.createElement('li');
                li.className = 'list-group-item py-1';
                li.textContent = u.name + ' — ' + u.email;
                ul.appendChild(li);
            });
        });
}

document.getElementById('filter').addEventListener('change', fetchRecipients);
document.getElementById('exclude_technique').addEventListener('change', fetchRecipients);
fetchRecipients();

// Synchronise les champs cachés du formulaire test avec le formulaire principal
document.querySelector('form[action="/admin/mailing/test"]').addEventListener('submit', function () {
    document.getElementById('test_subject').value = document.getElementById('subject').value;
    document.getElementById('test_body').value    = document.getElementById('body').value;
});
</script>
@endsection
