@php
    $logoParam       = \App\Models\parametre::getValue('club-logo', '');
    $nomCourt        = \App\Models\parametre::getValue('club-nom_court', 'CVVT');
    $nomComplet      = \App\Models\parametre::getValue('club-nom_complet', 'Club de Vol à Voile de Thionville');
    $emailClub       = \App\Models\parametre::getValue('club-email', '');
    $iban            = \App\Models\parametre::getValue('paiement-iban', 'FR76 1333 5004 0108 9253 9002 919');
    $cbUrl           = \App\Models\parametre::getValue('paiement-cb_url', '');
    $cbActif         = (bool) \App\Models\parametre::getValue('paiement-cb_actif', '1');
    $virementActif   = (bool) \App\Models\parametre::getValue('paiement-virement_actif', '1');
    $totalAmount = 0;
    foreach ($transactions as $t) {
        $totalAmount += abs(floatval(str_replace(',', '.', $t['value'])));
    }
@endphp
<style type="text/css">
    * {
        font-family: 'DejaVu Sans', sans-serif;
        color: #2c2c2c;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* ── En-tête ── */
    #header {
        background-color: #1a3a6b;
        padding: 24px 32px;
    }
    #header-logo {
        width: 20%;
        text-align: left;
        vertical-align: middle;
    }
    #header-logo img {
        max-width: 110px;
        max-height: 70px;
    }
    #header-text {
        width: 80%;
        text-align: right;
        vertical-align: middle;
        padding-left: 16px;
    }
    #header-text .club-name {
        color: #ffffff;
        font-size: 18px;
        font-weight: bold;
        letter-spacing: 1px;
    }
    #header-text .club-full {
        color: #a8c4e8;
        font-size: 11px;
        margin-top: 3px;
    }
    #header-text .doc-title {
        color: #ffffff;
        font-size: 13px;
        margin-top: 10px;
        font-weight: bold;
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    /* ── Sous-en-tête ── */
    #subheader {
        background-color: #f0f4fa;
        border-bottom: 2px solid #1a3a6b;
        padding: 12px 32px;
        margin-bottom: 24px;
    }
    #subheader table {
        border: none;
        width: 100%;
    }
    #subheader td {
        border: none;
        height: auto;
        padding: 0 0 0 0;
        background: none;
        vertical-align: top;
    }
    .sh-label {
        color: #666666;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .sh-value {
        color: #1a3a6b;
        font-size: 13px;
        font-weight: bold;
        margin-top: 2px;
    }
    .sh-value-sm {
        color: #1a3a6b;
        font-size: 11px;
        font-weight: bold;
        margin-top: 2px;
    }

    /* ── Tableau transactions ── */
    #container {
        padding: 0 32px;
    }
    table.transactions {
        border-collapse: collapse;
        width: 100%;
        border: none;
        font-size: 12px;
    }
    table.transactions thead tr {
        background-color: #1a3a6b;
    }
    table.transactions thead th {
        color: #ffffff;
        font-size: 11px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 10px 12px;
        border: none;
        height: auto;
        text-align: left;
    }
    table.transactions thead th.col-right {
        text-align: right;
    }
    table.transactions tbody tr:nth-child(even) {
        background-color: #f7f9fc;
    }
    table.transactions tbody tr:nth-child(odd) {
        background-color: #ffffff;
    }
    table.transactions tbody td {
        padding: 9px 12px;
        border: none;
        border-bottom: 1px solid #e8edf4;
        height: auto;
        vertical-align: middle;
        font-size: 12px;
    }
    table.transactions tbody td.col-date {
        color: #555555;
        font-size: 11px;
        white-space: nowrap;
        text-align: center;
        width: 80px;
    }
    table.transactions tbody td.col-desc {
        color: #2c2c2c;
    }
    .col-obs {
        color: #888888;
        font-size: 10px;
        font-style: italic;
    }
    table.transactions tbody td.col-amount {
        text-align: right;
        white-space: nowrap;
        width: 90px;
        font-weight: bold;
        color: #c0392b;
    }

    /* ── Bloc total ── */
    #total-block {
        margin: 28px 32px 0;
        padding: 14px 20px;
        border-left: 4px solid #1a3a6b;
        background-color: #f0f4fa;
        text-align: right;
    }
    #total-block .total-label {
        color: #555555;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    #total-block .total-value {
        font-size: 22px;
        font-weight: bold;
        color: #1a3a6b;
        margin-top: 4px;
    }
    #total-block .balance-note {
        font-size: 11px;
        font-style: italic;
        margin-top: 6px;
    }
    #total-block .balance-note.ok {
        color: #1a6b3a;
    }
    #total-block .balance-note.neg {
        color: #c0392b;
    }

    /* ── Section paiement ── */
    #payment-block {
        margin: 24px 32px 32px;
        padding: 14px 20px;
        background-color: #f9f9f9;
        border: 1px solid #e0e0e0;
        font-size: 11px;
        color: #555555;
    }
    #payment-block .pay-title {
        font-weight: bold;
        color: #2c2c2c;
        margin-bottom: 8px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    #payment-block .pay-item {
        margin-bottom: 5px;
        padding-left: 10px;
    }
    #payment-block .pay-detail {
        color: #888888;
        font-size: 10px;
        font-style: italic;
        padding-left: 10px;
    }

    #no-transactions {
        margin: 28px 32px;
        padding: 20px;
        background-color: #f9f9f9;
        border: 1px solid #e0e0e0;
        font-size: 13px;
        color: #888888;
        text-align: center;
    }

    .page-break {
        page-break-after: always;
    }
