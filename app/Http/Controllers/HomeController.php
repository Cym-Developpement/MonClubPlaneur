<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\library\alerts;
use App\Models\transaction;
use App\Models\transactionType;
use App\Models\User;
use App\Models\usersData;
use App\Models\aircraft;
use App\Models\sailplaneStartPrice;
use App\Models\flight;
use App\Models\flightDay;
use App\Models\usersAttributes;
use App\Models\refundCategory;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function wiki(Request $request)
    {
        return view('wiki');
    }


    private function getUserData()
    {
        $userDataSql = usersData::where('userID', Auth::user()->id)->get();
        $userData = array();
        foreach ($userDataSql as $key => $value) {
            $userData[$value->dataName] = $value->dataValue;
        }

        return $userData;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        Auth::user()->resetAdminAccess();
        $userData = $this->getUserData();
        $alerts = alerts::getAlertsList(Auth::user()->id);
        $transactions = array();
        $transactionsData = transaction::where('idUser',  Auth::user()->id)
                                        ->orderBy('time', 'asc')
                                        ->orderBy('id', 'ASC')
                                        ->get();
        foreach ($transactionsData as $key => $value) {
            $transactions[] = ['time'=> date('d/m/Y H:i', $value->time), 'value' => number_format(($value->value/100), 2), 'solde' => number_format(($value->solde/100), 2), 'name' => $value->name, 'quantity' => $value->quantity, 'valid' => $value->valid, 'observation' => $value->observation, 'year' => date('Y', $value->time)];
        }

        if (Auth::user()->can('admin')) {
            $allUsers = User::where('id', '>', 0)->orderBy('name', 'asc')->get();
        } else {
            $allUsers = [];
        }

        $attributes = usersAttributes::where('userId', Auth::user()->id)->get();
        
        return view('home', ['userAttributes' => $attributes, 'transactions' => $transactions, 'solde' => number_format(($this->getSolde(Auth::user()->id)/100), 2), 'userData' => $userData, 'alertsList' => $alerts, 'allUsers' => $allUsers]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function saisie(Request $request)
    {

        $selectedUser = 0;
        $users = User::where('name', '<>', '')->where('state', 1)->orderBy('name', 'asc')->get();
        $transactions = array();
        if (isset($request->selectUserInTransaction)) {

            if (isset($request->selectTransactionType)) {
                if (!isset($request->valueTransaction)) {
                    $request->valueTransaction = 0;
                }

                if ($request->selectTransactionTypeEnc == 1) {
                    $request->valueTransaction = (0-$request->valueTransaction);
                }
                $this->saveTransaction($request->selectUserInTransaction, $request->selectTransactionType, $request->valueTransaction);
            }

            if (isset($request->nameFreeTransaction))
            {
                $this->saveFreeTransaction($request->selectUserInTransaction, $request->nameFreeTransaction, $request->valueFreeTransaction);
            }

            $selectedUser = $request->selectUserInTransaction;
            $transactionsUser = transaction::where('idUser',  $request->selectUserInTransaction)
                                            ->orderBy('time', 'asc')
                                            ->orderBy('id', 'ASC')
                                            ->get();
            foreach ($transactionsUser as $key => $value) {
                $transactions[] = ['time'=> date('d/m/Y H:i', $value->time), 'value' => number_format(($value->value/100), 2), 'solde' => number_format(($value->solde/100), 2), 'name' => $value->name, 'id' => $value->id, 'observation' => $value->observation, 'valid' => $value->valid, 'year' => date('Y', $value->time)];
            }
        }


        $transactionType = transactionType::all();
        

        $aircraft = aircraft::all();
        $sailplaneStartPrice = sailplaneStartPrice::all();

        return view('transaction', ['users' => $users, 'transactions' => $transactions, 'selectedUser' => $selectedUser, 'transactionType' => $transactionType, 'aircrafts' => $aircraft, 'sailplaneStartPrices' => $sailplaneStartPrice]);
    }

    private function getSolde($user)
    {
        $solde = 0;
        $transaction = transaction::where('idUser', $user)->orderBy('time', 'desc')->first();
        if (isset($transaction->solde)) {
            $solde = $transaction->solde;
        }
        return $solde;
    }

    private function saveTransaction($user, $type, $amount)
    {
        $transaction = new transaction();
        $transaction->idUser = $user;
        $transaction->name = $type;
        $transaction->value = intval($amount*100);
        $transaction->solde = ($this->getSolde($user)+$transaction->value);
        $transaction->year = date('Y');
        $transaction->time = time();
        $transaction->save();
    }

    private function saveFreeTransaction($user, $name, $amount)
    {
        $transaction = new transaction();
        $transaction->idUser = $user;
        $transaction->name = $name;
        $transaction->value = intval($amount*100);
        $transaction->solde = ($this->getSolde($user)+$transaction->value);
        $transaction->year = date('Y');
        $transaction->time = time();
        $transaction->save();
    }

    public function deleteLastTransaction(Request $request)
    {
        if (isset($request->deleteLastUserTransaction)) {
            $lastTransaction = transaction::where('idUser', $request->deleteLastUserTransaction)
               ->orderBy('time', 'desc')
               ->limit(1)
               ->delete();
        }
        
        return redirect('saisie?selectUserInTransaction='.$request->deleteLastUserTransaction);
    }

    public function addFlightDay(Request $request)
    {
        $alreadyRegister = flightDay::where('userId', Auth::user()->id)->where('date', $request->date)->get();
        if (count($alreadyRegister) == 0) {
            $flightDay = new flightDay();
            $dateExploded = explode('/', $request->date);
            $flightDay->date = $dateExploded[2].'-'.$dateExploded[1].'-'.$dateExploded[0];
            $flightDay->userId = Auth::user()->id;
            $flightDay->state = $request->attribute;
            $flightDay->observation = $request->observation;
            $flightDay->save();
            return 'OK|Votre inscription le '.$request->date.' en tant que '.$request->attribute.' a été pris en compte.';
        } else {
            return 'ERROR|Vous êtes déjà inscrit le '.$request->date;
        }
    }

    public function getFlightDay()
    {
        $flightDaysDB = flightDay::whereDate('date', '>=', date('Y-m-d'))->orderBy('date', 'asc')->orderBy('state', 'desc')->get();
        $flightDays = array();
        $currentDate = date('Y-m-d');
        foreach ($flightDaysDB as $key => $value) {
            $dateExploded = explode('-', $value->date);
            $newDate = $dateExploded[2].'/'.$dateExploded[1].'/'.$dateExploded[0];
            $user = User::find($value->userId);
            if (Auth::user()->id == $value->userId && $value->date > $currentDate) {
                $deleteButton = '&nbsp;<button class="btn btn-outline-danger btn-sm float-right" onclick="deleteFlightDayRegister('.$value->id.')"><i data-feather="trash" style="width: 16px;
  height: 16px;"></i></button>';
            } else {
                $deleteButton = '';
            }
            $flightDays[$newDate]['USER'][] = $user->name.' ('.$value->state.')'. $deleteButton;
            $flightDays[$newDate]['OBSERVATION'][] = $value->observation;
            $flightDays[$newDate]['DATE'] = $newDate;
        }
        return view('flightDayBoard', ['flightDays' => $flightDays]);
    }

    public function deleteFlightDay(Request $request)
    {
        $flightDay = flightDay::find($request->id);
        if ($flightDay->userId == Auth::user()->id) {
            $flightDay->delete();
        }
    }

    public function addPay(Request $request)
    {
        switch ($request->type) {
            case 'CB':
                $type = 'CB';
                break;
            case 'CH':
                $type = 'Chèque';
                break;
            case 'VI':
                $type = 'Virement';
                break;
        }
        $transaction = new transaction();
        $transaction->idUser = Auth::user()->id;
        $transaction->solde = ($this->getSolde(Auth::user()->id) + intval($request->amount*100));
        $transaction->value = intval($request->amount*100);
        $transaction->name = "Paiement ".$type." ".date('Y');
        $transaction->valid = 0;
        $transaction->quantity = 0;
        $transaction->year = date('Y');
        $transaction->time = time();
        $transaction->observation = $request->observation;
        $transaction->sendEmail = $request->mail;
        $transaction->save();
    }

    private function updateFlightTimestamp()
    {
        $flights = flight::where('flightTimestamp', '')->orWhere('flightTimestamp', NULL)->get();
        foreach ($flights as $key => $value) {
            $value->flightTimestamp = strtotime(str_replace('/', '-', $value->takeOffTime));
            $value->save();
        }

    }

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

    public function planches(Request $request)
    {
        $this->updateFlightTimestamp();
        $flights = array();
        if (!isset($request->start) || !isset($request->end)) {
            $start = strtotime(date('01-01-Y 00:00'));
            $end = strtotime(date('d-m-Y 23:59'));
            $startInput = date('01/01/Y');
            $endInput = date('d/m/Y');
        } else {
            $start = strtotime(str_replace('/', '-', $request->start).' 00:00');
            $end = strtotime(str_replace('/', '-', $request->end).' 23:59');
            $startInput = $request->start;
            $endInput = $request->end;
        }
        $flightsData = flight::where('flightTimestamp', '>=', $start)->where('flightTimestamp', '<=', $end)->where('transactionID', '>', 0)->orderBy('flightTimestamp', 'ASC')->get();
        $totalFlightTime = 0;
        $totalLanding = 0;
        foreach ($flightsData as $key => $value) {
            $flightArray = array();
            $flightArray['aircraft'] = aircraft::find($value->aircraftId)->name;
            $flightArray['pilot'] = User::find($value->idUser)->name;
            $flightArray['startDate'] = $value->takeOffTime;
            $flightArray['endDate'] = $value->landingTime;
            $flightArray['nbLanding'] = $value->landing;
            $totalLanding += $value->landing;
            $flightArray['flighTime'] = $this->convertMinToHM($value->totalTime);
            $totalFlightTime += $value->totalTime;
            if (aircraft::find($value->aircraftId)->type == 2) {
                $flightArray['startType'] = sailplaneStartPrice::find($value->startType)->name;
            } else {
                $flightArray['startType'] = 'Autonome';
            }
            

            $flights[] = $flightArray;
        }
        $flightArray = array();
        $flightArray['aircraft'] = '';
        $flightArray['pilot'] = '';
        $flightArray['startDate'] = '';
        $flightArray['endDate'] = 'Totaux : ';
        $flightArray['nbLanding'] = $totalLanding;
        $flightArray['flighTime'] = $this->convertMinToHM($totalFlightTime);
        $flightArray['startType'] = '';
        $flights[] = $flightArray;
        return view('planches', ['flights' => $flights, 'dates' => [$startInput, $endInput]]);
    }

    public function carnet(Request $request)
    {
        $this->updateFlightTimestamp();
        $flights = array();
        if (!isset($request->start) || !isset($request->end)) {
            $start = strtotime(date('01-01-Y 00:00'));
            $end = strtotime(date('d-m-Y 23:59'));
            $startInput = date('01/01/Y');
            $endInput = date('d/m/Y');
        } else {
            $start = strtotime(str_replace('/', '-', $request->start).' 00:00');
            $end = strtotime(str_replace('/', '-', $request->end).' 23:59');
            $startInput = $request->start;
            $endInput = $request->end;
        }
        
        $flightsData = flight::where('idUser', Auth::user()->id)->whereBetween('flightTimestamp', [$start, $end])->where('transactionID', '>', 0)->orderBy('flightTimestamp', 'ASC')->get();
        $totalFlightTime = 0;
        $totalLanding = 0;
        foreach ($flightsData as $key => $value) {
            $flightArray = array();
            $flightArray['aircraft'] = aircraft::find($value->aircraftId)->name;
            $flightArray['startDate'] = $value->takeOffTime;
            $flightArray['endDate'] = $value->landingTime;
            $flightArray['nbLanding'] = $value->landing;
            $totalLanding += $value->landing;
            $totalFlightTime += $value->totalTime;
            $flightArray['flighTime'] = $this->convertMinToHM($value->totalTime);
            if (aircraft::find($value->aircraftId)->type == 2) {
                $flightArray['startType'] = sailplaneStartPrice::find($value->startType)->name;
            } else {
                $flightArray['startType'] = 'Autonome';
            }
            

            $flights[] = $flightArray;
        }

        $flightArray = array();
        $flightArray['aircraft'] = '';
        $flightArray['pilot'] = '';
        $flightArray['startDate'] = '';
        $flightArray['endDate'] = 'Totaux : ';
        $flightArray['nbLanding'] = $totalLanding;
        $flightArray['flighTime'] = $this->convertMinToHM($totalFlightTime);
        $flightArray['startType'] = '';
        $flights[] = $flightArray;

        $externalFlightsData = flight::where('idUser', Auth::user()->id)->whereBetween('flightTimestamp', [$start, $end])->where('transactionID', '=', 0)->orderBy('flightTimestamp', 'ASC')->get();
        $externalFlights = array();
        $totalFlightTime = 0;
        $totalLanding = 0;
        foreach ($externalFlightsData as $key => $value) {
            $externalFlightArray = array();
            
            $externalFlightArray['aircraft'] = aircraft::find($value->aircraftId)->name;
            
            $externalFlightArray['startDate'] = $value->takeOffTime;
            $externalFlightArray['nbLanding'] = $value->landing;
            $externalFlightArray['flighTime'] = $this->convertMinToHM($value->totalTime);
            $totalLanding += $value->landing;
            $totalFlightTime += $value->totalTime;
            if (aircraft::find($value->aircraftId)->type == 2) {
                $externalFlightArray['startType'] = sailplaneStartPrice::find($value->startType)->name;
            } else {
                $externalFlightArray['startType'] = 'Autonome';
            }

            $externalFlightArray['aircraftType'] = '';
            

            $externalFlights[] = $externalFlightArray;
        }

        $flightArray = array();
        $flightArray['aircraft'] = '';
        $flightArray['pilot'] = '';
        $flightArray['startDate'] = '';
        $flightArray['endDate'] = 'Totaux : ';
        $flightArray['nbLanding'] = $totalLanding;
        $flightArray['flighTime'] = $this->convertMinToHM($totalFlightTime);
        $flightArray['aircraftType'] = '';
        $flightArray['startType'] = '';

        if ($totalLanding > 0) {
            $externalFlights[] = $flightArray;
        }
        

        return view('carnetVol', ['flights' => $flights, 'externalFlights' => $externalFlights, 'dates' => [$startInput, $endInput]]);
    }

    public function addFlight(Request $request)
    {
        return view('addFlight');
    }

    public function alertRead(Request $request)
    {
        alerts::markAsRead(Auth::user()->id, $request->id);
    }

    public function getPrice(Request $request)
    {
        $aircraft = aircraft::find($request->aircraft);
        $start = strtotime(str_replace('/', '-', $request->takeOffDate));
        $end = strtotime(str_replace('/', '-', $request->landingDate));
        $price = $aircraft->price($start, $end, $request->flightTime, $request->startType, $request->motorStart, $request->motorEnd, $request->nbTakeOff, $request->simulation);
        return json_encode($price);
    }

    public function getAddFlightInfoTime(Request $request)
    {

        $time = strtotime(str_replace('/', '-', $request->startDate));
        $landingTime = $time+($request->flightTime*60);
        
        
        $aircraft = aircraft::find($request->aircraft);
        //return json_encode([$time, $landingTime]);
        $existFlight = flight::selectRaw('*, (flightTimestamp+(60*totalTime)) AS landingTimestamp')->
            where('aircraftId', $aircraft->id)->whereRaw("(
            (landingTimestamp >= $time AND landingTimestamp <= $landingTime) 
            OR (flightTimestamp >= $time AND flightTimestamp <= $landingTime)
            OR (landingTimestamp >= $time AND landingTimestamp <= $landingTime)
        )")->get();

        //return json_encode([$existFlight->toArray(), $time, $landingTime]);
        
        
        if (count($existFlight) > 0) {
            return json_encode(['error', 'Un vol est dèjà enregistré sur cette période']);
        }

        if ($aircraft->type == 2) {
            return json_encode(['ok', 0, 0]);
        }

        if ($aircraft->type == 1) {
            $lastFlight = flight::where('aircraftId', $aircraft->id)
                            ->where('flightTimestamp', '<', $time)
                            ->orderBy('flightTimestamp', 'desc')
                            ->first();
            $nextFlight = flight::where('aircraftId', $aircraft->id)
                            ->where('flightTimestamp', '>', $landingTime)
                            ->orderBy('flightTimestamp', 'asc')
                            ->first();
            $lastIndex = (is_null($lastFlight)) ? 0 : $lastFlight->real_motor_end_time;
            $nextIndex = (is_null($nextFlight)) ? 0 : $nextFlight->real_motor_start_time;
            return json_encode(['ok', $lastIndex, $nextIndex]);
        }
    }

    /**
     * Afficher la page de transfert entre pilotes
     */
    public function transfer()
    {
        $users = User::where('state', 1)
                    ->where('id', '<>', Auth::id())
                    ->orderBy('name')
                    ->get();
        
        $currentBalance = (Auth::user()->updateSolde()/100);
        
        return view('transfer', compact('users', 'currentBalance'));
    }

    /**
     * Traiter le transfert entre pilotes
     */
    public function processTransfer(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01|max:10000',
            'message' => 'nullable|string|max:255'
        ]);

        $sender = Auth::user();
        $recipient = User::findOrFail($request->recipient_id);
        $amount = $request->amount;

        // Vérifier que l'utilisateur ne se transfère pas à lui-même
        if ($sender->id === $recipient->id) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas vous transférer de l\'argent à vous-même.');
        }

        // Vérifier que le pilote a suffisamment de fonds
        $currentBalance = ($sender->updateSolde()/100);

        if ($currentBalance < $amount) {
            return redirect()->back()->with('error', 'Solde insuffisant. Votre solde actuel est de ' . number_format($currentBalance, 2) . ' €.');
        }

        try {
            // Transaction de débit pour l'expéditeur
            transaction::add(
                $sender->id,
                -($amount*100),
                'Transfert vers ' . $recipient->name,
                $request->message ? $request->message : null
            );

            // Transaction de crédit pour le destinataire
            transaction::add(
                $recipient->id,
                ($amount*100),
                'Transfert reçu de ' . $sender->name,
                $request->message ? $request->message : null
            );

            return redirect()->route('home')->with('success', 'Transfert de ' . number_format($amount, 2) . ' € vers ' . $recipient->name . ' effectué avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Une erreur est survenue lors du transfert. Veuillez réessayer.');
        }
    }


}
