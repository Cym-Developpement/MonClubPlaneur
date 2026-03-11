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
                                                <th>Membre</th>
                                                <th>Début de période</th>
                                                <th class="text-center">Nb transactions</th>
                                                <th class="text-end">Montant total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="previewTableBody"></tbody>
                                        <tfoot class="table-light fw-bold">
                                            <tr>
                                                <td colspan="3" class="text-end">Total</td>
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

            {{-- ── Liste de toutes les factures ── --}}
            <div class="card">
                <div class="card-header"><i class="fas fa-list me-2"></i>Toutes les factures</div>

                @if($invoices->isEmpty())
                    <div class="card-body">
                        <p class="text-muted mb-0">Aucune facture émise pour l'instant.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0 align-middle">
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
                                    <td class="text-end text-nowrap fw-semibold {{ $inv->totalAmount < 0 ? 'text-danger' : 'text-success' }}">
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
                        </table>
                    </div>

                    @if($invoices->hasPages())
                    <div class="card-footer">
                        {{ $invoices->links() }}
                    </div>
                    @endif
                @endif
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
            rows.forEach(function(row) {
                grandTotal += row.total;
                var amount = Math.abs(row.total / 100).toFixed(2).replace('.', ',') + '\u00a0€';
                tbody.innerHTML += '<tr>'
                    + '<td>' + row.userName + '</td>'
                    + '<td class="small">' + row.periodStart + '</td>'
                    + '<td class="text-center">' + row.count + '</td>'
                    + '<td class="text-end text-danger fw-semibold">' + amount + '</td>'
                    + '</tr>';
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

function resetPreview() {
    document.getElementById('previewZone').style.display = 'none';
}

function confirmEmit() {
    var n = document.getElementById('previewCount').textContent;
    return confirm('Émettre ' + n + ' facture(s) définitive(s)\u00a0? Cette action est irréversible.');
}
</script>
@endsection
