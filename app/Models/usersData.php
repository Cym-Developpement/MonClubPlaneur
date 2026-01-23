<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant les données complémentaires des utilisateurs
 * 
 * @property int $id Identifiant unique de l'entrée
 * @property int $userId ID de l'utilisateur concerné
 * @property string $key Clé de la donnée
 * @property string $value Valeur de la donnée
 * @property int $year Année d'application
 * @property \DateTime $created_at Date de création
 * @property \DateTime $updated_at Date de dernière modification
 */
class usersData extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'usersData';
}
