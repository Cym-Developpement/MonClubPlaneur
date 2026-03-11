@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

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

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-file-invoice me-2"></i>Facturation — émission en lot</span>
                </div>
                <div class="card-body">

                    <p class="text-muted mb-4">
                        Émet une facture définitive pour chaque membre ayant des transactions non facturées
                        jusqu'à la date de fin choisie.
                    </p>

                    <div class="row align-items-end g-3">
                        <div class="col-sm-auto">
                            <label for="batchPeriodEnd" class="form-label">Date de fin commune</label>
                            <input type="date" id="batchPeriodEnd" class="form-control"
                                   value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-sm-auto">
                            <button type="button" class="btn btn-warning" id="btnEmitAll"
                                    onclick="emitAll()">
                                <i class="fas fa-lock me-1"></i>Émettre toutes
                            </button>
                        </div>
                    </div>

                    <div id="batchResult" class="mt-4" style="display:none;">
                        <div id="batchSpinner" class="text-center py-3" style="display:none;">
                            <div class="spinner-border text-warning" role="status"></div>
                            <p class="mt-2 text-muted">Émission en cours…</p>
                        </div>
                        <div id="batchSuccess" style="display:none;">
                            <div class="alert alert-success" id="batchSummary"></div>
                            <table class="table table-sm" id="batchTable">
                                <thead class="table-light">
                                    <tr><th>Membre (ID)</th><th>Facture</th></tr>
                                </thead>
                                <tbody id="batchTableBody"></tbody>
                            </table>
                        </div>
                        <div id="batchError" style="display:none;">
                            <div class="alert alert-danger" id="batchErrorMsg"></div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
function emitAll() {
    var periodEnd = document.getElementById('batchPeriodEnd').value;
    if (!periodEnd) { alert('Veuillez choisir une date.'); return; }
    if (!confirm('Émettre les factures pour tous les membres ayant des transactions non facturées jusqu\'au ' + periodEnd + ' ?')) return;

    document.getElementById('btnEmitAll').disabled = true;
    document.getElementById('batchResult').style.display = 'block';
    document.getElementById('batchSpinner').style.display = 'block';
    document.getElementById('batchSuccess').style.display = 'none';
    document.getElementById('batchError').style.display   = 'none';

    $.ajax({
        url: '{{ route('invoiceLockAll') }}',
        method: 'POST',
        data: { periodEnd: periodEnd, _token: '{{ csrf_token() }}' },
        success: function(res) {
            document.getElementById('batchSpinner').style.display = 'none';
            document.getElementById('batchSuccess').style.display = 'block';
            document.getElementById('batchSummary').textContent = res.created + ' facture(s) émise(s).';
            var tbody = document.getElementById('batchTableBody');
            tbody.innerHTML = '';
            res.invoices.forEach(function(row) {
                tbody.innerHTML += '<tr><td>' + row.userId + '</td><td>' + row.invoice + '</td></tr>';
            });
            if (res.created === 0) {
                document.getElementById('batchSummary').textContent = 'Aucune transaction non facturée trouvée pour cette période.';
            }
        },
        error: function(xhr) {
            document.getElementById('batchSpinner').style.display = 'none';
            document.getElementById('batchError').style.display   = 'block';
            document.getElementById('batchErrorMsg').textContent = 'Erreur : ' + (xhr.responseJSON?.message ?? xhr.statusText);
        },
        complete: function() {
            document.getElementById('btnEmitAll').disabled = false;
        }
    });
}
</script>
@endsection
