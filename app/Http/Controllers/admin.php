<?php
namespace App\Http\Controllers;

use App\Gesasso;
use App\Mail\sendAccount;
use App\Models\aircraft;
use App\Models\flight;
use App\Models\parametre;
use App\Models\refund;
use App\Models\sailplaneStartPrice;
use App\Models\transaction;
use App\Models\transactionType;
use App\Models\User;
use App\Models\usersAttributes;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class admin extends Controller
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

    /**
     * @param $id
     * @return mixed
     */
    private function getUserData($id)
    {
        $userDataSql = usersData::where('userID', $id)->get();
        $userData    = [];
        foreach ($userDataSql as $key => $value) {
            $userData[$value->dataName] = $value->dataValue;
        }

        return $userData;
    }

    /**
     * @param Request $request
     */
    public function addUser(Request $request)
    {
        $userExist = User::where('email', $request->mail)->get();
        if (count($userExist) > 0) {
            return 'ERROR|Cette adresse e-mail est dèjà utilisé';
        }

        $user = new User();
        //$user->password = Hash::make(uniqid());
        $user->password      = Hash::make('TEST');
        $user->email         = $request->mail;
        $user->name          = $request->name;
        $user->licenceNumber = $request->licence;
        $user->save();

        $newTrAccount         = new transaction();
        $newTrAccount->idUser = $user->id;
        $newTrAccount->name   = 'Ouverture de compte';
        $newTrAccount->value  = 0;
        $newTrAccount->solde  = 0;
        $newTrAccount->year   = date('Y');
        $newTrAccount->time   = time();
        $newTrAccount->save();

        foreach ($request->state as $key => $value) {
            $attributes                = new usersAttributes();
            $attributes->userId        = $user->id;
            $attributes->attributeName = $value;
            $attributes->save();
        }

        return 'OK|L\'utilisateur a été ajouté.';
    }

    /**
     * @param $user
     * @return mixed
     */
    private function getSolde($user)
    {
        $solde       = 0;
        $transaction = transaction::where('idUser', $user)->orderBy('time', 'desc')->orderBy('id', 'desc')->first();
        if (isset($transaction->solde)) {
            $solde = $transaction->solde;
        }

        return $solde;
    }

    /**
     * @param Request $request
     */
    public function usersList(Request $request)
    {
        $currentYear = (int) date('Y');
        $filter      = $request->filter ?? (string) $currentYear;
        $filterLabel = 'Adhérents ' . $currentYear;

        if (is_numeric($filter) && (int) $filter >= $currentYear - 4 && (int) $filter <= $currentYear) {
            $filterYear  = (int) $filter;
            $filterLabel = 'Adhérents ' . $filterYear;
            $userIds     = transaction::where('name', 'Cotisation ' . $filterYear)->pluck('idUser')->unique();
            $users       = User::whereIn('id', $userIds);
        } else {
            switch ($filter) {
                case 'all':
                    $filterLabel = 'Actifs et Inactifs';
                    $users       = User::where('id', '>', 0);
                    break;
                default:
                    $filterLabel = 'Actifs uniquement';
                    $users       = User::where('state', 1);
                    break;
            }
        }

        $users = $users->orderBy('name', 'ASC')->get();

        $attribute     = usersAttributes::all();
        $allDataUsers  = [];
        $allAttributes = [];

        foreach ($attribute as $key => $value) {
            $allAttributes[$value->userId][] = $value->attributeName;
        }
        $totaux                                      = [];
        $totaux['Adhérents (nb)']                   = 0;
        $totaux['Total des soldes (€)']            = 0;
        $totaux['Total des comptes positifs (€)']  = 0;
        $totaux['Total des comptes négatifs (€)'] = 0;

        foreach ($users as $key => $value) {
            $totaux['Adhérents (nb)']++;
            $allDataUsers[$value->id]        = $value;
            $allDataUsers[$value->id]->solde = number_format(($this->getSolde($value->id) / 100), 2);
            $totaux['Total des soldes (€)'] += floatval($allDataUsers[$value->id]->solde);
            if ($allDataUsers[$value->id]->solde > 0) {
                $totaux['Total des comptes positifs (€)'] += floatval($allDataUsers[$value->id]->solde);
            } else {
                $totaux['Total des comptes négatifs (€)'] += floatval($allDataUsers[$value->id]->solde);
            }
            if (isset($allAttributes[$value->id])) {
                $allDataUsers[$value->id]->userAttributes = $allAttributes[$value->id];
                foreach ($allDataUsers[$value->id]->userAttributes as $idUser => $attributes) {
                    if (isset($totaux[$attributes . ' (nb)'])) {
                        $totaux[$attributes . ' (nb)']++;
                    } else {
                        $totaux[$attributes . ' (nb)'] = 1;
                    }
                }
            } else {
                $allDataUsers[$value->id]->userAttributes = [];
            }
        }

        return view('usersList', ['users' => $allDataUsers, 'totaux' => $totaux, 'filterLabel' => $filterLabel]);
    }

    private function buildUserExportRows(array $cols, $users, array $allAttributes): array
    {
        $colLabels = [
            'name'          => 'Nom',
            'email'         => 'Email',
            'licenceNumber' => 'Numéro de licence',
            'sexe'          => 'Sexe',
            'FFVP'          => 'FFVP',
            'FFPLUM'        => 'FFPLUM',
            'isSupervisor'  => 'Instructeur',
            'isAdmin'       => 'Administrateur',
            'state'         => 'Actif',
            'solde'         => 'Solde (€)',
            'attributes'    => 'Attributs',
            'created_at'    => 'Date de création',
        ];

        $rows    = [];
        $userIds = [];
        foreach ($users as $user) {
            $userIds[] = $user->id;
            $row = [];
            foreach ($cols as $col) {
                switch ($col) {
                    case 'sexe':
                        $map = [0 => 'Non spécifié', 1 => 'Homme', 2 => 'Femme'];
                        $row[$col] = $map[$user->sexe] ?? 'Non spécifié';
                        break;
                    case 'FFVP':
                    case 'FFPLUM':
                    case 'isSupervisor':
                    case 'isAdmin':
                    case 'state':
                        $row[$col] = $user->$col ? 'Oui' : 'Non';
                        break;
                    case 'solde':
                        $row[$col] = number_format($this->getSolde($user->id) / 100, 2);
                        break;
                    case 'attributes':
                        $row[$col] = isset($allAttributes[$user->id]) ? implode(', ', $allAttributes[$user->id]) : '';
                        break;
                    case 'created_at':
                        $row[$col] = $user->created_at ? date('d/m/Y', strtotime($user->created_at)) : '';
                        break;
                    default:
                        $row[$col] = $user->$col ?? '';
                }
            }
            $rows[] = $row;
        }

        $headers = array_map(fn($c) => $colLabels[$c] ?? $c, $cols);
        return ['headers' => $headers, 'rows' => $rows, 'userIds' => $userIds];
    }

    private function resolveExportUsers(Request $request)
    {
        $filter = $request->input('filter', 'active');
        $currentYear = (int) date('Y');

        if (str_starts_with($filter, 'year:')) {
            $year = (int) substr($filter, 5);
            $userIds = transaction::where('name', 'Cotisation ' . $year)->pluck('idUser')->unique();
            $users = User::whereIn('id', $userIds);
        } elseif ($filter === 'all') {
            $users = User::where('id', '>', 0);
        } else {
            $users = User::where('state', 1);
        }

        return $users->orderBy('name', 'ASC')->get();
    }

    public function exportUsersPage(Request $request)
    {
        $availableCols = [
            'name'          => 'Nom',
            'email'         => 'Email',
            'licenceNumber' => 'Numéro de licence',
            'sexe'          => 'Sexe',
            'FFVP'          => 'FFVP',
            'FFPLUM'        => 'FFPLUM',
            'isSupervisor'  => 'Instructeur',
            'isAdmin'       => 'Administrateur',
            'state'         => 'Actif',
            'solde'         => 'Solde (€)',
            'attributes'    => 'Attributs',
            'created_at'    => 'Date de création',
        ];
        $defaultCols = ['name', 'email', 'licenceNumber', 'state', 'solde'];

        if ($request->isMethod('GET')) {
            return view('admin.exportUsers', [
                'availableCols' => $availableCols,
                'defaultCols'   => $defaultCols,
                'rows'          => null,
                'headers'       => [],
                'userIds'       => [],
                'selectedCols'  => $defaultCols,
                'filter'        => 'active',
            ]);
        }

        $cols = $request->input('cols', $defaultCols);
        $cols = array_filter($cols, fn($c) => isset($availableCols[$c]));
        $cols = array_values($cols);

        $users = $this->resolveExportUsers($request);
        $allAttributes = [];
        foreach (usersAttributes::all() as $attr) {
            $allAttributes[$attr->userId][] = $attr->attributeName;
        }

        $data = $this->buildUserExportRows($cols, $users, $allAttributes);

        return view('admin.exportUsers', [
            'availableCols' => $availableCols,
            'defaultCols'   => $defaultCols,
            'rows'          => $data['rows'],
            'headers'       => $data['headers'],
            'userIds'       => $data['userIds'],
            'selectedCols'  => $cols,
            'filter'        => $request->input('filter', 'active'),
        ]);
    }

    public function exportUsersCsvDownload(Request $request)
    {
        $availableCols = [
            'name'          => 'Nom',
            'email'         => 'Email',
            'licenceNumber' => 'Numéro de licence',
            'sexe'          => 'Sexe',
            'FFVP'          => 'FFVP',
            'FFPLUM'        => 'FFPLUM',
            'isSupervisor'  => 'Instructeur',
            'isAdmin'       => 'Administrateur',
            'state'         => 'Actif',
            'solde'         => 'Solde (€)',
            'attributes'    => 'Attributs',
            'created_at'    => 'Date de création',
        ];
        $defaultCols = ['name', 'email', 'licenceNumber', 'state', 'solde'];

        $cols = $request->input('cols', $defaultCols);
        $cols = array_filter($cols, fn($c) => isset($availableCols[$c]));
        $cols = array_values($cols);
        if (empty($cols)) {
            $cols = $defaultCols;
        }

        $users = $this->resolveExportUsers($request);
        $ids   = array_filter((array) $request->input('ids', []));
        if (!empty($ids)) {
            $users = $users->filter(fn($u) => in_array($u->id, $ids))->values();
        }

        $allAttributes = [];
        foreach (usersAttributes::all() as $attr) {
            $allAttributes[$attr->userId][] = $attr->attributeName;
        }

        $data = $this->buildUserExportRows($cols, $users, $allAttributes);
        $lines = [];
        $lines[] = implode(';', $data['headers']);
        foreach ($data['rows'] as $row) {
            $cells = array_map(fn($v) => '"' . str_replace('"', '""', $v) . '"', array_values($row));
            $lines[] = implode(';', $cells);
        }

        $csv = "\xEF\xBB\xBF" . implode("\r\n", $lines);
        $filename = 'utilisateurs_export_' . date('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function usersExportCsv(Request $request)
    {
        $currentYear = (int) date('Y');
        $filterLabel = 'actifs';

        if (is_numeric($request->filter) && (int) $request->filter >= $currentYear - 4 && (int) $request->filter <= $currentYear) {
            $filterYear  = (int) $request->filter;
            $filterLabel = 'adherents_' . $filterYear;
            $userIds     = transaction::where('name', 'Cotisation ' . $filterYear)->pluck('idUser')->unique();
            $users       = User::whereIn('id', $userIds);
        } else {
            switch ($request->filter) {
                case 'all':
                    $filterLabel = 'tous';
                    $users       = User::where('id', '>', 0);
                    break;
                default:
                    $users = User::where('state', 1);
                    break;
            }
        }

        $users     = $users->orderBy('name', 'ASC')->get();
        $attribute = usersAttributes::all();

        $allAttributes = [];
        foreach ($attribute as $value) {
            $allAttributes[$value->userId][] = $value->attributeName;
        }

        $rows   = [];
        $rows[] = implode(';', ['Nom', 'Email', 'Licence FFVP', 'Solde (€)', 'Attributs', 'Actif']);

        foreach ($users as $user) {
            $solde      = number_format(($this->getSolde($user->id) / 100), 2);
            $attributes = isset($allAttributes[$user->id]) ? implode(', ', $allAttributes[$user->id]) : '';
            $actif      = $user->state ? 'Oui' : 'Non';

            $rows[] = implode(';', [
                '"' . str_replace('"', '""', $user->name) . '"',
                '"' . str_replace('"', '""', $user->email) . '"',
                '"' . str_replace('"', '""', $user->licenceNumber ?? '') . '"',
                $solde,
                '"' . str_replace('"', '""', $attributes) . '"',
                $actif,
            ]);
        }

        $csv      = "\xEF\xBB\xBF" . implode("\r\n", $rows); // BOM UTF-8 pour Excel
        $filename = 'pilotes_' . $filterLabel . '_' . date('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function getValidTransactions()
    {
        $transactions        = transaction::where('valid', 0)->get();
        $allDataTransactions = [];
        foreach ($transactions as $key => $value) {
            $user                  = User::find($value->idUser);
            $value->CompleteName   = $user->name;
            $allDataTransactions[] = $value;
        }
        return view('validTransactions', ['transactions' => $allDataTransactions]);
    }

    /**
     * @param Request $request
     */
    public function ValidTransactions(Request $request)
    {
        $transaction        = transaction::find($request->id);
        $transaction->valid = 1;
        $transaction->save();
        if ($transaction->sendEmail == 1) {
            # code...
        }
    }
    /**
     * @param Request $request
     */
    public function DeleteTransactions(Request $request)
    {
        $transaction = transaction::find($request->id);
        $user        = User::find($transaction->idUser);
        if (! is_null($transaction->refundId) && $transaction->refundId != 0) {
            $refund = refund::find($transaction->refundId);
            Storage::delete($refund->file);
            $refund->delete();
        }
        $transaction->delete();
        $user->updateSolde();
        return back();
    }

    /**
     * @param Request $request
     */
    public function validNewTrDate(Request $request)
    {
        $newTime           = strtotime(str_replace('/', '-', $request->date));
        $transaction       = transaction::find($request->id);
        $transaction->year = date('Y', $newTime);
        $transaction->time = $newTime;
        $userId            = $transaction->idUser;
        $transaction->save();

        /*
        $transactions = transaction::where('idUser', $request->selectUserInTransaction)->orderBy('time', 'asc')->orderBy('id', 'ASC')->get();
        $solde = 0;
        foreach ($transactions as $key => $value) {
        $transactions[$key]->solde = $solde+$value->value;
        $solde = $transactions[$key]->solde;
        $transactions[$key]->save();
        }
         */
        return redirect('updateSolde?selectUserInTransaction=' . $userId);
    }

    /**
     * @param Request $request
     */
    public function updateSolde(Request $request)
    {
        $transactions = transaction::where('idUser', $request->selectUserInTransaction)->orderBy('time', 'asc')->orderBy('id', 'ASC')->get();
        $solde        = 0;
        foreach ($transactions as $key => $value) {
            $transactions[$key]->solde = $solde + $value->value;
            $solde                     = $transactions[$key]->solde;
            $transactions[$key]->save();
        }

        return redirect('saisie?selectUserInTransaction=' . $request->selectUserInTransaction);
    }

    /**
     * @param $minutes
     * @return mixed
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
     * @param Request $request
     * @return null
     */
    public function validNewAdminFlight(Request $request)
    {

        $flight                   = new flight();
        $flight->idUser           = $request->user;
        $flight->totalTime        = $request->flightTime;
        $flight->takeOffTime      = $request->takeOffDate;
        $flight->landingTime      = $request->landingDate;
        $flight->landing          = $request->nbTakeOff;
        $flight->aircraftId       = $request->aircraft;
        $flight->motorStartTime   = $request->startMotor;
        $flight->motorEndTime     = $request->endMotor;
        $flight->airPortStartCode = 'LFCT';
        $flight->airPortEndCode   = 'LFCT';
        $flight->startType        = $request->startType;
        $flight->flightTimestamp  = strtotime(str_replace('/', '-', $flight->takeOffTime));
        $flight->userPayId        = $request->userPay;
        if ($request->supervisor !== '') {
            $flight->idInstructor = intval($request->supervisor);
        }
        $aircraft  = aircraft::find($request->aircraft);
        $startType = sailplaneStartPrice::find($request->startType);
        $price     = $aircraft->price(
            $request->takeOffDate,
            $request->landingDate,
            $request->flightTime,
            $request->startType,
            $request->startMotor,
            $request->endMotor,
            $request->nbTakeOff,
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
        $transaction->idUser      = $flight->userPayId;
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

    /**
     * @param Request $request
     * @return mixed
     */
    public function flightList(Request $request)
    {

        if (isset($request->deleteFlight)) {
            flight::deleteFlight($request->deleteFlight);
        }

        $flights       = [];
        $currentFilter = 'C';
        if (isset($request->filterID)) {
            $currentFilter = $request->filterID;
        }

        if (! isset($request->year)) {
            return redirect($request->fullUrlWithQuery(['year' => date('Y')]));
            //return redirect('')
        }
        $export            = [];
        $export['gesasso'] = $request->fullUrlWithQuery(['export' => 'gesasso']);
        $filterType        = Route::currentRouteName();
        $filterList        = [];
        $year              = $request->year;
        if (! isset($request->year)) {
            $yearMin = strtotime(date('Y') . '-01-01 00:00:00');
            $yearMax = strtotime(date('Y') . '-01-01 00:00:00');
        } else {
            $yearMin = strtotime($request->year . '-01-01 00:00:00');
            $yearMax = strtotime($request->year . '-12-31 23:59:59');
        }

        if ($filterType == 'aircraftFlights') {
            $filterListData = aircraft::all();
            foreach ($filterListData as $key => $value) {
                $filterList[] = [$value->id, $value->name];
            }
            if (isset($request->filterID) && $request->filterID > 0) {
                $nameExport  = aircraft::find($request->filterID)->name;
                $flightsData = flight::where('aircraftId', $currentFilter)->where('flightTimestamp', '>=', $yearMin)->where('flightTimestamp', '<=', $yearMax)->orderBy('flightTimestamp')->get();
            } elseif ($request->filterID == 0) {
                $nameExport  = 'tout les appareils';
                $flightsData = flight::where('id', '>', 0)->where('flightTimestamp', '>=', $yearMin)->where('flightTimestamp', '<=', $yearMax)->orderBy('flightTimestamp')->get();
            }
        }

        if ($filterType == 'pilotFlights') {
            $filterListData = User::all();
            foreach ($filterListData as $key => $value) {
                $filterList[] = [$value->id, $value->name];
            }

            if (isset($request->filterID) && $request->filterID > 0) {
                $nameExport  = User::find($request->filterID)->name;
                $flightsData = flight::where('idUser', $currentFilter)->where('flightTimestamp', '>=', $yearMin)->where('flightTimestamp', '<=', $yearMax)->orderBy('flightTimestamp')->get();
            } elseif ($request->filterID == 0) {
                $nameExport  = 'tout les pilotes';
                $flightsData = flight::where('id', '>', 0)->where('flightTimestamp', '>=', $yearMin)->where('flightTimestamp', '<=', $yearMax)->orderBy('flightTimestamp')->get();
            }
        }

        $totalTime           = 0;
        $totalMotorTime      = 0;
        $totalPrice          = 0;
        $totalDayTime        = 0;
        $totalLanding        = 0;
        $previousDay         = 0;
        $totalInstruction    = [0, 0, 0, 0, []];
        $totalNoInstruction  = [0, 0, 0, 0, []];
        $stat                = [];
        $stat['instruction'] = $totalInstruction;
        $stat['normal']      = $totalNoInstruction;

        if (isset($flightsData)) {
            $flight             = [];
            $flightDayTimeArray = [];
            foreach ($flightsData as $key => $value) {
                $aircraft                                                 = aircraft::find($value->aircraftId);
                $user                                                     = User::find($value->idUser);
                $flight['id']                                             = $value->id;
                $flight['data']                                           = $value;
                $flight['aircraft']                                       = $aircraft->name;
                $flight['pilot']                                          = $user->name;
                $flight['startDate']                                      = $value->takeOffTime;
                $flight['endDate']                                        = $value->landingTime;
                $flight['nbLanding']                                      = $value->landing;
                $flightDayTimeArray[explode(' ', $value->landingTime)[0]] = 1;
                $flight['flighTime']                                      = $this->convertMinToHM($value->totalTime);
                if ($aircraft->type == 1) {
                    $flight['startType'] = ' A ';
                } elseif ($aircraft->type == 2) {
                    $startType           = sailplaneStartPrice::find($value->startType);
                    $flight['startType'] = $startType->name;
                }

                if ($aircraft->type == 2) {
                    $flight['motorTime'] = '';
                } elseif ($aircraft->type == 1) {
                    $flight['motorTime'] = intval(($value->real_motor_end_time - $value->real_motor_start_time) * 100);
                    $totalMotorTime += $flight['motorTime'];
                }
                $flight['price'] = number_format(($value->value / 100), 2) . " €";
                $flights[]       = $flight;
                $totalTime += $value->totalTime;
                $totalLanding += $value->landing;
                $totalPrice += $value->value;
                if ($value->idInstructor != 0 && $value->idInstructor != null) {
                    $totalInstruction[$user->sexe] += $value->totalTime;
                    $totalInstruction[2] += $value->totalTime;
                    $totalInstruction[3] += $value->landing;
                    if (! isset($totalInstruction[4][$flight['startType']])) {
                        $totalInstruction[4][$flight['startType']] = 0;
                    }
                    $totalInstruction[4][$flight['startType']]++;
                } else {
                    $totalNoInstruction[$user->sexe] += $value->totalTime;
                    $totalNoInstruction[2] += $value->totalTime;
                    $totalNoInstruction[3] += $value->landing;
                    if (! isset($totalNoInstruction[4][$flight['startType']])) {
                        $totalNoInstruction[4][$flight['startType']] = 0;
                    }
                    $totalNoInstruction[4][$flight['startType']]++;
                }
                if ($previousDay != $value->takeOffTime) {
                    $totalDayTime++;
                }
                $previousDay = $value->takeOffTime;
            }
            $totalDayTime        = count($flightDayTimeArray);
            $stat['instruction'] = $totalInstruction;
            $stat['normal']      = $totalNoInstruction;
        }
        $stat['instruction'][0] = $this->convertMinToHM($stat['instruction'][0]);
        $stat['instruction'][1] = $this->convertMinToHM($stat['instruction'][1]);
        $stat['instruction'][2] = $this->convertMinToHM($stat['instruction'][2]);

        $stat['normal'][0] = $this->convertMinToHM($stat['normal'][0]);
        $stat['normal'][1] = $this->convertMinToHM($stat['normal'][1]);
        $stat['normal'][2] = $this->convertMinToHM($stat['normal'][2]);
        //dd($flights);
        $flights[] = ['aircraft' => 'TOTAL', 'pilot' => '', 'startDate' => $totalDayTime . ' Jour(s)', 'endDate' => '', 'nbLanding' => $totalLanding, 'flighTime' => $this->convertMinToHM($totalTime), 'startType' => '', 'motorTime' => $this->convertMinToHM(($totalMotorTime / 100) * 60), 'price' => number_format(($totalPrice / 100), 2) . " €"];
        //var_dump($flights);

        if (isset($request->export)) {
            $nameExport = str_replace(' ', '-', $nameExport);
            return $this->flightExport($request->export, $this->getFlightsFromId($flights), $nameExport, $request->year);
        }
        return view('flights', ['stat' => $stat, 'filters' => $filterList, 'currentFilter' => $currentFilter, 'flights' => $flights, 'year' => $year, 'export' => $export]);
    }

    /**
     * @param $flights
     */
    private function getFlightsFromId($flights)
    {
        $allId = [];

        foreach ($flights as $flight) {
            if (isset($flight['id'])) {
                $allId[] = $flight['id'];
            }
        }
        return flight::whereIn('id', $allId)->get();
    }

    /**
     * @param $export
     * @param $flights
     * @param $name
     * @param $year
     * @return mixed
     */
    private function flightExport($export, $flights, $name, $year)
    {
        switch ($export) {
            case 'gesasso':
                return $this->exportGesasso($flights, $name, $year);
                break;

            default:
                return 'NO-EXPORT';
                break;
        }
    }

    /**
     * @param $flights
     * @param $name
     * @param $year
     */
    private function exportGesasso($flights, $name, $year)
    {
        $line   = [];
        $line[] = "Date;Aéronef;Aéronef externe;Individu 1;Individu 1 externe;Individu 2;Individu 2 externe;Vol d'instruction;Heure de décollage;Heure de l'atterrissage;Durée;Code OACI du décollage;Code OACI de l'atterrissage;Nombre de décollage/atterrissage;Moyen de mise en l'air;Temps moteur;Temps moteur en h:m;Immatriculation du remorqueur;Remorqueur externe;Individu 1 dans le remorqueur;Individu 1 externe dans le remorqueur;Individu 2 dans le remorqueur;Individu 2 externe dans le remorqueur;Instruction Remorqueur;Treuil;Treuil externe;Treuilleur;Treuilleur externe;Commentaire";
        foreach ($flights as $flight) {
            $element   = [];
            $element[] = date('d/m/Y', $flight->flightTimestamp);
            $element[] = $flight->aircraft->register;
            $element[] = '';

            if ($flight->idInstructor > 0) {
                $element[] = $flight->instructor->licenceNumber;
                $element[] = '';
                if ($flight->user->FFVP) {
                    $element[] = $flight->user->licenceNumber;
                    $element[] = '';
                } else {
                    $element[] = 'Pilote externe';
                    $element[] = 1;
                }
                $element[] = 1;
            } else {
                if ($flight->user->FFVP) {
                    $element[] = $flight->user->licenceNumber;
                    $element[] = '';
                } else {
                    $element[] = 'Pilote externe';
                    $element[] = 1;
                }
                $element[] = '';
                $element[] = '';
                $element[] = '';
            }

            $element[] = explode(' ', $flight->takeOffTime)[1];
            $element[] = explode(' ', $flight->landingTime)[1];
            $element[] = '';
            $element[] = $flight->airportStartCode;
            $element[] = $flight->airportEndCode;
            $element[] = $flight->landing;

            if ($flight->aircraft->type == 1) {
                $element[] = 'Autonome';
                $element[] = intval((($flight->motorEndTime * 100) - ($flight->motorStartTime * 100)));
                $element[] = '';
                $element[] = '';
                $element[] = '';
                $element[] = '';
                $element[] = '';
            } else {
                $element[] = 'Remorquage';
                $element[] = 10;
                $element[] = '';
                $element[] = '79-LO';
                $element[] = '';
                $element[] = 'PILOTE EXTERNE';
                $element[] = 1;
            }

            $element[] = '';
            $element[] = '';
            $element[] = '';
            $element[] = '';
            $element[] = '';
            $element[] = '';
            $element[] = '';
            $element[] = '';

            $line[] = implode(';', $element);
            //dd([$flight, $line]);
        }
        $csv      = implode("\r\n", $line);
        $filename = 'export-gesasso_' . $name . '_' . $year;
        return response()->csv($csv, $filename);
        dd($line);
    }

    public function updateAndControlData()
    {
        echo 'Controle de la base de données<br>';
        $fault   = 0;
        $flights = flight::where('flightTimestamp', '')->orWhere('flightTimestamp', null)->get();
        foreach ($flights as $key => $value) {
            echo 'Mise a jour TimeStamp Flight : ' . $value->id . '<br>';
            $value->flightTimestamp = strtotime(str_replace('/', '-', $value->takeOffTime));
            $value->save();
            $fault++;
        }

        $flights = flight::where('userPayId', null)->orWhere('userPayId', '')->orWhere('userPayId', 0)->get();
        foreach ($flights as $key => $value) {
            echo 'Mise a jour Utilisateur Facturé Flight : ' . $value->id . '<br>';
            $value->userPayId = $value->idUser;
            $value->save();
            $fault++;
        }

        $flights = flight::where('transactionID', null)->orWhere('transactionID', '')->get();
        foreach ($flights as $key => $value) {
            echo 'Mise a jour Transaction associé Flight : ' . $value->id . '<br>';
            $transaction = transaction::where('idUser', $value->userPayId)->where('time', $value->flightTimestamp)->first();
            if ($transaction->value == (0 - $value->value)) {
                $value->transactionID = $transaction->id;
                $value->save();
            }
            $fault++;
        }

        $users = User::all();

        foreach ($users as $keyUsers => $user) {
            $transactions = transaction::where('idUser', $user->id)->orderBy('time', 'asc')->orderBy('id', 'ASC')->get();
            $solde        = 0;
            foreach ($transactions as $key => $value) {
                $transactions[$key]->solde = $solde + $value->value;
                $solde                     = $transactions[$key]->solde;
                $transactions[$key]->save();
            }
            echo 'Solde compte :' . $user->name . ' = ' . ($solde / 100) . '<br>';
        }

        echo '<br>Controle de la base de données terminée ' . $fault . ' défauts corrigés.';
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function accountExport(Request $request)
    {
        $selectedUser = 0;
        $year         = date('Y');
        $transactions = [];
        if (isset($request->user)) {
            $selectedUser     = User::where('id', $request->user)->first();
            $transactionsUser = transaction::where('idUser', $request->user)
                ->where('year', $year)
                ->orderBy('time', 'asc')
                ->orderBy('id', 'ASC')
                ->get();
            foreach ($transactionsUser as $key => $value) {
                $transactions[] = ['time' => date('d/m/Y H:i', $value->time), 'value' => number_format(($value->value / 100), 2), 'solde' => number_format(($value->solde / 100), 2), 'name' => $value->name, 'id' => $value->id, 'observation' => $value->observation];
            }

            $transactionType = [];

            $transactionTypeData = transactionType::all();
            foreach ($transactionTypeData as $key => $value) {
                $value->name       = $value->name . ' ' . date('Y');
                $transactionType[] = $value;
            }

            $aircraft            = aircraft::all();
            $sailplaneStartPrice = sailplaneStartPrice::all();
            $filename            = 'CVVT-' . str_replace(' ', '_', strtoupper($selectedUser->name)) . '_' . date('d-m-Y_H-i') . '.pdf';
            $pdf                 = Pdf::loadView('exportPdfAccount', ['transactions' => $transactions, 'selectedUser' => $selectedUser, 'transactionType' => $transactionType, 'aircrafts' => $aircraft, 'sailplaneStartPrices' => $sailplaneStartPrice]);
            return $pdf->download($filename);
            //return $pdf->stream();

            //return view('exportPdfAccount', ['transactions' => $transactions, 'selectedUser' => $selectedUser, 'transactionType' => $transactionType, 'aircrafts' => $aircraft, 'sailplaneStartPrices' => $sailplaneStartPrice]);
        } else {
            echo 'ERREUR';
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function invoiceExport(Request $request)
    {
        $selectedUser = 0;
        $year         = $request->year ?? date('Y'); // Permet de sélectionner l'année
        $transactions = [];
        if (isset($request->user)) {
            $selectedUser     = User::where('id', $request->user)->first();
            $transactionsUser = transaction::where('idUser', $request->user)
                ->where('year', $year)
                ->where('value', '<', 0) // Seulement les transactions négatives
                ->orderBy('time', 'asc')
                ->orderBy('id', 'ASC')
                ->get();
            foreach ($transactionsUser as $key => $value) {
                $transactions[] = ['time' => date('d/m/Y H:i', $value->time), 'value' => number_format(($value->value / 100), 2), 'name' => $value->name, 'id' => $value->id, 'observation' => $value->observation];
            }

            $transactionType = [];

            $transactionTypeData = transactionType::all();
            foreach ($transactionTypeData as $key => $value) {
                $value->name       = $value->name . ' ' . date('Y');
                $transactionType[] = $value;
            }

            $aircraft            = aircraft::all();
            $sailplaneStartPrice = sailplaneStartPrice::all();
            
            // Calculer le solde du compte pour l'année
            $accountBalance = transaction::where('idUser', $request->user)
                ->where('year', $year)
                ->orderBy('time', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            $currentBalance = $accountBalance ? ($accountBalance->solde / 100) : 0;
            
            // Déterminer le numéro de facture
            // Récupérer la date de clôture des écritures (par défaut 31/12 de l'année n-2)
            $currentYear = date('Y');
            $defaultClosureDate = ($currentYear - 2) . '-12-31';
            $closureDateStr = parametre::getValue('Date de cloture des écritures', $defaultClosureDate);
            
            // Convertir la date de clôture en timestamp (gère plusieurs formats)
            $closureDate = strtotime($closureDateStr);
            if ($closureDate === false) {
                // Si le format n'est pas reconnu, essayer avec le format français
                $closureDate = strtotime(str_replace('/', '-', $closureDateStr));
            }
            if ($closureDate === false) {
                // En dernier recours, utiliser la date par défaut
                $closureDate = strtotime($defaultClosureDate);
            }
            
            // Date d'émission de la facture (aujourd'hui)
            $invoiceDate = time();
            
            // Récupérer le dernier numéro de facture de l'année
            $lastInvoiceNumberParam = 'Dernier numéro facture ' . $year;
            $lastInvoiceNumber = parametre::getValue($lastInvoiceNumberParam, 0);
            $invoiceSequence = intval($lastInvoiceNumber) + 1;
            
            // Formater le numéro séquentiel sur 4 chiffres
            $invoiceSequenceFormatted = str_pad($invoiceSequence, 4, '0', STR_PAD_LEFT);
            
            // Déterminer le suffixe selon la date
            if ($invoiceDate > $closureDate) {
                // Facture postérieure à la date de clôture : PROVISOIRE
                $invoiceNumber = 'F' . $invoiceSequenceFormatted . '-PROVISOIRE';
            } else {
                // Facture antérieure ou égale à la date de clôture : avec ID utilisateur
                $invoiceNumber = 'F' . $invoiceSequenceFormatted . '-' . $selectedUser->id;
            }
            
            // Mettre à jour le dernier numéro de facture pour l'année
            parametre::getOrCreate($lastInvoiceNumberParam, $invoiceSequence);
            $lastInvoiceParam = parametre::where('nom', $lastInvoiceNumberParam)->first();
            if ($lastInvoiceParam) {
                $lastInvoiceParam->value = $invoiceSequence;
                $lastInvoiceParam->save();
            }
            
            $filename            = 'CVVT-FACTURE-' . str_replace(' ', '_', strtoupper($selectedUser->name)) . '_' . $year . '_' . date('d-m-Y_H-i') . '.pdf';
            $pdf                 = Pdf::loadView('exportPdfInvoice', ['transactions' => $transactions, 'selectedUser' => $selectedUser, 'transactionType' => $transactionType, 'aircrafts' => $aircraft, 'sailplaneStartPrices' => $sailplaneStartPrice, 'year' => $year, 'currentBalance' => $currentBalance, 'invoiceNumber' => $invoiceNumber]);
            return $pdf->download($filename);
            //return $pdf->stream();

            //return view('exportPdfInvoice', ['transactions' => $transactions, 'selectedUser' => $selectedUser, 'transactionType' => $transactionType, 'aircrafts' => $aircraft, 'sailplaneStartPrices' => $sailplaneStartPrice, 'year' => $year, 'currentBalance' => $currentBalance]);
        } else {
            echo 'ERREUR';
        }
    }

    public function sendAccountStateForUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $currentYear = intval(date('Y'));
        $hasCurrentYear = transaction::where('idUser', $user->id)->where('year', $currentYear)->exists();
        $statementYear  = $hasCurrentYear ? $currentYear : ($currentYear - 1);

        $transactionsUser = transaction::where('idUser', $user->id)
            ->where('year', $statementYear)
            ->orderBy('time', 'asc')
            ->orderBy('id', 'ASC')
            ->get();

        $transactions = [];
        foreach ($transactionsUser as $value) {
            $transactions[] = [
                'time'        => date('d/m/Y H:i', $value->time),
                'value'       => number_format($value->value / 100, 2),
                'solde'       => number_format($value->solde / 100, 2),
                'name'        => $value->name,
                'id'          => $value->id,
                'observation' => $value->observation,
            ];
        }

        $transactionType = [];
        foreach (transactionType::all() as $value) {
            $value->name       = $value->name . ' ' . date('Y');
            $transactionType[] = $value;
        }

        $aircraft            = aircraft::all();
        $sailplaneStartPrice = sailplaneStartPrice::all();

        $filename = 'CVVT-' . str_replace(' ', '_', strtoupper($user->name)) . '_' . date('d-m-Y_H-i') . '.pdf';
        $pdf = Pdf::loadView('exportPdfAccount', [
            'transactions'         => $transactions,
            'selectedUser'         => $user,
            'transactionType'      => $transactionType,
            'aircrafts'            => $aircraft,
            'sailplaneStartPrices' => $sailplaneStartPrice,
        ]);
        $pdf->save('../storage/app/userAcountState/' . $filename);
        Mail::to($user->email)->send(new sendAccount($user->name, 'userAcountState/' . $filename, $user->realAmountAccount, $user->email));

        return redirect('/usersList')->with('success', 'Extrait de compte envoyé à ' . $user->name . '.');
    }

    public function sendAccountStatePreview(Request $request, $year)
    {
        $year = intval($year);
        $userIds = transaction::where('name', 'Cotisation ' . $year)->pluck('idUser')->unique();
        $users = User::whereIn('id', $userIds)->orderBy('name')->get();
        return view('admin.sendAccountStatePreview', compact('users', 'year'));
    }

    public function sendAccountStateForYear(Request $request, $year)
    {
        $year    = intval($year);
        $userIds = transaction::where('name', 'Cotisation ' . $year)->pluck('idUser')->unique();
        $users   = User::whereIn('id', $userIds)->get();

        $file = new Filesystem;
        $file->cleanDirectory('userAcountState');

        $transactionType     = [];
        $transactionTypeData = transactionType::all();
        foreach ($transactionTypeData as $value) {
            $value->name       = $value->name . ' ' . $year;
            $transactionType[] = $value;
        }

        $aircraft            = aircraft::all();
        $sailplaneStartPrice = sailplaneStartPrice::all();

        foreach ($users as $user) {
            $transactionsUser = transaction::where('idUser', $user->id)
                ->orderBy('time', 'asc')
                ->orderBy('id', 'ASC')
                ->get();

            $transactions = [];
            foreach ($transactionsUser as $value) {
                $transactions[] = [
                    'time'        => date('d/m/Y H:i', $value->time),
                    'value'       => number_format($value->value / 100, 2),
                    'solde'       => number_format($value->solde / 100, 2),
                    'name'        => $value->name,
                    'id'          => $value->id,
                    'observation' => $value->observation,
                ];
            }

            $filename = 'CVVT-' . str_replace(' ', '_', strtoupper($user->name)) . '_' . date('d-m-Y_H-i') . '.pdf';
            $pdf = Pdf::loadView('exportPdfAccount', [
                'transactions'       => $transactions,
                'selectedUser'       => $user,
                'transactionType'    => $transactionType,
                'aircrafts'          => $aircraft,
                'sailplaneStartPrices' => $sailplaneStartPrice,
            ]);
            $pdf->save('../storage/app/userAcountState/' . $filename);
            //Mail::to($user->email)->send(new sendAccount($user->name, 'userAcountState/' . $filename));
        }

        return redirect('/usersList')->with('success', 'Emails envoyés à ' . count($users) . ' adhérent(s) ' . $year . '.');
    }

    public function sendAccountStateForYearTest(Request $request, $year)
    {
        $testEmail = auth()->user()->email;
        $year      = intval($year);
        $userIds   = transaction::where('name', 'Cotisation ' . $year)->pluck('idUser')->unique();
        $users     = User::whereIn('id', $userIds)->get();

        $file = new Filesystem;
        $file->cleanDirectory('userAcountState');

        $transactionType = [];
        foreach (transactionType::all() as $value) {
            $value->name       = $value->name . ' ' . $year;
            $transactionType[] = $value;
        }

        $aircraft            = aircraft::all();
        $sailplaneStartPrice = sailplaneStartPrice::all();

        foreach ($users as $user) {
            $transactionsUser = transaction::where('idUser', $user->id)->orderBy('time', 'asc')->orderBy('id', 'ASC')->get();
            $transactions = [];
            foreach ($transactionsUser as $value) {
                $transactions[] = ['time' => date('d/m/Y H:i', $value->time), 'value' => number_format($value->value / 100, 2), 'solde' => number_format($value->solde / 100, 2), 'name' => $value->name, 'id' => $value->id, 'observation' => $value->observation];
            }
            $filename = 'CVVT-' . str_replace(' ', '_', strtoupper($user->name)) . '_' . date('d-m-Y_H-i') . '.pdf';
            $pdf = Pdf::loadView('exportPdfAccount', ['transactions' => $transactions, 'selectedUser' => $user, 'transactionType' => $transactionType, 'aircrafts' => $aircraft, 'sailplaneStartPrices' => $sailplaneStartPrice]);
            $pdf->save('../storage/app/userAcountState/' . $filename);
            Mail::to($testEmail)->send(new sendAccount($user->name, 'userAcountState/' . $filename, $user->realAmountAccount, $user->email));
        }

        return redirect('/sendAccountState/preview/' . $year)->with('success', 'Test envoyé à ' . $testEmail . ' (' . count($users) . ' email(s)).');
    }

    /**
     * @param Request $request
     */
    public function sendAccountState(Request $request)
    {

        $file = new Filesystem;
        //$file->cleanDirectory();
        $file->cleanDirectory('userAcountState');
        $allUsers = User::all();
        //$allUsers = User::where('email', 'yann@cymdev.com')->get();
        //dd($allUsers);
        foreach ($allUsers as $usersKey => $usersValue) {
            $transactions     = [];
            $selectedUser     = User::where('id', $usersValue->id)->first();
            $transactionsUser = transaction::where('idUser', $usersValue->id)
                ->orderBy('time', 'asc')
                ->orderBy('id', 'ASC')
                ->get();
            echo $selectedUser->name . "<br>";
            foreach ($transactionsUser as $key => $value) {
                $transactions[] = ['time' => date('d/m/Y H:i', $value->time), 'value' => number_format(($value->value / 100), 2), 'solde' => number_format(($value->solde / 100), 2), 'name' => $value->name, 'id' => $value->id, 'observation' => $value->observation];
            }

            $transactionType = [];

            $transactionTypeData = transactionType::all();
            foreach ($transactionTypeData as $key => $value) {
                $value->name       = $value->name . ' ' . date('Y');
                $transactionType[] = $value;
            }

            $aircraft            = aircraft::all();
            $sailplaneStartPrice = sailplaneStartPrice::all();
            $filename            = 'CVVT-' . str_replace(' ', '_', strtoupper($selectedUser->name)) . '_' . date('d-m-Y_H-i') . '.pdf';
            $pdf                 = Pdf::loadView('exportPdfAccount', ['transactions' => $transactions, 'selectedUser' => $selectedUser, 'transactionType' => $transactionType, 'aircrafts' => $aircraft, 'sailplaneStartPrices' => $sailplaneStartPrice]);
            $pdf->save('../storage/app/userAcountState/' . $filename);

            //Mail::to($selectedUser->email)->send(new sendAccount($selectedUser->name, 'userAcountState/'.$filename));
            Mail::to('yann@cymdev.com')->send(new sendAccount($selectedUser->name, 'userAcountState/' . $filename, $selectedUser->realAmountAccount, $selectedUser->email));
        }
        return 'OK!';
    }

    public function instruction()
    {
        $flightsData = flight::where('idInstructor', 0)->orWhere('idInstructor', null)->orderBy('flightTimestamp', 'ASC')->get();
        $flights     = [];
        foreach ($flightsData as $key => $value) {
            $flight              = [];
            $aircraft            = aircraft::find($value->aircraftId);
            $user                = User::find($value->idUser);
            $flight['id']        = $value->id;
            $flight['aircraft']  = $aircraft->name;
            $flight['pilot']     = $user->name;
            $flight['startDate'] = $value->takeOffTime;
            $flight['endDate']   = $value->landingTime;
            $flight['nbLanding'] = $value->landing;
            $flight['flighTime'] = $this->convertMinToHM($value->totalTime);
            if ($aircraft->type == 1) {
                $flight['startType'] = ' A ';
            } elseif ($aircraft->type == 2) {
                $startType           = sailplaneStartPrice::find($value->startType);
                $flight['startType'] = $startType->name;
            }

            if ($aircraft->type == 2) {
                $flight['motorTime'] = '';
            } elseif ($aircraft->type == 1) {
                $flight['motorTime'] = intval(($value->motorEndTime - $value->motorStartTime) * 100);
            }
            $flight['price'] = number_format(($value->value / 100), 2) . " €";
            $flights[]       = $flight;
        }

        return view('instructorFlights', ['flights' => $flights]);
    }

    /**
     * @param Request $request
     */
    public function addInstructeur(Request $request)
    {
        $flight               = flight::find($request->id);
        $flight->idInstructor = 16;
        $flight->save();
        return redirect('instruction');
    }

    public function tarifs()
    {
        $prices = aircraft::all();
        $startPrices = sailplaneStartPrice::all();
        $viTypes = parametre::where('nom', 'like', 'vi-%')
            ->where('nom', 'not like', 'vi_config-%')
            ->orderBy('nom')
            ->get();
        return view('admin.priceList', ['prices' => $prices, 'startPrices' => $startPrices, 'viTypes' => $viTypes]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAircraftPrice(Request $request, $id)
    {
        try {
            $aircraft = aircraft::findOrFail($id);
            
            // Debug temporaire pour voir les données reçues
            \Log::info('Données reçues:', $request->all());
            
            // Validation des données
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'base_price' => 'required|numeric|min:0',
                'motor_price' => 'required|numeric|min:0',
                'motor_price_type' => 'required|in:centieme,minutes,aucun',
                'min_price' => 'required|numeric|min:0',
                'actif' => 'nullable',
                'public' => 'nullable'
            ]);

            // Conversion des valeurs pour correspondre au modèle
            $motorPriceType = 0;
            switch($request->motor_price_type) {
                case 'centieme':
                    $motorPriceType = 1;
                    break;
                case 'minutes':
                    $motorPriceType = 2;
                    break;
                default:
                    $motorPriceType = 0;
                    break;
            }

            // Mise à jour des données
            $aircraft->name = $request->name;
            $aircraft->basePrice = $request->base_price;
            $aircraft->motorPrice = $request->motor_price;
            $aircraft->motorPriceType = $motorPriceType;
            $aircraft->minPrice = $request->min_price * 100; // Convertir en centimes
            $aircraft->actif = $request->has('actif') ? 1 : 0;
            $aircraft->public = $request->has('public') ? 1 : 0;
            
            $aircraft->save();
            
            return redirect('/tarifs')->with('success', 'Tarifs mis à jour avec succès');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = [];
            foreach ($e->errors() as $field => $errors) {
                $errorMessages[] = $field . ': ' . implode(', ', $errors);
            }
            return redirect('/tarifs')->with('error', 'Erreur de validation: ' . implode('; ', $errorMessages));
        } catch (\Exception $e) {
            return redirect('/tarifs')->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createAircraft(Request $request)
    {
        try {
            // Validation des données
            $validated = $request->validate([
                'type' => 'required|in:1,2',
                'name' => 'required|string|max:255',
                'register' => 'required|string|max:255',
                'base_price' => 'required|numeric|min:0',
                'motor_price' => 'required|numeric|min:0',
                'motor_price_type' => 'required|in:centieme,minutes,aucun',
                'min_price' => 'required|numeric|min:0',
                'actif' => 'nullable'
            ]);
            
            // Conversion des valeurs pour correspondre au modèle
            $motorPriceType = 0;
            switch($request->motor_price_type) {
                case 'centieme':
                    $motorPriceType = 1;
                    break;
                case 'minutes':
                    $motorPriceType = 2;
                    break;
                default:
                    $motorPriceType = 0;
                    break;
            }
            
            // Créer le nouvel aéronef
            $aircraft = new aircraft();
            $aircraft->type = $request->type;
            $aircraft->name = $request->name;
            $aircraft->register = $request->register;
            $aircraft->basePrice = $request->base_price;
            $aircraft->motorPrice = $request->motor_price;
            $aircraft->motorPriceType = $motorPriceType;
            $aircraft->minPrice = $request->min_price * 100; // Convertir en centimes
            $aircraft->actif = $request->has('actif') ? 1 : 0;
            
            $aircraft->save();
            
            return redirect('/tarifs')->with('success', 'Aéronef créé avec succès');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = [];
            foreach ($e->errors() as $field => $errors) {
                $errorMessages[] = $field . ': ' . implode(', ', $errors);
            }
            return redirect('/tarifs')->with('error', 'Erreur de validation: ' . implode('; ', $errorMessages));
        } catch (\Exception $e) {
            return redirect('/tarifs')->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStartPrice(Request $request, $id)
    {
        try {
            $startPrice = sailplaneStartPrice::findOrFail($id);
            
            // Validation des données
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'base_price' => 'required|numeric|min:0',
                'public' => 'nullable'
            ]);

            // Mise à jour des données
            $startPrice->name = $request->name;
            $startPrice->basePrice = $request->base_price * 100; // Convertir en centimes
            $startPrice->public = $request->has('public') ? 1 : 0;

            $startPrice->save();
            
            return redirect('/tarifs')->with('success', 'Moyen de mise en l\'air mis à jour avec succès');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = [];
            foreach ($e->errors() as $field => $errors) {
                $errorMessages[] = $field . ': ' . implode(', ', $errors);
            }
            return redirect('/tarifs')->with('error', 'Erreur de validation: ' . implode('; ', $errorMessages));
        } catch (\Exception $e) {
            return redirect('/tarifs')->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createStartPrice(Request $request)
    {
        try {
            // Validation des données
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'base_price' => 'required|numeric|min:0',
                'public' => 'nullable'
            ]);

            // Créer le nouveau moyen de mise en l'air
            $startPrice = new sailplaneStartPrice();
            $startPrice->name = $request->name;
            $startPrice->basePrice = $request->base_price * 100; // Convertir en centimes
            $startPrice->public = $request->has('public') ? 1 : 0;

            $startPrice->save();
            
            return redirect('/tarifs')->with('success', 'Moyen de mise en l\'air créé avec succès');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = [];
            foreach ($e->errors() as $field => $errors) {
                $errorMessages[] = $field . ': ' . implode(', ', $errors);
            }
            return redirect('/tarifs')->with('error', 'Erreur de validation: ' . implode('; ', $errorMessages));
        } catch (\Exception $e) {
            return redirect('/tarifs')->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function userState(Request $request)
    {
        $user          = User::find($request->user);
        $user->state   = ($request->state == 'true') ? 1 : 0;
        $messageReturn = ($user->state == 1) ? 'Actif' : 'Inactif';
        $user->save();
        return $messageReturn;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function aircraftState(Request $request)
    {
        $aircraft        = aircraft::find($request->aircraft);
        $aircraft->actif = ($request->state == 'true') ? 1 : 0;
        $messageReturn   = ($aircraft->actif == 1) ? 'Actif' : 'Inactif';
        $aircraft->save();
        return $messageReturn;
    }

    /**
     * @param Request $request
     */
    public function saisiePeriodique(Request $request)
    {
        $year  = (isset($request->year)) ? $request->year : date('Y');
        $users = User::where('id', '>', 0)->where('state', 1)->get();
        return view('admin.saisiePeriodique', ['users' => $users, 'year' => $year]);
    }

    /**
     * @param Request $request
     */
    public function saisiePeriodiqueEnregistrement(Request $request)
    {
        switch ($request->typeAdd) {
            case 'cotisation':
                $users = User::whereIn('id', array_keys($request->addCotisation))->get();
                //dd($users);

                foreach ($users as $user) {
                    $tr           = new transaction();
                    $tr->idUser   = $user->id;
                    $tr->name     = 'Cotisation ' . $request->year;
                    $tr->value    = -5000;
                    $tr->quantity = 1;
                    $tr->valid    = 1;
                    $tr->time     = (strtotime($request->year . '-01-01'));
                    $tr->year     = $request->year;
                    $tr->solde    = 0;
                    $tr->save();
                    $user->updateSolde();
                }
                break;

            case 'frais':
                foreach ($request->addDayFlightInvoice as $user => $NBJ) {
                    if ($NBJ > 0) {
                        $user         = User::find($user);
                        $tr           = new transaction();
                        $tr->idUser   = $user->id;
                        $tr->name     = 'Frais de fonctionement ' . $request->year;
                        $tr->value    = (-1500 * intval($NBJ));
                        $tr->quantity = 1;
                        $tr->valid    = 1;
                        $tr->time     = (strtotime($request->year . '-12-31'));
                        $tr->year     = $request->year;
                        $tr->solde    = 0;

                        $tr->save();
                        $user->updateSolde();
                    }
                }
                break;

            default:
                // code...
                break;
        }
        return back();
    }

    public function usersSendAccountNotificationPreview()
    {
        $users = User::all();
        $notifUsers = [];
        foreach ($users as $user) {
            if ($user->real_amount_account < 0 && $user->state == 1 && !$user->isAttr('user:technique')) {
                $notifUsers[] = $user;
            }
        }
        return view('admin.sendAccountNotificationPreview', ['users' => $notifUsers]);
    }

    public function usersSendAccountNotification()
    {
        User::sendAccountAlertNotification();
        return redirect('/usersList')->with('success', 'Emails envoyés à ' . count(User::all()->filter(fn($u) => $u->real_amount_account < 0 && $u->state == 1)) . ' utilisateur(s).');
    }

    public function usersSendAccountNotificationTest()
    {
        $testEmail = auth()->user()->email;
        $users     = User::all()->filter(fn($u) => $u->real_amount_account < 0 && $u->state == 1 && !$u->isAttr('user:technique'));

        $file = new Filesystem;
        $file->cleanDirectory('userAcountState');

        $aircraft            = aircraft::all();
        $sailplaneStartPrice = sailplaneStartPrice::all();
        $transactionType     = [];
        foreach (transactionType::all() as $value) {
            $value->name       = $value->name . ' ' . date('Y');
            $transactionType[] = $value;
        }

        foreach ($users as $user) {
            $transactionsUser = transaction::where('idUser', $user->id)->orderBy('time', 'asc')->orderBy('id', 'ASC')->get();
            $transactions = [];
            foreach ($transactionsUser as $value) {
                $transactions[] = ['time' => date('d/m/Y H:i', $value->time), 'value' => number_format($value->value / 100, 2), 'solde' => number_format($value->solde / 100, 2), 'name' => $value->name, 'id' => $value->id, 'observation' => $value->observation];
            }
            $filename = 'CVVT-' . str_replace(' ', '_', strtoupper($user->name)) . '_' . date('d-m-Y_H-i') . '.pdf';
            $pdf = Pdf::loadView('exportPdfAccount', ['transactions' => $transactions, 'selectedUser' => $user, 'transactionType' => $transactionType, 'aircrafts' => $aircraft, 'sailplaneStartPrices' => $sailplaneStartPrice]);
            $pdf->save('../storage/app/userAcountState/' . $filename);
            Mail::to($testEmail)->send(new sendAccount($user->name, 'userAcountState/' . $filename, $user->realAmountAccount, $user->email));
        }

        return redirect('/usersSendAccountNotification/preview')->with('success', 'Test envoyé à ' . $testEmail . ' (' . count($users) . ' email(s)).');
    }

    /**
     * @param Request $request
     */
    public function userMod(Request $request)
    {
        $user = User::find($request->id);
        return view('admin.userMod', ['user' => $user]);
    }

    /**
     * @param Request $request
     */
    public function saveUserMod(Request $request)
    {
        $user                = User::find($request->id);
        $user->name          = $request->name;
        $user->email         = $request->email;
        $user->licenceNumber = $request->licenceNumber;
        $user->isSupervisor  = $request->isSupervisor;

        // Si admin retiré, supprime en cascade tous les admin:*
        $wasAdmin = $user->isAdmin == 1;
        $isAdmin  = $request->has('isAdmin');
        if ($wasAdmin && !$isAdmin) {
            \App\Models\usersAttributes::where('userId', $user->id)
                ->where('attributeName', 'like', 'admin:%')
                ->delete();
        }
        $user->isAdmin = $isAdmin ? 1 : 0;

        $user->saveAttr($request->userState ?? []);

        // Attribut technique (exclu des emails débiteurs)
        \App\Models\usersAttributes::where('userId', $user->id)
            ->where('attributeName', 'user:technique')
            ->delete();
        if ($request->has('user_technique')) {
            $attr = new \App\Models\usersAttributes();
            $attr->userId = $user->id;
            $attr->attributeName = 'user:technique';
            $attr->save();
        }

        // Sauvegarde des permissions admin (uniquement si l'admin courant a le droit de les modifier)
        if ($isAdmin && \Illuminate\Support\Facades\Gate::allows('admin:rights')) {
            $perms = [];
            foreach (array_keys(\App\Models\usersAttributes::$userRights) as $right) {
                $inputKey = 'perm_' . str_replace(':', '_', $right);
                if ($request->has($inputKey)) {
                    $perms[] = $right;
                }
            }
            $user->savePermissions('admin', $perms);
        }

        $user->save();
        return view('admin.userMod', ['user' => $user]);
    }

    /**
     * @param Request $request
     */
    public function getAdminAccess(Request $request)
    {
        $user = User::find($request->id);
        return "Accès administrateur a usage unique : \n" . $user->name . " : " . $user->email . ' / ' . $user->admin_access;
    }

    /**
     * @param Request $request
     */
    public function towing(Request $request)
    {
        if (! isset($request->date)) {
            $lastFlightDay = flight::where('flightTimestamp', '>=', strtotime(date('Y-01-01 00:00:00')))->where('startType', 1)->whereNull('towingFlightId')->orderBy('flightTimestamp')->first();
            if (! is_null($lastFlightDay)) {
                return redirect('/towing?date=' . date('Y-m-d', $lastFlightDay->flightTimestamp));
            } else {
                return redirect('/towing?date=' . date('Y-m-d'));
            }

        }
        $flights = flight::where('startType', 1)->where('flightTimestamp', '>=', strtotime($request->date . ' 00:00:00'))->where('flightTimestamp', '<=', strtotime($request->date . ' 23:59:59'))->orderBy('flightTimestamp')->get();
        return view('admin.towing', ['flights' => $flights]);
    }

    public function gesasso()
    {
        return view('admin.importGesasso');
    }

    /**
     * @param Request $request
     */
    public function gesassofile(Request $request)
    {
        $file  = file_get_contents($request->file('planche')->getRealPath());
        $file  = mb_convert_encoding($file, 'UTF-8', 'UTF-8');
        $lines = explode("\n", $file);
        foreach ($lines as $key => $line) {
            //dd(str_getcsv($line, ';'));
            $lines[$key] = str_getcsv($line, ';');
        }
        $total     = 0;
        $existList = [];
        foreach ($lines as $key => $line) {
            if ($key > 0 && count($line) > 28) {
                $total += strtotime($line[0] . ' ' . $line[9] . ':00') - strtotime($line[0] . ' ' . $line[8] . ':00');
                if (Gesasso::existFlight($line)) {
                    $existList[] = $key;
                }
                $lines[$key][29] = gesasso::csvToUser($line);
                $lines[$key][30] = gesasso::csvToAircraft($line);
            }
        }
        $hours    = intval($total / 3600);
        $minutes  = intval(($total - ($hours * 3600)) / 60);
        $totalStr = $hours . ':' . $minutes;
        return view('admin.importGesasso', ['data' => $lines, 'totalStr' => $totalStr, 'existList' => $existList]);
    }

    /**
     * @param Request $request
     */
    public function saveDataGesasso(Request $request)
    {
        $resultImport = [];
        if (isset($request->import) && is_array($request->import)) {
            foreach ($request->import as $key => $value) {
                
                $gess           = json_decode($value);
                if (is_null(User::find($request->userPayId[$gess[31]]))) {
                    abort(500, 'Utilisateur non trouvé');
                }
                
                $flight         = Gesasso::exportToFlight($gess, $request->userPayId[$gess[31]], $request->startType[$gess[31]]);
                $transaction    = transaction::getFlightTransaction($flight);
                $resultImport[] = [$flight, $transaction];
                if ($flight->startType > 0) {
                    $gess[1]           = $gess[17];
                    $gess[3]           = $gess[19];
                    // $gess[15] est en centièmes d'heures → conversion en minutes
                    $minutesFromHundredths = \App\H::centiToMinutes($gess[15]);
                    if ($minutesFromHundredths <= 15) {
                        $gess[9]  = date('H:i', ($flight->flightTimestamp + (60 * 7)));
                        $gess[10] = '00:07';
                    } else {
                        $gess[9]  = date('H:i', ($flight->flightTimestamp + (60 * $minutesFromHundredths)));
                        $gess[10] = sprintf('00:%02d', $minutesFromHundredths);
                    }
                    $gess[14]          = '';
                    $gess[17]          = '';
                    $gess[19]          = '';
                    $towing            = Gesasso::exportToFlight($gess);
                    
                    $towing->userPayId = 24;
                    $transactionTowing = transaction::getFlightTransaction($towing);
                    $transactionTowing->save();
                    $towing->transactionID = $transactionTowing->id;
                    $towing->save();
                    transaction::add(24, 2800, 'remorquage', '', date('Y-m-d H:i', $flight->flightTimestamp));
                }

                $transaction->save();
                $flight->transactionID = $transaction->id;
                $flight->save();

            }
        }
        return view('admin.importGesasso', ['resultImport' => $resultImport]);
    }
}
