<?php
namespace App;

// Fichier d'aide pour les fonctions utilitaires
// Ce fichier est requis par composer mais peut être vide si aucune fonction utilitaire n'est définie

/**
 * Classe utilitaire H (Helpers)
 * - Conversion minutes ↔ centièmes d'heure
 */
class H
{
    /**
     * Convertit des minutes en centièmes d'heure (1h = 100 centièmes)
     * Exemple: 30 min → 50
     *
     * @param float|int $minutes
     * @return int centièmes d'heure
     */
    public static function minutesToCenti($minutes)
    {
        return (int) round(($minutes * 100) / 60);
    }

    /**
     * Convertit des centièmes d'heure en minutes
     * Exemple: 50 → 30 min
     *
     * @param float|int $centi
     * @return int minutes
     */
    public static function centiToMinutes($centi)
    {
        return (int) round(($centi * 60) / 100);
    }
}
