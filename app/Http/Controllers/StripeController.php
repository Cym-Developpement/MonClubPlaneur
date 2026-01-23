<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\User;
use App\Models\transaction;

class StripeController extends Controller
{
    public function completed(Request $request)
    {
        $payload = @file_get_contents('php://input');
        
        $event = null;
        
        try {
            $event = \Stripe\Event::constructFrom(
                json_decode($payload, true)
            );
            if ($event['type'] == 'checkout.session.completed') {
                file_put_contents('userid.txt', $event->data->object->client_reference_id);
                $user = User::find(intval($event->data->object->client_reference_id));
                $idTrStripe = $event->id;
                if (transaction::where('data', $idTrStripe)->exists()) {
                    return;
                } else {

                    $transaction = new transaction();
                    $transaction->idUser = $user->id;
                    $transaction->name = 'Paiement carte bancaire';
                    $transaction->value = $event->data->object->amount_total;
                    $transaction->quantity = 1;
                    $transaction->valid = 1;
                    $transaction->solde = 0.0;
                    $transaction->time = time();
                    $transaction->year = date('Y');
                    $transaction->data = $idTrStripe;
                    $transaction->save();
                    $transaction->observation = 'N° de paiement : '.$transaction->id;
                    $transaction->save();
                    $user->updateSolde();
                }
            }
            
            Storage::disk('local')->put('STRIPE/'.$event->id.'.txt', json_encode($event));
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        }
    }
}
