@php
    // En-tête de carte : logo (1/3 de la largeur) à gauche, titre + nom du club à droite.
    $logoWidth = round($layout['cardW'] / 3, 1);
    $inset     = 2;                                          // marge intérieure (mm)
    $textLeft  = round($inset + $logoWidth + 3, 1);          // début du texte, à droite du logo (mm)
    $titleSize = max(9, (int) round($layout['cardW'] / 5));  // taille du titre (pt)
    $clubSize  = max(6, (int) round($layout['cardW'] / 11)); // taille du nom du club (pt)

    // Corps : prix + cases à cocher (consommations).
    $prix      = isset($prix) ? (float) $prix : 0;
    $nbCases   = isset($nbCases) ? (int) $nbCases : 0;
    $bodyTop   = round($inset + $logoWidth + 3, 1);          // corps sous l'en-tête (mm)
    $priceSize = max(9, (int) round($layout['cardW'] / 8));  // taille du prix (pt)
    $boxSize   = max(5, (int) round($layout['cardW'] / 12)); // côté d'une case (mm)
    $boxGap    = 2;                                          // espace entre cases (mm)
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
        .case {
            display: inline-block;
            width: {{ $boxSize }}mm;
            height: {{ $boxSize }}mm;
            border: 1px solid #1a3a6b;
            margin: 0 {{ $boxGap }}mm {{ $boxGap }}mm 0;
            vertical-align: top;
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
                    @if($prix > 0)
                        <div class="card-price">{{ $prixLabel }}</div>
                    @endif
                    @for($i = 0; $i < $nbCases; $i++)
                        <span class="case"></span>
                    @endfor
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>
