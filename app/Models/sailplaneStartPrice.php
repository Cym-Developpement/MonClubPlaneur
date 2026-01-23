<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant le prix de départ d'un planeur
 * 
 * @property int $id Identifiant unique du tarif
 * @property float $price Prix du départ
 * @property int $type Type de départ (remorquage, treuil, etc.)
 * @property int $year Année d'application du tarif
 * @property \DateTime $created_at Date de création
 * @property \DateTime $updated_at Date de dernière modification
 */
class sailplaneStartPrice extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sailplaneStartPrice';
}
