@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-11">

            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- ── Émission en lot ── --}}
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-file-invoice me-2"></i>Émission en lot</div>
                <div class="card-body">

                    <div class="row align-items-end g-3 mb-3">
                        <div class="col-sm-auto">
                            <label for="batchPeriodEnd" class="form-label">Date de fin commune</label>
                            <input type="date" id="batchPeriodEnd" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-sm-auto">
                            <button type="button" class="btn btn-secondary" id="btnPreviewAll" onclick="loadPreview()">
                                <i class="fas fa-eye me-1"></i>Prévisualiser
                            </button>
                        </div>
                    </div>

                    <div id="previewZone" style="display:none;">
                        <div id="previewSpinner" class="text-center py-3" style="display:none;">
                            <div class="spinner-border text-secondary" role="status"></div>
                        </div>

                        <div id="previewContent" style="display:none;">
                            <div id="previewEmpty" class="alert alert-info" style="display:none;">
                                Aucune transaction non facturée trouvée pour cette période.
                            </div>

                            <div id="previewTable" style="display:none;">
                                <p class="text-muted small mb-2">
                                    <strong id="previewCount"></strong> membre(s) — vérifiez avant de confirmer.
                                </p>
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:30px;"></th>
                                                <th>Membre</th>
                                                <th>Début de période</th>
                                                <th class="text-center">Nb transactions</th>
                                                <th class="text-end">Montant total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="previewTableBody"></tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <td colspan="4" class="text-end">Total</td>
                                                <td class="text-end text-danger" id="previewGrandTotal"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <form id="formEmitAll" method="POST" action="{{ route('invoiceLockAll') }}"
                                      onsubmit="return confirmEmit()">
                                    @csrf
                                    <input type="hidden" name="periodEnd" id="emitPeriodEnd">
                                    <button type="submit" class="btn btn-warning" id="btnEmitAll">
                                        <i class="fas fa-lock me-1"></i>Confirmer l'émission
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary ms-2" onclick="resetPreview()">
                                        Annuler
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div id="previewError" style="display:none;">
                            <div class="alert alert-danger" id="previewErrorMsg"></div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── Envoi par email ── --}}
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-envelope me-2"></i>Envoyer les dernières factures par email</div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Envoie par email la dernière facture non annulée de chaque membre (avec le PDF en pièce jointe).
                        Cochez les factures à envoyer.
                    </p>

                    @if($latestInvoices->isEmpty())
                        <div class="alert alert-info mb-0">Aucune facture à envoyer.</div>
                    @else
                    @php
                        $monthStart    = \Carbon\Carbon::now()->startOfMonth()->timestamp;
                        $monthEnd      = \Carbon\Carbon::now()->endOfMonth()->timestamp;
                        $inMonth       = fn($i) => $i->emittedAt && $i->emittedAt >= $monthStart && $i->emittedAt <= $monthEnd;
                        $allInMonth    = $latestInvoices->every($inMonth);
                    @endphp
                    <form id="sendInvoicesForm" method="POST">
                        @csrf
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width:40px">
                                            <input type="checkbox" id="checkAll" @checked($allInMonth)
                                                   onchange="document.querySelectorAll('.invoice-check').forEach(c => c.checked = this.checked); updateSendButtons();">
                                        </th>
                                        <th>Membre</th>
                                        <th>N° Facture</th>
                                        <th class="text-end">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($latestInvoices->sortByDesc('emittedAt') as $inv)
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" class="invoice-check" name="invoiceIds[]"
                                                   value="{{ $inv->id }}" @checked($inMonth($inv)) onchange="updateSendButtons()">
                                        </td>
                                        <td>{{ $inv->user->name ?? '—' }}</td>
                                        <td>{{ $inv->invoiceNumber }}</td>
                                        <td class="text-end">{{ number_format(abs($inv->totalAmount / 100), 2, ',', ' ') }} €</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" id="btnSendTest" class="btn btn-outline-primary"
                                    formaction="{{ route('admin.invoices.sendTest') }}"
                                    onclick="return confirm('Envoyer les factures cochées à votre adresse email (test)\u00a0?')">
                                <i class="fas fa-vial me-1"></i>Envoi test (à mon email)
                            </button>
                            <button type="submit" id="btnSendReal" class="btn btn-warning"
                                    formaction="{{ route('admin.invoices.send') }}"
                                    onclick="return confirm('Envoyer les factures cochées à chaque membre\u00a0? Cette action enverra un email aux membres concernés.')">
                                <i class="fas fa-paper-plane me-1"></i>Envoyer aux membres
                            </button>
                        </div>
                    </form>

                    <script>
                        function updateSendButtons() {
                            const anyChecked = document.querySelectorAll('.invoice-check:checked').length > 0;
                            document.getElementById('btnSendTest').disabled = !anyChecked;
                            document.getElementById('btnSendReal').disabled = !anyChecked;
                            const allChecked = document.querySelectorAll('.invoice-check:checked').length === document.querySelectorAll('.invoice-check').length;
                            document.getElementById('checkAll').checked = allChecked;
                        }
                        updateSendButtons();
                    </script>
                    @endif
                </div>
            </div>

            {{-- ── Liste de toutes les factures ── --}}
            <div class="card">
                <div class="card-header"><i class="fas fa-list me-2"></i>Toutes les factures</div>
                <div class="table-responsive p-2">
                    <table id="invoicesTable" class="table table-sm table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Numéro</th>
                                <th>Membre</th>
                                <th>Période</th>
                                <th>Émis le</th>
                                <th class="text-end">Montant</th>
                                <th class="text-center">Type</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $inv)
                            @php
                                $isCancelled = $inv->type === 'facture' && $inv->avoir !== null;
                                $isAvoir     = $inv->type === 'avoir';
                            @endphp
                            <tr class="{{ $isCancelled ? 'text-muted' : '' }}">
                                <td class="fw-semibold {{ $isAvoir ? 'text-success' : '' }}">
                                    {{ $inv->invoiceNumber }}
                                    @if($isCancelled)
                                        <small class="text-danger">(annulée)</small>
                                    @endif
                                </td>
                                <td>{{ $inv->user?->name ?? '—' }}</td>
                                <td class="small text-nowrap">
                                    {{ $inv->periodStart > 0 ? date('d/m/Y', $inv->periodStart) : 'Origine' }}
                                    → {{ date('d/m/Y', $inv->periodEnd) }}
                                </td>
                                <td class="small text-nowrap">{{ date('d/m/Y', $inv->emittedAt) }}</td>
                                <td class="text-end text-nowrap fw-semibold {{ $inv->totalAmount < 0 ? 'text-danger' : 'text-success' }}"
                                    data-sort="{{ $inv->totalAmount }}">
                                    {{ number_format(abs($inv->totalAmount / 100), 2, ',', ' ') }} €
                                </td>
                                <td class="text-center">
                                    @if($isAvoir)
                                        <span class="badge bg-success">Avoir</span>
                                        @if($inv->relatedInvoice)
                                            <br><small class="text-muted">← {{ $inv->relatedInvoice->invoiceNumber }}</small>
                                        @endif
                                    @else
                                        <span class="badge bg-primary">Facture</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($inv->pdfPath)
                                        <a href="{{ route('invoicePdf', $inv->id) }}" target="_blank"
                                           class="btn btn-sm btn-outline-secondary py-0 px-2" title="Consulter le PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="4" class="text-end">Total affiché</td>
                                <td class="text-end text-nowrap" id="invoicesTotalFooter"></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function loadPreview() {
    var periodEnd = document.getElementById('batchPeriodEnd').value;
    if (!periodEnd) { alert('Veuillez choisir une date.'); return; }

    document.getElementById('previewZone').style.display    = 'block';
    document.getElementById('previewSpinner').style.display = 'block';
    document.getElementById('previewContent').style.display = 'none';
    document.getElementById('previewError').style.display   = 'none';
    document.getElementById('btnPreviewAll').disabled = true;

    $.ajax({
        url: '{{ route('admin.invoices.preview') }}',
        method: 'GET',
        data: { periodEnd: periodEnd },
        success: function(rows) {
            document.getElementById('previewSpinner').style.display = 'none';
            document.getElementById('previewContent').style.display = 'block';

            if (rows.length === 0) {
                document.getElementById('previewEmpty').style.display = 'block';
                document.getElementById('previewTable').style.display = 'none';
                return;
            }

            document.getElementById('previewEmpty').style.display = 'none';
            document.getElementById('previewTable').style.display = 'block';
            document.getElementById('previewCount').textContent   = rows.length;
            document.getElementById('emitPeriodEnd').value        = periodEnd;

            var tbody = document.getElementById('previewTableBody');
            tbody.innerHTML = '';
            var grandTotal = 0;
            rows.forEach(function(row, idx) {
                grandTotal += row.total;
                var amount = Math.abs(row.total / 100).toFixed(2).replace('.', ',') + '\u00a0€';
                var detailHtml = '';
                if (row.transactions && row.transactions.length) {
                    detailHtml = '<table class="table table-sm table-borderless mb-0 small">'
                        + '<thead><tr><th>Date</th><th>Libellé</th><th class="text-end">Montant</th></tr></thead><tbody>';
                    row.transactions.forEach(function(t) {
                        var tAmount = Math.abs(t.value / 100).toFixed(2).replace('.', ',') + '\u00a0€';
                        detailHtml += '<tr><td class="text-nowrap">' + t.date + '</td><td>' + t.name + '</td><td class="text-end text-danger">' + tAmount + '</td></tr>';
                    });
                    detailHtml += '</tbody></table>';
                }
                tbody.innerHTML += '<tr style="cursor:pointer;" onclick="toggleDetail(' + idx + ')">'
                    + '<td class="text-center"><i class="fas fa-chevron-right" id="chevron' + idx + '" style="transition:transform .2s;"></i></td>'
                    + '<td>' + row.userName + '</td>'
                    + '<td class="small">' + row.periodStart + '</td>'
                    + '<td class="text-center">' + row.count + '</td>'
                    + '<td class="text-end text-danger fw-semibold">' + amount + '</td>'
                    + '</tr>'
                    + '<tr id="detail' + idx + '" style="display:none;"><td></td><td colspan="4" class="bg-light p-2">' + detailHtml + '</td></tr>';
            });
            var gt = Math.abs(grandTotal / 100).toFixed(2).replace('.', ',') + '\u00a0€';
            document.getElementById('previewGrandTotal').textContent = gt;
        },
        error: function(xhr) {
            document.getElementById('previewSpinner').style.display = 'none';
            document.getElementById('previewError').style.display   = 'block';
            document.getElementById('previewErrorMsg').textContent  = 'Erreur\u00a0: ' + (xhr.responseJSON?.message ?? xhr.statusText);
        },
        complete: function() {
            document.getElementById('btnPreviewAll').disabled = false;
        }
    });
}

