<?php
namespace App;

use App\Models\aircraft;
use App\Models\flight;
use App\Models\User;

/**
 *
 */
class Gesasso
{

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
        $flight->motorEndTime     = intval($csv[15]) <= 15 ? 0 : (intval($csv[15])/100);
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
     * Récupère l'ID de l'utilisateur à partir des données CSV
     *
     * @param array $csv Les données du vol au format CSV
     * @return int L'ID de l'utilisateur ou -1 si non trouvé
     */
    public static function csvToUser($csv)
    {
        //dd(strlen(explode('(', $csv[3])[1]) - 1);
        if (strpos($csv[3], '(') === false) {
            return -1;
        }

        $first = substr(explode('(', $csv[3])[1], 0, (strlen(explode('(', $csv[3])[1]) - 1));
        $user  = User::where('licenceNumber', $first)->first();
        if (is_null($user)) {
            $user = self::createUser($csv[3]);
        }
        if ($csv[5] !== "" && $csv[7] == "1") {
            if (strpos($csv[5], '(') === false) {
                return -1;
            }
            $second = substr(explode('(', $csv[5])[1], 0, (strlen(explode('(', $csv[5])[1]) - 1));
            $user2  = User::where('licenceNumber', $second)->first();
            if (is_null($user2)) {
                $user2 = self::createUser($csv[5]);
            }
            if ($user->isSupervisor == 1) {
                return $user2->id;
            }
        }

        return (! is_null($user)) ? $user->id : -1;
    }

    /**
     * Récupère l'ID de l'instructeur à partir des données CSV
     *
     * @param array $csv Les données du vol au format CSV
     * @return int|null L'ID de l'instructeur ou null si pas d'instructeur
     */
    public static function csvToInstructorId($csv)
    {
        $id = null;
        if ($csv[5] !== "" && $csv[7] == "1") {
            $first = substr(explode('(', $csv[3])[1], 0, (strlen(explode('(', $csv[3])[1]) - 1));
            $user  = User::where('licenceNumber', $first)->first();
            if ($user->isSupervisor == 1) {
                $id = $user->id;
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
