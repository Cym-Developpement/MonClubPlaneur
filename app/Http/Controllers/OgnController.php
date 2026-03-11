<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\aircraft;
use App\Models\flight;
use App\Models\ognFlight;
use App\Models\sailplaneStartPrice;
use App\Models\transaction;
use App\Models\User;

class OgnController extends Controller
{
    public function import(Request $request)
    {
        ognFlight::getDataFromApi('lfct', $request->DATE);
        echo 'OK';
    }

    public function planches(Request $request)
    {
        $date = $request->DATE ?? date('Y-m-d');

        ognFlight::getDataFromApi('lfct', $date);
        $flights = ognFlight::where('date', $date)->first();

        $users      = User::where('state', 1)->orderBy('name')->get();
        $aircrafts  = aircraft::where('actif', 1)->orderBy('name')->get();
        $startTypes = sailplaneStartPrice::all();

        return view('ogn.planche', compact('flights', 'date', 'users', 'aircrafts', 'startTypes'));
    }

    public function save(Request $request, $DATE)
    {
        $ognRecord = ognFlight::where('date', $DATE)->first();
        $saved = 0;

        // ── Vols OGN cochés ──────────────────────────────────────────────
        foreach (($request->input('flights', [])) as $data) {
            if (!isset($data['import'])) continue;

            $ac = aircraft::find($data['aircraftId']);
            if (!$ac) continue;

            $startTsp  = (int) $data['start_tsp'];
            $stopTsp   = (int) $data['stop_tsp'];
            $totalTime = (int) round(($stopTsp - $startTsp) / 60);

            $f = $this->buildFlight(
                aircraftId:      $ac->id,
                userId:          (int) $data['userId'],
                userPayId:       (int) $data['userPayId'],
                instructorId:    (int) ($data['instructorId'] ?? 0),
                startTypeId:     (int) $data['startTypeId'],
                startTsp:        $startTsp,
                stopTsp:         $stopTsp,
                totalTime:       $totalTime,
                motorStartTime:  (float) ($data['motorStartTime'] ?? 0),
                motorEndTime:    (float) ($data['motorEndTime'] ?? 0),
            );

            $this->saveFlight($f);
            $saved++;
        }

        // ── Vols ajoutés manuellement ─────────────────────────────────────
        foreach (($request->input('manualFlights', [])) as $data) {
            if (!isset($data['import'])) continue;

            $ac = aircraft::find($data['aircraftId']);
            if (!$ac) continue;

            $startTsp  = strtotime($DATE . ' ' . $data['takeOffTime'] . ':00');
            $stopTsp   = strtotime($DATE . ' ' . $data['landingTime']  . ':00');
            if ($stopTsp <= $startTsp) $stopTsp += 86400; // passage minuit
            $totalTime = (int) round(($stopTsp - $startTsp) / 60);

            $f = $this->buildFlight(
                aircraftId:      $ac->id,
                userId:          (int) $data['userId'],
                userPayId:       (int) $data['userPayId'],
                instructorId:    (int) ($data['instructorId'] ?? 0),
                startTypeId:     (int) $data['startTypeId'],
                startTsp:        $startTsp,
                stopTsp:         $stopTsp,
                totalTime:       $totalTime,
                motorStartTime:  (float) ($data['motorStartTime'] ?? 0),
                motorEndTime:    (float) ($data['motorEndTime'] ?? 0),
            );

            $this->saveFlight($f);
            $saved++;
        }

        if ($ognRecord && $saved > 0) {
            $ognRecord->imported = 1;
            $ognRecord->save();
        }

        return redirect('/planchesOgn?DATE=' . $DATE)
            ->with('status', $saved . ' vol(s) enregistré(s).');
    }

    public function ignore(Request $request)
    {
        $ogn = ognFlight::find($request->ID);
        $ogn->imported = 2;
        $ogn->save();
        return back();
    }

    // ── Helpers privés ────────────────────────────────────────────────────

    private function buildFlight(
        int $aircraftId,
        int $userId,
        int $userPayId,
        int $instructorId,
        int $startTypeId,
        int $startTsp,
        int $stopTsp,
        int $totalTime,
        float $motorStartTime = 0.0,
        float $motorEndTime   = 0.0,
    ): flight {
        $f = new flight();
        $f->idUser           = $userId;
        $f->userPayId        = $userPayId;
        $f->idInstructor     = $instructorId;
        $f->aircraftId       = $aircraftId;
        $f->totalTime        = $totalTime;
        $f->takeOffTime      = date('d/m/Y H:i', $startTsp);
        $f->landingTime      = date('d/m/Y H:i', $stopTsp);
        $f->flightTimestamp  = $startTsp;
        $f->landing          = 1;
        $f->startType        = $startTypeId;
        $f->motorStartTime   = $motorStartTime;
        $f->motorEndTime     = $motorEndTime;
        $f->airportStartCode = 'lfct';
        $f->airportEndCode   = 'lfct';
        $f->pilotState       = '';
        $f->value            = 0;
        return $f;
    }

    private function saveFlight(flight $f): void
    {
        $tx = transaction::getFlightTransaction($f);
        $tx->save();

        $f->transactionID = $tx->id;
        $f->save();

        User::find($f->userPayId)?->updateSolde();
    }
}
