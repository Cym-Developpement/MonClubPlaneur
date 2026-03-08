@php
    $logoParam        = \App\Models\parametre::getValue('club-logo', '');
    $nomCourt         = \App\Models\parametre::getValue('club-nom_court', 'CVVT');
    $nomComplet       = \App\Models\parametre::getValue('club-nom_complet', 'Club de Vol à Voile de Thionville');
    $iban             = \App\Models\parametre::getValue('paiement-iban', 'FR76 1333 5004 0108 9253 9002 919');
    $cbUrl            = $selectedUser->pay_link;
    $cbActif          = (bool) \App\Models\parametre::getValue('paiement-cb_actif', '1');
    $virementActif    = (bool) \App\Models\parametre::getValue('paiement-virement_actif', '1');
    $firstTr          = count($transactions) > 0 ? $transactions[0] : null;
    $lastTr           = count($transactions) > 0 ? $transactions[count($transactions) - 1] : null;
    $periodeDebut     = $firstTr ? $firstTr['time'] : null;
    $periodeFin       = date('d/m/Y');
    $soldeDepart      = $firstTr ? $firstTr['solde'] : null;
    $soldeFinal       = $lastTr  ? $lastTr['solde']  : null;
    $lastSolde        = $soldeFinal;
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
        margin-bottom: 0;
    }
    #header-inner {
        width: 100%;
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
    #subheader td, #subheader th {
        border: none;
        height: auto;
        padding: 0;
        font-size: 12px;
        background: none;
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
    table.transactions tbody td.col-obs {
        color: #888888;
        font-size: 10px;
        font-style: italic;
        margin-top: 2px;
    }
    table.transactions tbody td.col-amount {
        text-align: right;
        white-space: nowrap;
        width: 80px;
        font-weight: bold;
    }
    table.transactions tbody td.col-amount.positive {
        color: #1a6b3a;
    }
    table.transactions tbody td.col-amount.negative {
        color: #c0392b;
    }
    table.transactions tbody td.col-solde {
        text-align: right;
        white-space: nowrap;
        width: 90px;
        font-weight: bold;
    }
    table.transactions tbody td.col-solde.solde-ok {
        color: #1a6b3a;
    }
    table.transactions tbody td.col-solde.solde-neg {
        color: #c0392b;
        background-color: #fff0f0;
    }

    /* ── Bloc solde final ── */
    #solde-block {
        margin: 28px 32px 0;
        padding: 14px 20px;
        border-left: 4px solid #1a3a6b;
        background-color: #f0f4fa;
        font-size: 13px;
    }
    #solde-block .solde-label {
        color: #555555;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    #solde-block .solde-value {
        font-size: 20px;
        font-weight: bold;
        margin-top: 4px;
    }
    #solde-block .solde-value.ok {
        color: #1a6b3a;
    }
    #solde-block .solde-value.neg {
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

    .page-break {
        page-break-after: always;
    }
</style>

{{-- ── En-tête ── --}}
<div id="header">
    <table id="header-inner">
        <tr>
            <td id="header-logo">
                @if($logoParam)
                    <img src="{{ $logoParam }}">
                @endif
            </td>
            <td id="header-text">
                <div class="club-name">{{ $nomCourt }}</div>
                <div class="club-full">{{ $nomComplet }}</div>
                <div class="doc-title">Extrait de compte</div>
            </td>
        </tr>
    </table>
</div>

{{-- ── Sous-en-tête : pilote + période + date ── --}}
<div id="subheader">
    <table>
        <tr>
            <td style="width:45%;">
                <div class="sh-label">Pilote</div>
                <div class="sh-value">{{ $selectedUser->name }}</div>
            </td>
            <td style="width:35%; text-align:center;">
                @if($periodeDebut && $periodeFin)
                <div class="sh-label">Période</div>
                <div class="sh-value" style="font-size:11px;">{{ $periodeDebut }} → {{ $periodeFin }}</div>
                @endif
                @if($soldeDepart !== null && $soldeFinal !== null)
                <div class="sh-label" style="margin-top:5px;">Solde initial → Solde final</div>
                <div class="sh-value" style="font-size:11px;">
                    <span style="color:{{ $soldeDepart < 0 ? '#c0392b' : '#1a6b3a' }}">{{ $soldeDepart }}€</span> → <span style="color:{{ $soldeFinal < 0 ? '#c0392b' : '#1a6b3a' }}">{{ $soldeFinal }}€</span>
                </div>
                @endif
            </td>
            <td style="width:20%; text-align:right;">
                <div class="sh-label">Date d'édition</div>
                <div class="sh-value">{{ date('d/m/Y') }}</div>
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
                <th class="col-right">Solde</th>
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
                <td class="col-amount {{ $transaction['value'] >= 0 ? 'positive' : 'negative' }}">
                    {{ $transaction['value'] >= 0 ? '+' : '' }}{{ $transaction['value'] }}€
                </td>
                <td class="col-solde {{ $transaction['solde'] < 0 ? 'solde-neg' : 'solde-ok' }}">
                    {{ $transaction['solde'] }}€
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- ── Solde final ── --}}
@if($lastSolde !== null)
<div id="solde-block">
    <div class="solde-label">Solde du compte</div>
    <div class="solde-value {{ $lastSolde < 0 ? 'neg' : 'ok' }}">{{ $lastSolde }}€</div>
</div>
@endif

{{-- ── Informations de paiement ── --}}
@if($virementActif || ($cbActif && $cbUrl))
<div id="payment-block">
    <div class="pay-title">Pour approvisionner votre compte</div>
    @if($virementActif)
    <div class="pay-item">Par virement — indiquer votre nom dans le libellé</div>
    <div class="pay-detail">IBAN : {{ $iban }}</div>
    @endif
    @if($cbActif && $cbUrl)
    <div class="pay-item" style="{{ $virementActif ? 'margin-top:5px;' : '' }}">Par carte bancaire — <a href="{{ $cbUrl }}">Cliquez ici</a></div>
    @endif
    <div class="pay-detail" style="margin-top:8px;">Ou connectez-vous à votre compte : <a href="{{ config('app.url') }}">{{ config('app.url') }}</a></div>
</div>
@endif
