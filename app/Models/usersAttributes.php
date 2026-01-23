<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant les attributs additionnels des utilisateurs
 * 
 * @property int $id Identifiant unique de l'attribut
 * @property int $userId ID de l'utilisateur concerné
 * @property string $attribute Nom de l'attribut
 * @property string|null $value Valeur de l'attribut
 * @property \DateTime $created_at Date de création
 * @property \DateTime $updated_at Date de dernière modification
 */
class usersAttributes extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'usersAttribute';
}
