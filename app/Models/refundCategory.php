<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant une catégorie de remboursement dans le système
 * 
 * @property int $id Identifiant unique de la catégorie
 * @property string $name Nom de la catégorie
 * @property string|null $description Description de la catégorie
 * @property \DateTime $created_at Date de création
 * @property \DateTime $updated_at Date de dernière modification
 */
class refundCategory extends Model
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'refundCategory';
}
