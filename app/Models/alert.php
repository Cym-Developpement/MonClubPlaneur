<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant une alerte dans le système
 * 
 * @property int $id Identifiant unique de l'alerte
 * @property string $message Message de l'alerte
 * @property string $type Type d'alerte
 * @property \DateTime $created_at Date de création
 * @property \DateTime $updated_at Date de dernière modification
 */
class alert extends Model
{
    //
}
