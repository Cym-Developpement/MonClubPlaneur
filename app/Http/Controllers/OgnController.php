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
        if (isset($request->DATE)) {
            ognFlight::getDataFromApi('lfct', $request->DATE);
            $flights = ognFlight::where('date', $request->DATE)->first();
            $date = $request->DATE;
        } else {
            ognFlight::getDataFromApi('lfct', date('Y-m-d'));
            $flights = ognFlight::where('imported', 0)->orderBy('date', 'asc')->first();
            if (is_null($flights)) {
                $date = date('Y-m-d');
            } else {
                $date = $flights->date;
            }
        }
        $next     = date('Y-m-d', strtotime($date) + 87400);
        $previous = date('Y-m-d', strtotime($date) - 85400);

        $users      = User::where('state', 1)->orderBy('name')->get();
        $aircrafts  = aircraft::where('actif', 1)->orderBy('name')->get();
        $startTypes = sailplaneStartPrice::all();

        return view('ogn.planche', compact('flights', 'date', 'next', 'previous', 'users', 'aircrafts', 'startTypes'));
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
                aircraftId:   $ac->id,
                userId:       (int) $data['userId'],
                userPayId:    (int) $data['userPayId'],
                instructorId: (int) ($data['instructorId'] ?? 0),
                startTypeId:  (int) $data['startTypeId'],
                startTsp:     $startTsp,
                stopTsp:      $stopTsp,
                totalTime:    $totalTime,
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
                aircraftId:   $ac->id,
                userId:       (int) $data['userId'],
                userPayId:    (int) $data['userPayId'],
                instructorId: (int) ($data['instructorId'] ?? 0),
                startTypeId:  (int) $data['startTypeId'],
                startTsp:     $startTsp,
                stopTsp:      $stopTsp,
                totalTime:    $totalTime,
            );

            $this->saveFlight($f);
            $saved++;
        }

        if ($ognRecord && $saved > 0) {
            $ognRecord->imported = 1;
            $ognRecord->save();
        }

        return redirect('/planchesOgn/' . $DATE)
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
    ): flight {
        $f = new flight();
        $f->idUser          = $userId;
        $f->userPayId       = $userPayId;
        $f->idInstructor    = $instructorId;
        $f->aircraftId      = $aircraftId;
        $f->totalTime       = $totalTime;
        $f->takeOffTime     = date('d/m/Y H:i', $startTsp);
        $f->landingTime     = date('d/m/Y H:i', $stopTsp);
        $f->flightTimestamp = $startTsp;
        $f->landing         = 1;
        $f->startType       = $startTypeId;
        $f->motorStartTime  = 0;
        $f->motorEndTime    = 0;
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
