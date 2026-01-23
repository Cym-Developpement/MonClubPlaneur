<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ognFlight;

class OgnController extends Controller
{
    //

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
        $next = date('Y-m-d', strtotime($date)+87400);
        $previous = date('Y-m-d', strtotime($date)-85400);
        return view('ogn.planche', ['flights' => $flights, 'date' => $date, 'next' => $next, 'previous' => $previous]);
    }

    public function ignore(Request $request)
    {
        $ogn = ognFlight::find($request->ID);
        $ogn->imported = 2;
        $ogn->save();
        return back();
    }
}
