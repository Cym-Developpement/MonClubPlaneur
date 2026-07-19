@php
    // En-tête de carte : logo (1/3 de la largeur) à gauche, titre + nom du club à droite.
    $logoWidth = round($layout['cardW'] / 3, 1);
    $inset     = 2;                                          // marge intérieure (mm)
    $textLeft  = round($inset + $logoWidth + 3, 1);          // début du texte, à droite du logo (mm)
    $titleSize = max(9, (int) round($layout['cardW'] / 5));  // taille du titre (pt)
    $clubSize  = max(6, (int) round($layout['cardW'] / 11)); // taille du nom du club (pt)

    // Corps : champs à remplir + prix + cases à cocher (consommations).
    $prix      = isset($prix) ? (float) $prix : 0;
    $nbCases   = isset($nbCases) ? (int) $nbCases : 0;
    $bodyTop   = round($inset + $logoWidth + 3, 1);          // corps sous l'en-tête (mm)
    $fieldSize = max(7, (int) round($layout['cardW'] / 12)); // champs NOM/DATE (pt)
    $priceSize = max(9, (int) round($layout['cardW'] / 4));  // taille du prix (doublée)
    $footSize  = max(6, (int) round($layout['cardW'] / 16)); // texte d'explication (pt)
    $boxGap    = 2;                                          // espace entre cases (mm)

    // Réservation verticale (mm) : en-tête + champs + prix + explication en bas.
    $fieldsH   = (int) round(2 * ($fieldSize * 0.42 + 3.5) + 3);
    $priceH    = (int) round($priceSize * 0.42 + 4);
    $footerH   = (int) round(3 * $footSize * 0.5 + 5);
    $casesH    = max(12, $layout['cardH'] - $bodyTop - $fieldsH - $priceH - $footerH - $inset);

    // Taille de case : limitée par la largeur ET la hauteur restante (2 lignes).
    $perRow    = max(1, (int) ceil($nbCases / 2));           // 2 lignes, même nombre par ligne
    $availW    = $layout['cardW'] - 2 * $inset;
    $boxByW    = (int) floor(($availW - ($perRow + 1) * $boxGap) / $perRow);
    $boxByH    = (int) floor(($casesH - 3 * $boxGap) / 2);
    $boxSize   = max(6, min(22, $boxByW, $boxByH));          // côté case (mm)
    $caseFont  = max(8, (int) round($boxSize * 1.4));        // le « 1 » dans la case (pt)
    $caseRows  = $nbCases > 0 ? array_chunk(range(1, $nbCases), $perRow) : [];
    $prixLabel = ($prix == floor($prix))
        ? number_format($prix, 0, ',', ' ') . ' €'
        : number_format($prix, 2, ',', ' ') . ' €';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        html, body { margin: 0; padding: 0; }

        .sheet {
            position: relative;
            width: {{ $layout['pageW'] }}mm;
            height: {{ $layout['pageH'] }}mm;
        }

        /* Une carte, délimitée par des pointillés de découpe. */
        .card {
            position: absolute;
            border: 1px dotted #444;
            box-sizing: border-box;
            font-family: 'DejaVu Sans', sans-serif;
        }

        /* Logo du club : coin haut-gauche, 1/3 de la largeur de la carte. */
        .card-logo {
            position: absolute;
            top: {{ $inset }}mm;
            left: {{ $inset }}mm;
            width: {{ $logoWidth }}mm;
            height: auto;
        }

        /* En-tête texte, à droite du logo. */
        .card-head {
            position: absolute;
            top: {{ $inset }}mm;
            left: {{ $textLeft }}mm;
            right: {{ $inset }}mm;
        }
        .card-title {
            font-size: {{ $titleSize }}pt;
            font-weight: bold;
            color: #1a3a6b;
            line-height: 1.1;
            text-align: center;
            text-transform: uppercase;
        }
        .card-club {
            font-size: {{ $clubSize }}pt;
            font-weight: bold;
            color: #1a3a6b;
            margin-top: 1mm;
            text-align: center;
        }

        /* Corps : prix + cases à cocher. */
        .card-body-content {
            position: absolute;
            top: {{ $bodyTop }}mm;
            left: {{ $inset }}mm;
            right: {{ $inset }}mm;
            text-align: center;
        }
        .card-price {
            font-size: {{ $priceSize }}pt;
            font-weight: bold;
            color: #1a3a6b;
            margin-bottom: 2mm;
        }

        /* Champs à remplir à la main (NOM/PRÉNOM, DATE). */
        .fields { text-align: left; margin-bottom: 3mm; }
        .field {
            border-bottom: 1px dotted #444;
            padding-bottom: 0.5mm;
            margin-bottom: 3mm;
            font-size: {{ $fieldSize }}pt;
            color: #333;
        }
        .field-label { font-weight: bold; color: #1a3a6b; }

        /* Cases : 2 lignes de même longueur, un « 1 » centré par case. */
        .cases {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: {{ $boxGap }}mm;
        }
        .cases td { padding: 0; }
        .cases td.case {
            height: {{ $boxSize }}mm;
            border: 1px solid #1a3a6b;
            text-align: center;
            vertical-align: middle;
            line-height: 1;
            font-size: {{ $caseFont }}pt;
            font-weight: bold;
            color: #1a3a6b;
        }

        /* Explication du fonctionnement, réservée en bas de la carte. */
        .card-footer {
            position: absolute;
            left: {{ $inset }}mm;
            right: {{ $inset }}mm;
            bottom: {{ $inset }}mm;
            font-size: {{ $footSize }}pt;
            color: #555;
            text-align: center;
            line-height: 1.25;
        }
    </style>
</head>
<body>
    <div class="sheet">
        @foreach($layout['cards'] as $card)
            <div class="card" style="left: {{ $card['x'] }}mm; top: {{ $card['y'] }}mm; width: {{ $layout['cardW'] }}mm; height: {{ $layout['cardH'] }}mm;">
                @if(!empty($logo))
                    <img class="card-logo" src="{{ $logo }}" alt="">
                @endif
                <div class="card-head">
                    <div class="card-title">Carte de Bar</div>
                    @if(!empty($clubName))
                        <div class="card-club">{{ $clubName }}</div>
                    @endif
                </div>
                <div class="card-body-content">
                    <div class="fields">
                        <div class="field"><span class="field-label">NOM / PRÉNOM :</span></div>
                        <div class="field"><span class="field-label">DATE :</span></div>
                    </div>
                    @if($prix > 0)
                        <div class="card-price">{{ $prixLabel }}</div>
                    @endif
                    @if($nbCases > 0)
                        <table class="cases">
                            @foreach($caseRows as $row)
                                <tr>
                                    @foreach($row as $n)
                                        <td class="case">1</td>
                                    @endforeach
                                    @for($k = count($row); $k < $perRow; $k++)
                                        <td></td>
                                    @endfor
                                </tr>
                            @endforeach
                        </table>
                    @endif
                </div>
                <div class="card-footer">Chaque case correspond à une consommation : cochez-en une à chaque boisson servie. Carte nominative, valable uniquement au bar du club.</div>
            </div>
        @endforeach
    </div>
</body>
</html>
