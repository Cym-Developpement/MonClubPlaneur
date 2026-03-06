<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant un bon de vol d'initiation
 *
 * @property int $id
 * @property string $code Code unique 8 caractères généré automatiquement
 * @property string $source 'admin' | 'offert' | 'helloasso'
 * @property string|null $type Label du type de vol
 * @property int|null $prix_cts Prix en centimes au moment de création
 * @property bool $actif Vrai quand le bénéficiaire a rempli ses informations
 * @property bool $realise Vol réalisé
 * @property \Carbon\Carbon|null $date_realisation
 * @property string|null $nom
 * @property string|null $prenom
 * @property \Carbon\Carbon|null $date_naissance
 * @property string|null $adresse
 * @property string|null $cp
 * @property string|null $ville
 * @property string|null $email
 * @property string|null $telephone
 * @property string|null $notes
 * @property string|null $helloasso_order_id
 * @property string|null $helloasso_payment_id
 */
class VolInitiation extends Model
{
    protected $table = 'vol_initiations';

    protected $fillable = [
        'code',
        'source',
        'type',
        'prix_cts',
        'actif',
        'realise',
        'date_realisation',
        'nom',
        'prenom',
        'date_naissance',
        'adresse',
        'cp',
        'ville',
        'email',
        'telephone',
        'notes',
        'helloasso_order_id',
        'helloasso_payment_id',
    ];

    protected $casts = [
        'actif'            => 'boolean',
        'realise'          => 'boolean',
        'date_naissance'   => 'date',
        'date_realisation' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (VolInitiation $vi) {
            if (empty($vi->code)) {
                $chars = '0123456789abcdefghijklmnopqrstuvwxyz';
                do {
                    $code = '';
                    for ($i = 0; $i < 8; $i++) {
                        $code .= $chars[random_int(0, strlen($chars) - 1)];
                    }
                } while (self::where('code', $code)->exists());

                $vi->code = $code;
            }
        });
    }

    /**
     * Retourne le prix formaté (ex: "50,00 €") ou "Offert"
     */
    public function getPrixEurAttribute(): string
    {
        if (is_null($this->prix_cts) || $this->prix_cts === 0) {
            return 'Offert';
        }

        return number_format($this->prix_cts / 100, 2, ',', ' ') . ' €';
    }
}
