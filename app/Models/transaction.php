<?php

namespace App\Models;

use App\Models\refund;
use App\Models\sailplaneStartPrice;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Modèle représentant une transaction financière dans le système
 * 
 * @property int $id Identifiant unique de la transaction
 * @property int $idUser ID de l'utilisateur concerné
 * @property string $name Libellé de la transaction
 * @property int $value Montant en centimes (négatif pour un débit)
 * @property int $quantity Quantité associée à la transaction
 * @property bool $valid État de validation de la transaction
 * @property int $time Timestamp de la transaction
 * @property int $year Année de la transaction
 * @property float $solde Solde après la transaction
 * @property int|null $refundId ID du remboursement associé
 */
class transaction extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transaction';
    /**
     * @var string
     */
    private static $stripeLink = 'https://buy.stripe.com/6oE5lbdfd39wfvibII';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'time', 'year',
    ];

    /**
     * Convertit un nombre de minutes en format heures et minutes
     *
     * @param int $minutes Nombre de minutes à convertir
     * @return string Temps formaté (ex: "2 Heures 30 Minutes")
     */
    public static function convertMinToHM($minutes)
    {
        if ($minutes < 60) {
            return $minutes . " Minutes";
        } else {
            $hourR = intval($minutes / 60);
            $minutesR = $minutes - ($hourR * 60);
            if ($minutesR == 0) {
                return $hourR . " Heures";
            } else {
                return $hourR . " Heures " . $minutesR . " Minutes";
            }
        }
    }

    /**
     * Convertit une date du format français vers le format ISO
     *
     * @param string $date Date au format "dd/mm/yyyy HH:ii"
     * @return string Date au format "yyyy-mm-dd HH:ii"
     */
    public static function frToIso($date)
    {
        $time = explode(' ', $date)[1];
        $date = explode(' ', $date)[0];

        $raw = explode('/', $date);
        return $raw[2] . '-' . $raw[1] . '-' . $raw[0] . ' ' . $time;
    }

    /**
     * Récupère l'objet remboursement associé à la transaction
     *
     * @return refund|null L'objet remboursement ou null si pas de remboursement
     */
    public function getRefundAttribute()
    {
        return refund::find($this->refundId);
    }

    /**
     * Compte le nombre de transactions non validées
     *
     * @return int Nombre de transactions non validées
     */
    public static function getNotValidNumber()
    {
        return transaction::where('valid', 0)->count();
    }

    /**
     * Génère le lien Stripe pour l'utilisateur connecté
     *
     * @return string URL Stripe avec les paramètres de l'utilisateur
     */
    public static function getStripeLink()
    {
        $user = Auth::user();
        return self::$stripeLink . '?prefilled_email=' . urlencode($user->email) . '&client_reference_id=' . $user->id;
    }

    /**
     * Retourne le montant de la transaction en euros formaté
     *
     * @return string Montant en euros avec 2 décimales
     */
    public function getValueEurAttribute()
    {
        return number_format((0 - ($this->value / 100)), 2);
    }

    /**
     * Ajoute une nouvelle transaction
     *
     * @param int $userId ID de l'utilisateur
     * @param int $value Montant en centimes
     * @param string $name Libellé de la transaction
     * @param string|null $observation Observation optionnelle
     * @param string|null $date Date de la transaction (format Y-m-d)
     * @return void
     */
    public static function add($userId, $value, $name, $observation = null, $date = null)
    {
        $user = User::find($userId);
        $tr = new transaction();
        $tr->idUser = $user->id;
        $tr->name = $name;
        $tr->observation = $observation;
        $tr->value = $value;
        $tr->quantity = 1;
        $tr->valid = 1;
        $tr->time = (is_null($date)) ? time() : (strtotime($date));
        $tr->year = (is_null($date)) ? date('Y') : date('Y', strtotime($date));
        $tr->solde = 0;
        $tr->save();
        $user->updateSolde();
    }

    /**
     * @param $flight
     * @return mixed
     */
    public static function getFlightTransaction($flight)
    {
        $startType = sailplaneStartPrice::find($flight->startType);

        $price = $flight->aircraft->price(
            strtotime(self::frToIso($flight->takeOffTime)),
            strtotime(self::frToIso($flight->landingTime)),
            0,
            $flight->startType,
            $flight->motorStartTime,
            $flight->motorEndTime,
            $flight->landing,
            0
        );

        //dd([self::frToIso($flight->takeOffTime), $flight->landingTime, $price]);

        switch ($flight->aircraft->type) {
            case 1:
                $transacObservation = $flight->aircraft->name . " (" . $flight->aircraft->register . ") - "
                . self::convertMinToHM($flight->totalTime) . " - Moteur : " . intval($price[4][0]) . " centièmes";
                break;
            case 2:
                $transacObservation = $flight->aircraft->name . " (" . $flight->aircraft->register . ") - "
                . self::convertMinToHM($flight->totalTime) . " - Lancement : " . $flight->landing . ' X ' . $startType->name;
                break;
            default:
                return;
                break;
        }

        $flight->value = $price[3];
        $transacTitle = "HDV : " . $flight->aircraft->name;

        $transaction = new transaction();
        $transaction->idUser = $flight->userPayId;
        $transaction->name = $transacTitle;
        $transaction->value = 0 - ($flight->value);
        $transaction->quantity = 1;
        $transaction->valid = 1;
        $transaction->solde = 0.0;
        $transaction->time = $flight->flightTimestamp;
        $transaction->year = date('Y', $transaction->time);
        $transaction->observation = $transacObservation;

        return $transaction;
    }
}
