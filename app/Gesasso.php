<?php
namespace App;

use App\Models\aircraft;
use App\Models\flight;
use App\Models\sailplaneStartPrice;
use App\Models\transaction;
use App\Models\User;

/**
 *
 */
class Gesasso
{
    /**
     * Durée du remorqueur, en minutes, au-delà de laquelle le vol n'est plus
     * un remorquage simple mais un convoyage (facturé à la minute).
     */
    public const CONVOYAGE_MINUTES = 12;

    /**
     * Durée du remorqueur en minutes (le CSV la donne en centièmes d'heure).
     *
     * @param array $csv Les données du vol au format CSV
     * @return int
     */
    public static function towingMinutes($csv)
    {
        return \App\H::centiToMinutes(intval($csv[15]));
    }

    /**
     * Indique si le remorqueur a dépassé le seuil de convoyage.
     *
     * @param array $csv Les données du vol au format CSV
     * @return bool
     */
    public static function isConvoyage($csv)
    {
        return self::towingMinutes($csv) > self::CONVOYAGE_MINUTES;
    }

    /**
     * Vérifie si un vol existe déjà dans la base de données
     *
     * @param array $csv Les données du vol au format CSV
     * @return bool True si le vol existe, False sinon
     */
    public static function existFlight($csv)
    {
        /*$exist = flight::where('takeOffTime', self::csvToTakeOff($csv))->where('aircraftId', self::csvToAircraft($csv))->first();
        dd($exist);*/
        return (! is_null(flight::where('takeOffTime', self::csvToTakeOff($csv))->where('aircraftId', self::csvToAircraft($csv))->first()));
    }

    /**
     * Crée un nouvel objet flight à partir des données CSV
     *
     * @param array $csv Les données du vol au format CSV
     * @param int|null $userPayId L'ID de l'utilisateur qui paie (optionnel)
     * @param int|null $startType Le type de départ (optionnel)
     * @return \App\Models\flight L'objet flight créé
     */
    public static function exportToFlight($csv, $userPayId = null, $startType = null)
    {

        $flight                   = new flight();
        $flight->idUser           = self::csvToUser($csv);
        $flight->totalTime        = self::csvToTotalTime($csv);
        $flight->takeOffTime      = self::csvToTakeOff($csv);
        $flight->landingTime      = self::csvToLanding($csv);
        $flight->landing          = intval($csv[13]);
        $flight->aircraftId       = self::csvToAircraft($csv);
        $flight->motorStartTime   = 0;
        $flight->motorEndTime     = self::isConvoyage($csv) ? (intval($csv[15])/100) : 0;
        $flight->airPortStartCode = $csv[11];
        $flight->airPortEndCode   = $csv[12];
        $flight->flightTimestamp  = strtotime(str_replace('/', '-', $flight->takeOffTime));
        if (is_null($userPayId)) {
            $flight->userPayId = $flight->idUser;
        } else {
            $flight->userPayId = $userPayId;
        }

        $flight->startType    = self::csvToAircraftStart($csv, $startType);
        $flight->idInstructor = self::csvToInstructorId($csv);
        //dd($flight);
        return $flight;
    }

    /**
     * Transaction de mise en l'air seule, utilisée quand le planeur n'est pas
     * connu de la base : les heures de vol ne peuvent pas être tarifées, seul
     * le remorquage est facturé au pilote remorqué.
     *
     * Le calcul reprend celui de aircraft::price() pour le poste « départ » :
     * basePrice x nombre de lancements, multiplié par les minutes de remorqueur
     * pour les tarifs à la minute (convoyage).
     *
     * @param array $csv Les données du vol au format CSV
     * @param int $userPayId L'utilisateur facturé
     * @param int $startTypeId Le moyen de mise en l'air retenu
     * @return \App\Models\transaction
     */
    public static function towingOnlyTransaction($csv, $userPayId, $startTypeId)
    {
        $startTypeElem = sailplaneStartPrice::find($startTypeId);
        $nbTakeOff     = max(1, intval($csv[13]));
        $value         = $startTypeElem->basePrice * $nbTakeOff;
        $minutes       = self::towingMinutes($csv);

        if ($startTypeElem->byMinutes == 1) {
            $value = $value * $minutes;
        }

        $observation = 'Aéronef inconnu (' . trim($csv[1]) . ') - '
            . $nbTakeOff . ' X ' . $startTypeElem->name
            . ($minutes > 0 ? ' - Remorqueur : ' . $minutes . ' minutes' : '');

        $transaction              = new transaction();
        $transaction->idUser      = $userPayId;
        $transaction->name        = 'Remorquage seul';
        $transaction->value       = 0 - intval($value);
        $transaction->quantity    = 1;
        $transaction->valid       = 1;
        $transaction->solde       = 0.0;
        $transaction->time        = strtotime(str_replace('/', '-', self::csvToTakeOff($csv)));
        $transaction->year        = date('Y', $transaction->time);
        $transaction->observation = $observation;

        return $transaction;
    }

