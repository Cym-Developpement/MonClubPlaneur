<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\usersData;
use App\Models\refund;
use App\Models\refundCategory;
use App\Models\transaction;
use App\Models\transactionType;

class RefundController extends Controller
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

   	public function ajoutDepense(Request $request)
   	{
   		$refund = new refund();
   		$refund->idUser = $request->idUser;
   		$refund->time = time();
   		$refund->amount = intval($request->amount*100);
   		$refund->refundType = $request->type;
   		$refund->category = $request->categorie;
   		$refund->file = $request->file('facture')->store('factures');
   		$refund->observation = $request->observation;
   		$refund->save();


   		if ($refund->refundType == 0) {
   			$transaction = new transaction();
   			$transaction->idUser = $refund->idUser;
   			$transaction->name = 'Remboursement achat';
   			$transaction->value = $refund->amount;
   			$transaction->quantity = 1;
   			$transaction->valid = 0;
   			$transaction->year = date('Y');
   			$transaction->time = time();
   			$transaction->refundId = $refund->id;
   			$transaction->save();
   			$user = User::find($refund->idUser);
   			$user->updateSolde();
   		}



   		return redirect('home');
   	}

	public function facture(Request $request)
   	{
   		$refund = refund::find($request->id);
   		return Storage::download($refund->file, $refund->filename);
   		//return Storage::download($refund->file);
   	}
}
