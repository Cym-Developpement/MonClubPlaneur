<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\transaction;
use App\Models\flight;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Mail;
use App\Mail\sendAccount;
use App\Models\usersAttributes;

/**
 * Modèle représentant un utilisateur dans le système
 * 
 * @property int $id Identifiant unique de l'utilisateur
 * @property string $name Nom complet de l'utilisateur
 * @property string $email Adresse email de l'utilisateur
 * @property string $password Mot de passe hashé
 * @property int $sexe Genre de l'utilisateur (0: Non spécifié, 1: Homme, 2: Femme)
 * @property string $licenceNumber Numéro de licence
 * @property bool $isSupervisor Indique si l'utilisateur est un instructeur
 * @property bool $FFVP Indique si l'utilisateur est membre FFVP
 * @property bool $FFPLUM Indique si l'utilisateur est membre FFPLUM
 * @property int $state État du compte (0: Inactif, 1: Actif)
 * @property \DateTime $email_verified_at Date de vérification de l'email
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public $yearState = null;

    /**
     * Convertit un nombre de minutes en format heures et minutes
     *
     * @param int $minutes Nombre de minutes à convertir
     * @return string Temps formaté (ex: "2 Heures 30 Minutes")
     */
    private function convertMinToHM($minutes)
    {
        if ($minutes < 60) {
            return $minutes." Minutes";
        } else {
            $hourR = intval($minutes/60);
            $minutesR = $minutes-($hourR*60);
            if ($minutesR == 0) {
                return $hourR . " Heures";
            } else {
                return $hourR . " Heures " . $minutesR ." Minutes";
            }
        }
    }

    public function getAuditNameAttribute(): string
    {
        return 'utilisateur';
    }

    public function getAuditLineAttribute(): string
    {
        return $this->name;
    }

    public function getNomAttribute()
    {
        return explode(' ', $this->name)[0];
    }

    public function getPrenomAttribute()
    {
        return explode(' ', $this->name)[1];
    }

    /**
     * Récupère la liste des utilisateurs ayant un attribut spécifique
     *
     * @param string $attr Nom de l'attribut recherché
     * @return User[] Liste des utilisateurs ayant l'attribut
     */
    public static function getUserListByAttr($attr)
    {
        $all = User::all();
        $list = [];
        foreach ($all as $user) {
            if ($user->isAttr($attr)) {
                $list[] = $user;
            }
        }
        return $list;
    }

    /**
     * Récupère le genre de l'utilisateur, initialise à 0 si non défini
     *
     * @return int Genre de l'utilisateur (0: Non spécifié, 1: Homme, 2: Femme)
     */
    public function getSexeAttribute()
    {
        if (is_null($this->attributes['sexe'])) {
            $this->attributes['sexe'] = 0;
            $this->save();
        }
        return $this->attributes['sexe'];
    }

    /**
     * Met à jour le solde de l'utilisateur en recalculant toutes les transactions
     *
     * @return float Solde final après mise à jour
     */
    public function updateSolde()
    {
        $transactions = transaction::where('idUser', $this->id)->orderBy('time', 'asc')->orderBy('id', 'ASC')->get();
        $solde = 0;
        foreach ($transactions as $key => $value) {
            $value->solde = floatval(intval($solde+$value->value));
            $solde = $value->solde;
            $value->save();
        }

        return $solde;
    }

    /**
     * Récupère la liste des instructeurs
     *
     * @return \Illuminate\Database\Eloquent\Collection|User[] Liste des instructeurs
     */
    public static function supervisor()
    {
        //dd(User::where('isSupervisor', 1)->get());
        return User::where('isSupervisor', 1)->get();
    }

    public function getRealAmountAccountAttribute()
    {
        $this->updateSolde();
        $lastTr = transaction::where('idUser', $this->id)->orderBy('time', 'desc')->orderBy('id', 'desc')->first();
        $amount = (!is_null($lastTr)) ? $lastTr->solde : 0 ;
        return intval($amount);
    }

    public function getDayFlight($year, $paid = 0)
    {
        $start = strtotime("$year-01-01 00:00:00");
        $end = strtotime("$year-12-31 23:59:59");
        if ($paid == 0) {
            $flights = flight::where('idUser', $this->id)->where('flightTimestamp', '>=', $start)->where('flightTimestamp', '<=', $end)->get();
        } else {
            $flights = flight::where('userPayId', $this->id)->where('flightTimestamp', '>=', $start)->where('flightTimestamp', '<=', $end)->get();
        }
        
        $days = 0;
        if (count($flights) > 0) {
            $dayFlight = [];
            foreach ($flights as $flight) {
                $dayFlight[explode(' ', $flight->landingTime)[0]] = 1;
            }
            $days = count($dayFlight);
        }
        return $days;
    }

    public function getHourFlight($year, $paid = 0)
    {
        $start = strtotime("$year-01-01 00:00:00");
        $end = strtotime("$year-12-31 23:59:59");
        if ($paid == 0) {
            $flights = flight::where('idUser', $this->id)->where('flightTimestamp', '>=', $start)->where('flightTimestamp', '<=', $end)->get();
        } else {
            $flights = flight::where('userPayId', $this->id)->where('flightTimestamp', '>=', $start)->where('flightTimestamp', '<=', $end)->get();
        }
        
        $minutes = 0;
        foreach ($flights as $flight) {
            $minutes += $flight->totalTime;
        }
        return $this->convertMinToHM($minutes);
    }

    public function getYearStateValueAttribute()
    {
        return (is_null($this->yearState)) ? date('Y')  : $this->yearState ;
    }

    public function getCurrentDayFlightAttribute()
    {
        return $this->getDayFlight($this->year_state_value);
    }

    public function getCurrentDayFlightPaidAttribute()
    {
        return $this->getDayFlight($this->year_state_value, 1);
    }

    public function getCurrentHourFlightAttribute()
    {
        return $this->getHourFlight($this->year_state_value);
    }

    public function getCurrentHourFlightPaidAttribute()
    {
        return $this->getHourFlight($this->year_state_value, 1);
    }

    public function getCurrentDayFlightInvoiceAttribute()
    {
        return ($this->getDayFlight($this->year_state_value, 1) < 10) ? $this->getDayFlight($this->year_state_value, 1) : 10 ;
    }

    public function getCurrentDayFlightStateAttribute()
    {
        $trs = transaction::where('idUser', $this->id)->where('name', 'Frais de fonctionement '.$this->year_state_value)->get();
        $total = 0;
        foreach ($trs as $tr) {
            $total += (0-$tr->value);
        }
        return (intval($total/1500));
    }

    public function getCotisationStateAttribute()
    {
        $tr = transaction::where('idUser', $this->id)->where('name', 'Cotisation '.$this->year_state_value)->first();
        return (!is_null($tr));
    }

    public function getCotisationForcedAttribute()
    {
        return (!$this->cotisation_state && $this->current_day_flight > 0);
    }

    public static function sendAccountAlertNotification()
    {
        $users = User::all();
        $notifUsers = [];
        foreach ($users as $user) {
            if ($user->real_amount_account < 0 && $user->state == 1 && !$user->isAttr('user:technique')) {
                $notifUsers[] = $user;
            }
        }

        $file = new Filesystem;
        //$file->cleanDirectory();
        $file->cleanDirectory('userAcountState');
        
        foreach ($notifUsers as $user) {
            $transactions = [];
            $twelveMonthsAgo = strtotime('-12 months');

            $reportAnterieur = transaction::where('idUser', $user->id)
                                            ->where('time', '<', $twelveMonthsAgo)
                                            ->orderBy('time', 'desc')
                                            ->orderBy('id', 'desc')
                                            ->first();

            if ($reportAnterieur) {
                $transactions[] = [
                    'time'        => date('d/m/Y', $twelveMonthsAgo),
                    'value'       => '',
                    'solde'       => number_format($reportAnterieur->solde / 100, 2),
                    'name'        => 'Report Antérieur',
                    'id'          => null,
                    'observation' => '',
                ];
            }

            $transactionsUser = transaction::where('idUser', $user->id)
                                            ->where('time', '>=', $twelveMonthsAgo)
                                            ->orderBy('time', 'asc')
                                            ->orderBy('id', 'ASC')
                                            ->get();
            foreach ($transactionsUser as $value) {
                $transactions[] = ['time'=> date('d/m/Y H:i', $value->time), 'value' => number_format(($value->value/100), 2), 'solde' => number_format(($value->solde/100), 2), 'name' => $value->name, 'id' => $value->id, 'observation' => $value->observation];
            }

            $transactionType = array();

            $transactionTypeData = transactionType::all();
            foreach ($transactionTypeData as $key => $value) {
                $value->name = $value->name.' '.date('Y');
                $transactionType[] = $value;
            }

            $aircraft = aircraft::all();
            $sailplaneStartPrice = sailplaneStartPrice::all();
            $filename = 'CVVT-'.str_replace(' ', '_', strtoupper($user->name)).'_'.date('d-m-Y_H-i').'.pdf';
            $pdf = Pdf::loadView('exportPdfAccount', ['transactions' => $transactions, 'selectedUser' => $user, 'transactionType' => $transactionType, 'aircrafts' => $aircraft, 'sailplaneStartPrices' => $sailplaneStartPrice]);
            $pdf->save('../storage/app/userAcountState/'.$filename);

            //Mail::to($user->email)->send(new sendAccount($user->name, 'userAcountState/'.$filename));
            //Mail::to('yann@cymdev.com')->send(new sendAccount($user->name, 'userAcountState/'.$filename));
        }



        //dd($notifUsers);
    }

    public function getAllAttrAttribute()
    {
        $all = usersAttributes::where('userId', $this->id)->get();
        $array = [];
        foreach ($all as $value) {
            $array[] = $value->attributeName;
        }
        return $array;
    }

    public function isAttr($name)
    {
        return in_array($name, $this->all_attr);
    }

    // Correspondance rôle → préfixe de sous-permissions
    public const ROLE_PREFIX_MAP = [
        'Pilote'               => 'pilote',
        'Elève'                => 'eleve',
        'Instructeur Planeur'  => 'instructeur_planeur',
        'Instructeur ULM'      => 'instructeur_ulm',
        'Remorqueur'           => 'remorqueur',
        'Licence associative'  => 'licence',
    ];

    public function saveAttr($arr)
    {
        // Rôles actuels sans préfixe
        $current = usersAttributes::where('userId', $this->id)
            ->where('attributeName', 'not like', '%:%')
            ->pluck('attributeName')
            ->toArray();

        // Pour chaque rôle supprimé, cascade sur ses sous-permissions
        foreach (array_diff($current, $arr) as $removed) {
            if (isset(self::ROLE_PREFIX_MAP[$removed])) {
                usersAttributes::where('userId', $this->id)
                    ->where('attributeName', 'like', self::ROLE_PREFIX_MAP[$removed] . ':%')
                    ->delete();
            }
        }

        // Remplace les attributs sans préfixe
        usersAttributes::where('userId', $this->id)
            ->where('attributeName', 'not like', '%:%')
            ->delete();

        foreach ($arr as $attr) {
            $attribute = new usersAttributes();
            $attribute->userId = $this->id;
            $attribute->attributeName = $attr;
            $attribute->save();
        }
    }

    /**
     * Sauvegarde les permissions d'un préfixe donné.
     * Ex: savePermissions('admin', ['admin:backups', 'admin:users'])
     */
    public function savePermissions(string $prefix, array $permissions): void
    {
        usersAttributes::where('userId', $this->id)
            ->where('attributeName', 'like', $prefix . ':%')
            ->delete();
        foreach ($permissions as $perm) {
            $attribute = new usersAttributes();
            $attribute->userId = $this->id;
            $attribute->attributeName = $perm;
            $attribute->save();
        }
    }

    public function resetAdminAccess()
    {
        if (!is_null($this->recordPasswordAdminAccess)) {
            $this->password = $this->recordPasswordAdminAccess;
            $this->recordPasswordAdminAccess = null;
            $this->save();
        }
    }

    public function getAdminAccessAttribute()
    {
        $adminpass = rand(111111, 999999);
        if (is_null($this->recordPasswordAdminAccess)) {
            $this->recordPasswordAdminAccess = $this->password;
            $this->password = Hash::make($adminpass);
        } else {
            $this->password = Hash::make($adminpass);
        }
        $this->save();
        return $adminpass;
    }

}