function toggleDetail(idx) {
    var row = document.getElementById('detail' + idx);
    var chevron = document.getElementById('chevron' + idx);
    if (row.style.display === 'none') {
        row.style.display = '';
        chevron.style.transform = 'rotate(90deg)';
    } else {
        row.style.display = 'none';
        chevron.style.transform = '';
    }
}

function resetPreview() {
    document.getElementById('previewZone').style.display = 'none';
}

function confirmEmit() {
    var n = document.getElementById('previewCount').textContent;
    return confirm('Émettre ' + n + ' facture(s) définitive(s)\u00a0? Cette action est irréversible.');
}
</script>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.min.js"></script>
<script>
function updateInvoicesTotal(table) {
    var total = 0;
    table.column(4, { search: 'applied' }).nodes().each(function(node) {
        total += parseInt($(node).data('sort'), 10) || 0;
    });
    var net     = -total; // totalAmount est négatif pour les factures, positif pour les avoirs
    var abs     = Math.abs(net / 100).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '\u202f');
    var display = (net < 0 ? '-\u00a0' : '') + abs + '\u00a0€';
    $('#invoicesTotalFooter')
        .text(display)
        .removeClass('text-danger text-success')
        .addClass(net < 0 ? 'text-danger' : 'text-success');
}

var invoicesTable = $('#invoicesTable').DataTable({
    order: [[0, 'desc']],
    pageLength: 25,
    language: {
        processing:     "Traitement en cours...",
        search:         "Rechercher&nbsp;:",
        lengthMenu:     "Afficher _MENU_ éléments",
        info:           "Affichage de _START_ à _END_ sur _TOTAL_ éléments",
        infoEmpty:      "Affichage de 0 à 0 sur 0 élément",
        infoFiltered:   "(filtré à partir de _MAX_ éléments au total)",
        loadingRecords: "Chargement en cours...",
        zeroRecords:    "Aucune facture trouvée",
        emptyTable:     "Aucune facture émise pour l'instant.",
        paginate: { first: "Premier", previous: "Précédent", next: "Suivant", last: "Dernier" }
    },
    columnDefs: [{ orderable: false, targets: [6] }]
});

updateInvoicesTotal(invoicesTable);
invoicesTable.on('search.dt draw.dt', function() {
    updateInvoicesTotal(invoicesTable);
});
</script>
@endpush
