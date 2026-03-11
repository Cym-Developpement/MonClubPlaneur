<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
@php
    $logoParam   = \App\Models\parametre::getValue('club-logo', '');
    $nomCourt    = \App\Models\parametre::getValue('club-nom_court', 'Club');
    $nomComplet  = \App\Models\parametre::getValue('club-nom_complet', '');
    $adresse     = \App\Models\parametre::getValue('club-adresse', '');
    $siret       = \App\Models\parametre::getValue('club-siret', '');
    $emailClub   = \App\Models\parametre::getValue('club-email', '');

    $isAvoir   = ($invoiceType === 'avoir');
    $isDraft   = ($invoiceType === 'brouillon');
    $docTitle  = $isAvoir ? 'Avoir' : ($isDraft ? 'Brouillon de facture' : 'Facture');

    $periodStartLabel = $periodStart > 0 ? date('d/m/Y', $periodStart) : 'Origine';
    $periodEndLabel   = date('d/m/Y', $periodEnd);

    $totalEur = number_format(abs($totalAmount / 100), 2, ',', ' ');
@endphp
<style type="text/css">
    * {
        font-family: 'DejaVu Sans', sans-serif;
        color: #2c2c2c;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    #header {
        background-color: #1a3a6b;
        padding: 24px 32px;
    }
    #header-logo { width: 20%; text-align: left; vertical-align: middle; }
    #header-logo img { max-width: 110px; max-height: 70px; }
    #header-text { width: 80%; text-align: right; vertical-align: middle; padding-left: 16px; }
    #header-text .club-name { color: #ffffff; font-size: 18px; font-weight: bold; letter-spacing: 1px; }
    #header-text .club-full { color: #a8c4e8; font-size: 11px; margin-top: 3px; }
    #header-text .club-info { color: #a8c4e8; font-size: 10px; margin-top: 2px; }
    #header-text .doc-title {
        color: #ffffff; font-size: 13px; margin-top: 10px;
        font-weight: bold; letter-spacing: 2px; text-transform: uppercase;
    }

    #subheader {
        background-color: #f0f4fa;
        border-bottom: 2px solid #1a3a6b;
        padding: 12px 32px;
        margin-bottom: 24px;
    }
    #subheader table { border: none; width: 100%; }
    #subheader td { border: none; height: auto; padding: 0; background: none; vertical-align: top; }
    .sh-label { color: #666666; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; }
    .sh-value { color: #1a3a6b; font-size: 13px; font-weight: bold; margin-top: 2px; }
    .sh-value-sm { color: #1a3a6b; font-size: 11px; font-weight: bold; margin-top: 2px; }

    #container { padding: 0 32px; }
    table.transactions { border-collapse: collapse; width: 100%; border: none; font-size: 12px; }
    table.transactions thead tr { background-color: #1a3a6b; }
    table.transactions thead th {
        color: #ffffff; font-size: 11px; font-weight: bold;
        text-transform: uppercase; letter-spacing: 0.5px;
        padding: 10px 12px; border: none; height: auto; text-align: left;
    }
    table.transactions thead th.col-right { text-align: right; }
    table.transactions tbody tr:nth-child(even) { background-color: #f7f9fc; }
    table.transactions tbody tr:nth-child(odd)  { background-color: #ffffff; }
    table.transactions tbody td {
        padding: 9px 12px; border: none;
        border-bottom: 1px solid #e8edf4;
        height: auto; vertical-align: middle; font-size: 12px;
    }
    table.transactions tbody td.col-date  { color: #555555; font-size: 11px; white-space: nowrap; width: 80px; }
    table.transactions tbody td.col-desc  { color: #2c2c2c; }
    .col-obs { color: #888888; font-size: 10px; font-style: italic; }
    table.transactions tbody td.col-amount { text-align: right; white-space: nowrap; width: 90px; font-weight: bold; color: #c0392b; }

    #total-block {
        margin: 28px 32px 0;
        padding: 14px 20px;
        border-left: 4px solid #1a3a6b;
        background-color: #f0f4fa;
        text-align: right;
    }
    #total-block .total-label { color: #555555; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
    #total-block .total-value { font-size: 22px; font-weight: bold; color: #1a3a6b; margin-top: 4px; }

    #related-block {
        margin: 12px 32px 0;
        padding: 10px 16px;
        background-color: #fff8e6;
        border-left: 4px solid #e6a817;
        font-size: 11px;
        color: #7a5500;
    }

    #draft-banner {
        margin: 0 32px 20px;
        padding: 10px 16px;
        background-color: #fff3cd;
        border-left: 4px solid #f0ad4e;
        font-size: 12px;
        font-weight: bold;
        color: #856404;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    #no-transactions {
        margin: 28px 32px; padding: 20px;
        background-color: #f9f9f9; border: 1px solid #e0e0e0;
        font-size: 13px; color: #888888; text-align: center;
    }
</style>

{{-- En-tête --}}
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
                @if($nomComplet)
                    <div class="club-full">{{ $nomComplet }}</div>
                @endif
                @if($adresse)
                    <div class="club-info">{{ $adresse }}</div>
                @endif
                @if($siret)
                    <div class="club-info">SIRET : {{ $siret }}</div>
                @endif
                @if($emailClub)
                    <div class="club-info">{{ $emailClub }}</div>
                @endif
                <div class="doc-title">{{ $docTitle }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- Sous-en-tête --}}
<div id="subheader">
    <table>
        <tr>
            <td style="width:35%;">
                <div class="sh-label">Client</div>
                <div class="sh-value">{{ $user->name }}</div>
                @if($user->email)
                    <div class="sh-value-sm" style="color:#555; font-weight:normal;">{{ $user->email }}</div>
                @endif
            </td>
            <td style="width:25%; text-align:center;">
                <div class="sh-label">Période</div>
                <div class="sh-value-sm">{{ $periodStartLabel }} → {{ $periodEndLabel }}</div>
            </td>
            <td style="width:25%; text-align:center;">
                <div class="sh-label">N° de facture</div>
                <div class="sh-value-sm">{{ $invoiceNumber }}</div>
            </td>
            <td style="width:15%; text-align:right;">
                <div class="sh-label">Date d'émission</div>
                <div class="sh-value-sm">{{ $isDraft ? date('d/m/Y') : date('d/m/Y', $emittedAt) }}</div>
            </td>
        </tr>
    </table>
</div>

@if($isDraft)
<div id="draft-banner">Document provisoire — non définitif</div>
@endif

@if($isAvoir && isset($relatedInvoiceNumber))
<div id="related-block">
    Avoir en annulation de la facture {{ $relatedInvoiceNumber }}
</div>
@endif

{{-- Tableau des transactions --}}
<div id="container">
    @if(count($transactionLines) > 0)
    <table class="transactions">
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th class="col-right">Montant</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactionLines as $line)
            <tr>
                <td class="col-date">{{ $line['time'] }}</td>
                <td class="col-desc">
                    {{ $line['name'] }}
                    @if(!empty($line['observation']))
                        <br><span class="col-obs">{{ $line['observation'] }}</span>
                    @endif
                </td>
                <td class="col-amount">{{ $line['value'] }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div id="no-transactions">Aucune transaction sur cette période.</div>
    @endif
</div>

@if(count($transactionLines) > 0)
<div id="total-block">
    <div class="total-label">{{ $isAvoir ? 'Montant de l\'avoir' : 'Montant total dû' }}</div>
    <div class="total-value">{{ $totalEur }} €</div>
</div>
@endif
