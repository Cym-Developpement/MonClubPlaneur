<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelloAssoPendingPayment extends Model
{
    protected $table = 'helloasso_pending_payments';

    protected $fillable = [
        'payment_id',
        'order_id',
        'checkout_intent_id',
        'amount',
        'payer_email',
        'payer_name',
        'installment_number',
        'webhook_data',
        'status',
        'attempts',
        'last_attempt_at',
        'processed_at',
        'error_message',
    ];

    protected $casts = [
        'webhook_data' => 'array',
        'last_attempt_at' => 'datetime',
        'processed_at' => 'datetime',
    ];
}