    /**
     * Crée un nouvel utilisateur à partir des données CSV
     *
     * @param string $nameCsv Le nom et la licence au format "Nom (Licence)"
     * @return \App\Models\User L'utilisateur créé
     */
    public static function createUser($nameCsv)
    {
        $name                = trim(explode('(', $nameCsv)[0]);
        $licence             = substr(explode('(', $nameCsv)[1], 0, (strlen(explode('(', $nameCsv)[1]) - 1));
        $user                = new User();
        $user->name          = $name;
        $user->email         = str_replace(' ', '-', $name) . '@cvvt-temp.fr';
        $user->password      = ' ';
        $user->sexe          = 0;
        $user->licenceNumber = $licence;
        $user->isSupervisor  = 0;
        $user->FFVP          = 1;
        $user->FFPLUM        = 0;
        $user->state         = 1;
        $user->save();
        return $user;
    }

    /**
     * Indique si la ligne est un vol d'instruction : la colonne « École » est
     * cochée et un second pilote est présent à bord.
     *
     * @param array $csv Les données du vol au format CSV
     * @return bool
     */
    public static function isSchoolFlight($csv)
    {
        return ($csv[7] == "1" && trim($csv[5]) !== "");
    }

    /**
     * Utilisateur correspondant à une cellule « Nom (Licence) », créé s'il
     * n'existe pas encore. Retourne null si la cellule ne porte pas de licence.
     *
     * @param string $cell
     * @return \App\Models\User|null
     */
    private static function userFromCell($cell)
    {
        if (strpos($cell, '(') === false) {
            return null;
        }

        $part    = explode('(', $cell)[1];
        $licence = substr($part, 0, (strlen($part) - 1));
        $user    = User::where('licenceNumber', $licence)->first();

        return is_null($user) ? self::createUser($cell) : $user;
    }

    /**
     * Récupère l'ID de l'utilisateur porté par le vol (celui qui est facturé
     * par défaut). En instruction, c'est l'élève : celui des deux pilotes qui
     * n'est pas instructeur, quel que soit son rang dans le fichier.
     *
     * @param array $csv Les données du vol au format CSV
     * @return int L'ID de l'utilisateur ou -1 si non trouvé
     */
    public static function csvToUser($csv)
    {
        $pilot1 = self::userFromCell($csv[3]);
        if (is_null($pilot1)) {
            return -1;
        }

        if (! self::isSchoolFlight($csv)) {
            return $pilot1->id;
        }

        $pilot2 = self::userFromCell($csv[5]);
        if (is_null($pilot2)) {
            return -1;
        }

        return ($pilot1->isSupervisor == 1) ? $pilot2->id : $pilot1->id;
    }

    /**
     * Récupère l'ID de l'instructeur à partir des données CSV.
     *
     * L'instructeur peut être saisi indifféremment en pilote 1 ou en pilote 2
     * dans le fichier : les deux places sont examinées.
     *
     * @param array $csv Les données du vol au format CSV
     * @return int|null L'ID de l'instructeur ou null si pas d'instructeur
     */
    public static function csvToInstructorId($csv)
    {
        $id = null;
        if (self::isSchoolFlight($csv)) {
            foreach ([$csv[3], $csv[5]] as $cell) {
                $user = self::userFromCell($cell);
                if (! is_null($user) && $user->isSupervisor == 1) {
                    $id = $user->id;
                    break;
                }
            }
        }

        return $id;
    }

    /**
     * Calcule la durée totale du vol en minutes
     *
     * @param array $csv Les données du vol au format CSV
     * @return int La durée totale en minutes
     */
    public static function csvToTotalTime($csv)
    {
        return intval((strtotime($csv[0] . ' ' . $csv[9]) - strtotime($csv[0] . ' ' . $csv[8])) / 60);
    }

    /**
     * Récupère l'heure de décollage au format "dd/mm/yyyy HH:ii"
     *
     * @param array $csv Les données du vol au format CSV
     * @return string L'heure de décollage formatée
     */
    public static function csvToTakeOff($csv)
    {
        return date('d/m/Y H:i', strtotime($csv[0] . ' ' . $csv[8]));
    }

    /**
     * Récupère l'heure d'atterrissage au format "dd/mm/yyyy HH:ii"
     *
     * @param array $csv Les données du vol au format CSV
     * @return string L'heure d'atterrissage formatée
     */
    public static function csvToLanding($csv)
    {
        return date('d/m/Y H:i', strtotime($csv[0] . ' ' . $csv[9]));
    }

    /**
     * Récupère l'ID de l'aéronef à partir des données CSV
     *
     * @param array $csv Les données du vol au format CSV
     * @return int L'ID de l'aéronef
     */
    public static function csvToAircraft($csv)
    {
        $reg   = [];
        $reg[] = explode(' ', $csv[1])[0];

        if (strpos($reg[0], 'F-') === false) {
            $reg[] = 'F-' . substr($reg[0], 1);
        }
        $aircraft = aircraft::whereIn('register', $reg)->first();
        return (! is_null($aircraft)) ? $aircraft->id : -1;
    }

    /**
     * Détermine le type de départ en fonction de l'aéronef
     *
     * @param array $csv Les données du vol au format CSV
     * @param int|null $startType Le type de départ par défaut (optionnel)
     * @return int Le type de départ (0 pour planeur, 1 pour autre)
     */
    public static function csvToAircraftStart($csv, $startType = null)
    {
        $reg      = explode(' ', $csv[1]);
        $aircraft = aircraft::where('register', $reg)->first();
        if(is_null($aircraft)){
            $aircraft = aircraft::where('register', str_replace('F', 'F-', $reg[0]))->first();
        }
        return ($aircraft->type == 1) ? 0 : ((is_null($startType)) ? 1 : $startType);
    }

}
