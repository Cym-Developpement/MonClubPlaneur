<?php

namespace App\Models;

use App\Models\flight;
use App\Models\sailplaneStartPrice;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant un aéronef dans le système
 * 
 * @property int $id Identifiant unique de l'aéronef
 * @property string $register Immatriculation de l'aéronef
 * @property int $type Type d'aéronef (1: Avion/Motoplaneur, 2: Planeur)
 * @property float $basePrice Prix de base par heure
 * @property int $motorPriceType Type de comptage moteur (1: Centième, 2: Minutes)
 */
class aircraft extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'aircraft';

    /**
     * Retourne la description textuelle du type d'aéronef
     *
     * @return string Description du type (Avion/Motoplaneur ou Planeur)
     */
    public function getTypeStrAttribute()
    {
        switch ($this->type) {
            case 1:
                return 'Avion/ Motoplaneur';
                break;
            case 2:
                return 'Planeur';
                break;

        }
    }

    /**
     * Retourne la description textuelle du type de comptage moteur
     *
     * @return string Description du type de comptage (Centième ou Minutes)
     */
    public function getMotorPriceTypeStrAttribute()
    {
        switch ($this->motorPriceType) {
            case 1:
                return 'Centiéme';
                break;
            case 2:
                return 'Minutes';
                break;

        }
    }

    /**
     * Récupère le dernier index moteur avant une date donnée
     *
     * @param string $date Date au format Y-m-d
     * @return float Dernier index moteur ou 0 si aucun vol trouvé
     */
    public function getLastIndex($date)
    {
        $last = flight::where('aircraftId', $this->id)
            ->where('flightTimestamp', '<=', strtotime($date))
            ->orderBy('flightTimestamp', 'desc')->first();
        return (!is_null($last)) ? $last->real_motor_end_time : 0;
    }

    /**
     * Récupère le prochain index moteur après une date donnée
     *
     * @param string $date Date au format Y-m-d
     * @return float Prochain index moteur ou 0 si aucun vol trouvé
     */
    public function getNextIndex($date)
    {
        $next = flight::where('aircraftId', $this->id)
            ->where('flightTimestamp', '>=', strtotime($date))
            ->orderBy('flightTimestamp', 'asc')->first();
        return (!is_null($next)) ? $next->real_motor_start_time : 0;
    }

    /**
     * Calcule le prix d'un vol pour cet aéronef
     *
     * @param int $start Timestamp de début du vol
     * @param int $end Timestamp de fin du vol
     * @param int $flightTime Durée du vol en minutes
     * @param int $startType Type de décollage
     * @param float $motorStart Index moteur au départ
     * @param float $motorEnd Index moteur à l'arrivée
     * @param int $nbTakeOff Nombre de décollages
     * @param bool $simulation Indique si c'est une simulation de prix
     * @return array Prix calculé [prix_base, prix_moteur, prix_total, prix_remorquage, [details_moteur]]
     */
    public function price($start, $end, $flightTime, $startType, $motorStart, $motorEnd, $nbTakeOff, $simulation)
    {
        if ($flightTime == 0) {
            $flightTime = (($end - $start) / 60);
        }

        $price = [0, 0, 0, 0, []];
        $motorStart = floatval($motorStart);
        $motorEnd = floatval($motorEnd);

        if ($this->type == 1) {
            $price[0] = ($flightTime * ($this->basePrice / 60));
            if ($motorStart == 0 && $motorEnd == 0) {
                $motorTimeCents = (100 / 60) * $flightTime;
                $price[4] = [$motorTimeCents];
            } elseif ($this->motorPriceType == 1) {
                $motorTimeCents = ($motorEnd - $motorStart) * 100;
                $price[4] = [$motorTimeCents];
            } elseif ($this->motorPriceType == 2) {
                $minutesStart = intval((($motorStart - intval($motorStart)) * (100 / 60)) * 100);
                $minutesEnd = intval((($motorEnd - intval($motorEnd)) * (100 / 60)) * 100);

                $startCents = intval($motorStart) + ($minutesStart / 100);
                $endCents = intval($motorEnd) + ($minutesEnd / 100);

                $motorTimeCents = ($endCents - $startCents) * 100;
                $price[4] = [$minutesStart, $minutesEnd, $startCents, $endCents, $motorTimeCents];
            }
            $price[1] = ($this->motorPrice * ($motorTimeCents / 100));
        } elseif ($this->type == 2) {
            $startTypeElem = sailplaneStartPrice::find($startType);
            $price[2] = (($startTypeElem->basePrice * $nbTakeOff) / 100);
            if ($startTypeElem->byMinutes == 1) {
                // Convertit les centièmes d'heures en minutes via helpers
                $minutes = \App\H::centiToMinutes(($motorEnd - $motorStart) * 100);
                $price[2] = $price[2] * $minutes;
            }
            $price[0] = ($flightTime * ($this->basePrice / 60));
            //dd([$this->basePrice, $flightTime]);
        }

        $price[0] = $price[0] * 100;
        $price[1] = $price[1] * 100;
        $price[2] = $price[2] * 100;
        $price[3] = $price[0] + $price[1] + $price[2];
        if ($price[3] < $this->minPrice) {
            $price[3] = $this->minPrice;
        }
        $price[3] = intval($price[3]);
        return $price;
    }

    /**
     * Retourne la liste des aéronefs utilisés par la tour
     *
     * @return \Illuminate\Database\Eloquent\Collection|aircraft[]
     */
    public static function getTowerList()
    {
        return aircraft::where('isTower', 1)->get();
    }
}
