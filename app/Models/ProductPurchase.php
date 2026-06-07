<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Achat d'un produit public via HelloAsso.
 *
 * @property int $id
 * @property int|null $product_id
 * @property string $product_title    Snapshot du titre
 * @property int $amount_cts          Snapshot du montant en centimes
 * @property string $payer_firstname
 * @property string $payer_lastname
 * @property string $payer_email
 * @property string|null $message
 * @property string|null $helloasso_checkout_intent_id
 * @property string|null $helloasso_order_id
 * @property string|null $helloasso_payment_id
 * @property string $status           pending | paid | failed
 * @property \Carbon\Carbon|null $paid_at
 * @property array|null $webhook_data
 * @property string|null $error_message
 */
class ProductPurchase extends Model
{
    protected $table = 'product_purchases';

    protected $fillable = [
        'product_id',
        'product_title',
        'amount_cts',
        'payer_firstname',
        'payer_lastname',
        'payer_email',
        'message',
        'helloasso_checkout_intent_id',
        'helloasso_order_id',
        'helloasso_payment_id',
        'status',
        'paid_at',
        'webhook_data',
        'error_message',
    ];

    protected $casts = [
        'amount_cts'   => 'integer',
        'paid_at'      => 'datetime',
        'webhook_data' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getAmountEurAttribute(): string
    {
        return number_format($this->amount_cts / 100, 2, ',', ' ') . ' €';
    }

    public function getPayerNameAttribute(): string
    {
        return trim($this->payer_firstname . ' ' . $this->payer_lastname);
    }
}
