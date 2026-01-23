<?php

namespace App\Http\Controllers;

use App\Models\aircraft;
use App\Models\sailplaneStartPrice;
use App\Models\parametre;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    /**
     * Affiche la page publique des tarifs
     *
     * @return \Illuminate\View\View
     */
    public function tarifs()
    {
        $aircrafts = aircraft::where('actif', 1)->where('public', 1)->get();
        $startPrices = sailplaneStartPrice::all();
        $parametres = parametre::where('public', 1)->get();
        
        return view('public.tarifs', [
            'aircrafts' => $aircrafts,
            'startPrices' => $startPrices,
            'parametres' => $parametres
        ]);
    }
} 