<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant une journée de vol dans le système
 * 
 * @property int $id Identifiant unique de la journée
 * @property string $date Date de la journée
 * @property bool $isOpen Indique si la journée est ouverte
 * @property int $userId ID de l'utilisateur responsable
 * @property \DateTime $created_at Date de création
 * @property \DateTime $updated_at Date de dernière modification
 */
class flightDay extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'flightDay';
}
