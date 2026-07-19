<?php

namespace App\Services;

use App\Models\parametre;

/**
 * Calcule l'imposition des cartes de bar sur une planche imprimable
 * (ex. 4×A6 sur A4), avec repères de découpe en pointillés.
 *
 * Toutes les dimensions sont exprimées en millimètres.
 */
class CarteBarService
{
    /** Dimensions des formats de page en millimètres [largeur, hauteur] en portrait. */
    private const FORMATS = [
        'A3' => [297, 420],
        'A4' => [210, 297],
        'A5' => [148, 210],
    ];

    /** Clés de paramètres et valeurs par défaut (planche 4×A6 sur A4). */
    public const DEFAULTS = [
        'largeur_mm'    => 105,   // largeur d'une carte (A6)
        'hauteur_mm'    => 148,   // hauteur d'une carte (A6)
        'format_page'   => 'A4',
        'orientation'   => 'portrait',
        'marge_mm'      => 0,
        'espacement_mm' => 0,
        'prix'          => 10,   // prix de la carte (€)
        'nb_cases'      => 10,   // nombre de cases à cocher (consommations)
    ];

    /** Lit la configuration courante depuis les paramètres du club. */
    public static function config(): array
    {
        return [
            'largeur_mm'    => (int) parametre::getValue('cartebar-largeur_mm', self::DEFAULTS['largeur_mm']),
            'hauteur_mm'    => (int) parametre::getValue('cartebar-hauteur_mm', self::DEFAULTS['hauteur_mm']),
            'format_page'   => (string) parametre::getValue('cartebar-format_page', self::DEFAULTS['format_page']),
            'orientation'   => (string) parametre::getValue('cartebar-orientation', self::DEFAULTS['orientation']),
            'marge_mm'      => (int) parametre::getValue('cartebar-marge_mm', self::DEFAULTS['marge_mm']),
            'espacement_mm' => (int) parametre::getValue('cartebar-espacement_mm', self::DEFAULTS['espacement_mm']),
            'prix'          => (float) parametre::getValue('cartebar-prix', self::DEFAULTS['prix']),
            'nb_cases'      => (int) parametre::getValue('cartebar-nb_cases', self::DEFAULTS['nb_cases']),
        ];
    }

    /** Liste des formats de page disponibles (pour les listes déroulantes). */
    public static function formats(): array
    {
        return array_keys(self::FORMATS);
    }

    /** Dimensions [largeur, hauteur] de la page en mm, orientation comprise. */
    public static function pageSize(string $format, string $orientation): array
    {
        [$w, $h] = self::FORMATS[$format] ?? self::FORMATS['A4'];

        return $orientation === 'paysage' ? [$h, $w] : [$w, $h];
    }

    /**
     * Calcule la disposition des cartes, centrée sur la page.
     *
     * @return array{
     *   pageW:int, pageH:int, cols:int, rows:int, count:int,
     *   cardW:int, cardH:int, gap:int, cards:array<int,array{x:float,y:float}>
     * }
     */
    public static function layout(array $config): array
    {
        [$pageW, $pageH] = self::pageSize($config['format_page'], $config['orientation']);

        $cardW  = max(1, (int) $config['largeur_mm']);
        $cardH  = max(1, (int) $config['hauteur_mm']);
        $margin = max(0, (int) $config['marge_mm']);
        $gap    = max(0, (int) $config['espacement_mm']);

        $usableW = $pageW - 2 * $margin;
        $usableH = $pageH - 2 * $margin;

        $cols = max(0, (int) floor(($usableW + $gap) / ($cardW + $gap)));
        $rows = max(0, (int) floor(($usableH + $gap) / ($cardH + $gap)));

        $gridW = $cols > 0 ? $cols * $cardW + ($cols - 1) * $gap : 0;
        $gridH = $rows > 0 ? $rows * $cardH + ($rows - 1) * $gap : 0;

        $offsetX = ($pageW - $gridW) / 2;
        $offsetY = ($pageH - $gridH) / 2;

        $cards = [];
        for ($r = 0; $r < $rows; $r++) {
            for ($c = 0; $c < $cols; $c++) {
                $cards[] = [
                    'x' => $offsetX + $c * ($cardW + $gap),
                    'y' => $offsetY + $r * ($cardH + $gap),
                ];
            }
        }

        return [
            'pageW' => $pageW,
            'pageH' => $pageH,
            'cols'  => $cols,
            'rows'  => $rows,
            'count' => $cols * $rows,
            'cardW' => $cardW,
            'cardH' => $cardH,
            'gap'   => $gap,
            'cards' => $cards,
        ];
    }
}