</style>

{{-- ── En-tête ── --}}
<div id="header">
    <table style="width:100%; border:none;">
        <tr>
            <td id="header-logo">
                @if($logoParam)
                    <img src="{{ $logoParam }}">
                @endif
            </td>
            <td id="header-text">
                <div class="club-name">{{ $nomCourt }}</div>
                <div class="club-full">{{ $nomComplet }}</div>
                <div class="doc-title">Facture</div>
            </td>
        </tr>
    </table>
</div>

{{-- ── Sous-en-tête ── --}}
<div id="subheader">
    <table>
        <tr>
            <td style="width:40%;">
                <div class="sh-label">Client</div>
                <div class="sh-value">{{ $selectedUser->name }}</div>
            </td>
            <td style="width:20%; text-align:center;">
                <div class="sh-label">Période</div>
                <div class="sh-value-sm">{{ $year }}</div>
            </td>
            @if(isset($invoiceNumber))
            <td style="width:25%; text-align:center;">
                <div class="sh-label">N° de facture</div>
                <div class="sh-value-sm">{{ $invoiceNumber }}</div>
            </td>
            @endif
            <td style="width:15%; text-align:right;">
                <div class="sh-label">Date d'émission</div>
                <div class="sh-value-sm">{{ date('d/m/Y') }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- ── Tableau des transactions ── --}}
<div id="container">
    @if (count($transactions) > 0)
    <table class="transactions">
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th class="col-right">Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $transaction)
            <tr>
                <td class="col-date">{{ $transaction['time'] }}</td>
                <td class="col-desc">
                    {{ $transaction['name'] }}
                    @if($transaction['observation'] != '')
                        <br><span class="col-obs">{{ $transaction['observation'] }}</span>
                    @endif
                </td>
                <td class="col-amount">
                    {{ number_format(abs(floatval(str_replace(',', '.', $transaction['value']))), 2) }}€
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@if (count($transactions) > 0)

{{-- ── Total ── --}}
<div id="total-block">
    <div class="total-label">Montant total</div>
    <div class="total-value">{{ number_format($totalAmount, 2) }}€</div>
    <div class="balance-note {{ $currentBalance < 0 ? 'neg' : 'ok' }}">
        Solde du compte :
        @if($currentBalance >= 0)
            créditeur de {{ number_format($currentBalance, 2) }}€
        @else
            débiteur de {{ number_format(abs($currentBalance), 2) }}€
        @endif
    </div>
</div>

{{-- ── Informations de paiement ── --}}
@if($virementActif || ($cbActif && $cbUrl))
<div id="payment-block">
    <div class="pay-title">Pour régler cette facture</div>
    @if($virementActif)
    <div class="pay-item">Par virement — indiquer votre nom dans le libellé</div>
    <div class="pay-detail">IBAN : {{ $iban }}</div>
    @endif
    @if($cbActif && $cbUrl)
    <div class="pay-item" style="{{ $virementActif ? 'margin-top:5px;' : '' }}">Par carte bancaire</div>
    <div class="pay-detail">{{ $cbUrl }}</div>
    @endif
</div>
@endif

@else

<div id="no-transactions">
    Aucune transaction trouvée pour cette période.
</div>

@endif
