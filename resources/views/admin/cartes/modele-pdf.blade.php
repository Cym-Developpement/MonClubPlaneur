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

        /* Une carte vierge, délimitée par des pointillés de découpe. */
        .card {
            position: absolute;
            border: 1px dotted #444;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <div class="sheet">
        @foreach($layout['cards'] as $card)
            <div class="card" style="left: {{ $card['x'] }}mm; top: {{ $card['y'] }}mm; width: {{ $layout['cardW'] }}mm; height: {{ $layout['cardH'] }}mm;"></div>
        @endforeach
    </div>
</body>
</html>
