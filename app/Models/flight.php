<?php
namespace App\Models;

use App\Models\aircraft;
use App\Models\transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant un vol dans le système
 *
 * @property int $id Identifiant unique du vol
 * @property int $idUser ID de l'utilisateur ayant effectué le vol
 * @property int $userPayId ID de l'utilisateur qui paie le vol
 * @property int $aircraftId ID de l'aéronef utilisé
 * @property int $idInstructor ID de l'instructeur (si applicable)
 * @property string $takeOffTime Heure de décollage
 * @property string $landingTime Heure d'atterrissage
 * @property int $totalTime Durée totale du vol en minutes
 * @property float $motorStartTime Index moteur au départ
 * @property float $motorEndTime Index moteur à l'arrivée
 * @property int $startType Type de décollage
 * @property int $flightTimestamp Timestamp du vol
 * @property string $airPortStartCode Code de l'aéroport de départ
 * @property string $airPortEndCode Code de l'aéroport d'arrivée
 */
class flight extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'flights';

    /**
     * Convertit un nombre de minutes en format heures et minutes
     *
     * @param int $minutes Nombre de minutes à convertir
     * @return string Temps formaté (ex: "2 Heures 30 Minutes")
     */
    private function convertMinToHM($minutes)
    {
        if ($minutes < 60) {
            return $minutes . " Minutes";
        } else {
            $hourR    = intval($minutes / 60);
            $minutesR = $minutes - ($hourR * 60);
            if ($minutesR == 0) {
                return $hourR . " Heures";
            } else {
                return $hourR . " Heures " . $minutesR . " Minutes";
            }
        }
    }

    /**
     * Retourne l'objet aircraft associé au vol
     *
     * @return aircraft L'aéronef utilisé pour le vol
     */
    public function getAircraftAttribute()
    {
        return aircraft::find($this->aircraftId);
    }

    /**
     * Retourne l'utilisateur qui a effectué le vol
     *
     * @return User L'utilisateur ayant effectué le vol
     */
    public function getUserAttribute()
    {
        return User::find($this->idUser);
    }

    /**
     * Retourne l'utilisateur qui paie le vol
     *
     * @return User L'utilisateur qui paie le vol
     */
    public function getUserPaidAttribute()
    {
        return User::find($this->userPayId);
    }

    /**
     * Retourne l'instructeur du vol
     *
     * @return User|null L'instructeur du vol ou null si pas d'instructeur
     */
    public function getInstructorAttribute()
    {
        return User::find($this->idInstructor);
    }

    /**
     * Retourne la durée du vol au format heures et minutes
     *
     * @return string Durée du vol formatée
     */
    public function getTimeHMAttribute()
    {
        return $this->convertMinToHM($this->totalTime);
    }

    /**
     * Convertit l'index de départ moteur en format décimal
     *
     * @return string Index moteur de départ converti
     */
    public function getRealMotorStartTimeAttribute()
    {
        if ($this->motorPriceType == 1) {
            return $this->motorStartTime;
        } else {
            $cents   = ($this->motorStartTime - intval($this->motorStartTime));
            $minutes = (round($cents * 100) * 0.006);
            return number_format((intval($this->motorStartTime) + $minutes), 2, '.', '');
        }
    }

    /**
     * Convertit l'index d'arrivée moteur en format décimal
     *
     * @return string Index moteur d'arrivée converti
     */
    public function getRealMotorEndTimeAttribute()
    {
        if ($this->motorPriceType == 1) {
            return $this->motorEndTime;
        } else {
            $cents = ($this->motorEndTime - intval($this->motorEndTime));

            $minutes = (round($cents * 100) * 0.006);
            //return $minutes;
            return number_format((intval($this->motorEndTime) + $minutes), 2, '.', '');
        }
    }

    /**
     * Supprime un vol et ses transactions associées
     *
     * @param int $id Identifiant du vol à supprimer
     * @return void
     */
    public static function deleteFlight($id)
    {
        $flight = flight::find($id);
        $tr     = transaction::find($flight->transactionID);

        if (! is_null($tr)) {
            $user = User::find($tr->idUser);
            $tr->delete();
            if (! is_null($user)) {
                $user->updateSolde();
            }
        }

        $flight->delete();
    }

    /**
     * Ajoute un nouveau vol
     *
     * @param int $start Timestamp de début du vol
     * @param int $end Timestamp de fin du vol
     * @param int $aircraft ID de l'aéronef utilisé
     * @param int $pilot ID de l'utilisateur ayant effectué le vol
     * @param int $nbTakeOff Nombre de décollages
     * @param int|null $instructor ID de l'instructeur (si applicable)
     * @param int|null $userPaid ID de l'utilisateur qui paie le vol (si différent de l'utilisateur ayant effectué le vol)
     * @return void
     */
    public static function add($start, $end, $aircraft, $pilot, $nbTakeOff = 1, $instructor = null, $userPaid = null)
    {
        $userPaid                 = (is_null($userPaid)) ? $pilot : $userPaid;
        $flightTime               = intval(($end - $start) / 60);
        $flight                   = new flight();
        $flight->idUser           = $pilot;
        $flight->totalTime        = $flightTime;
        $flight->takeOffTime      = date('d/m/Y H:i', $start);
        $flight->landingTime      = date('d/m/Y H:i', $end);
        $flight->landing          = $nbTakeOff;
        $flight->aircraftId       = $aircraft;
        $flight->motorStartTime   = 0;
        $flight->motorEndTime     = 0;
        $flight->airPortStartCode = 'LFCT';
        $flight->airPortEndCode   = 'LFCT';
        $flight->startType        = 1;
        $flight->flightTimestamp  = $start;
        $flight->userPayId        = $userPaid;
        if (! is_null($instructor)) {
            $flight->idInstructor = $instructor;
        }

        $aircraft  = aircraft::find($aircraft);
        $startType = sailplaneStartPrice::find($request->startType);
        $price     = $aircraft->price(
            $start,
            $end,
            $flightTime,
            $request->startType,
            $request->startMotor,
            $request->endMotor,
            $nbTakeOff,
            0
        );

        switch ($aircraft->type) {
            case 1:
                $transacObservation = $aircraft->name . " (" . $aircraft->register . ") - "
                . $this->convertMinToHM($request->flightTime) . " - Moteur : " . $price[4][0] . " centièmes";
                break;
            case 2:
                $transacObservation = $aircraft->name . " (" . $aircraft->register . ") - "
                . $this->convertMinToHM($request->flightTime) . " - Lancement : " . $request->nbTakeOff . ' X ' . $startType->name;
                break;
            default:
                return;
                break;
        }

        $flight->value = $price[3];
        $transacTitle  = "HDV : " . $aircraft->name;

        $transaction              = new transaction();
        $transaction->idUser      = $userPaid;
        $transaction->name        = $transacTitle;
        $transaction->value       = 0 - ($flight->value);
        $transaction->quantity    = 1;
        $transaction->valid       = 1;
        $transaction->solde       = 0.0;
        $transaction->time        = $flight->flightTimestamp;
        $transaction->year        = date('Y', $transaction->time);
        $transaction->observation = $transacObservation;

        $transaction->save();
        $flight->transactionID = $transaction->id;
        $flight->save();

        $transactions = transaction::where('idUser', $flight->userPayId)->where('year', '>=', $transaction->year)->orderBy('time', 'asc')->orderBy('id', 'ASC')->get();
        $solde        = 0;
        $first        = 1;
        foreach ($transactions as $key => $value) {
            if ($first == 1) {
                $solde = $value->solde;
                $first = 0;
            } else {
                $transactions[$key]->solde = $solde + $value->value;
                $solde                     = $transactions[$key]->solde;
                $transactions[$key]->save();
            }
        }
    }
}
