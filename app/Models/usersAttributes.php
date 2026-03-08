<?php

namespace App\Models;

use App\Models\User;
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

    public function getAuditNameAttribute(): string
    {
        return 'attribut utilisateur';
    }

    public function getAuditLineAttribute(): string
    {
        $user     = User::find($this->userId);
        $userName = $user ? $user->name : "utilisateur #{$this->userId}";
        return "{$userName} - {$this->attributeName}";
    }

    /**
     * Droits d'administration disponibles.
     * Clé = identifiant de permission (admin:<key>), valeur = [name, description]
     *
     * @var array<string, array{name: string, description: string}>
     */
    public static array $userRights = [
        'admin:super' => [
            'name'        => 'Super administrateur',
            'description' => 'Accès total à toutes les sections, supplante tous les autres droits',
        ],
        'admin:rights' => [
            'name'        => 'Gestion des droits',
            'description' => 'Modification des droits et permissions des utilisateurs',
        ],
        'admin:saisie' => [
            'name'        => 'Saisie & Imports',
            'description' => 'Saisie de vols, import GESASSO, planches OGN, saisie périodique',
        ],
        'admin:users' => [
            'name'        => 'Gestion des utilisateurs',
            'description' => 'Création, modification et liste des utilisateurs',
        ],
        'admin:transactions' => [
            'name'        => 'Transactions',
            'description' => 'Validation et suppression des transactions en attente',
        ],
        'admin:flights' => [
            'name'        => 'Carnets de vol & Remorquage',
            'description' => 'Carnet de route appareil, carnet de vol pilote, remorquage',
        ],
        'admin:data' => [
            'name'        => 'Contrôle des données',
            'description' => 'Vérification et mise à jour de la base de données',
        ],
        'admin:tarifs' => [
            'name'        => 'Tarifs',
            'description' => 'Gestion des tarifs aéronefs et moyens de mise en l\'air',
        ],
        'admin:instruction' => [
            'name'        => 'Instruction',
            'description' => 'Gestion des instructeurs et des élèves',
        ],
        'admin:backups' => [
            'name'        => 'Sauvegardes',
            'description' => 'Création, téléchargement et suppression des sauvegardes',
        ],
        'admin:audit' => [
            'name'        => 'Journal d\'audit',
            'description' => 'Consultation des logs d\'activité de l\'application',
        ],
        'admin:vi' => [
            'name'        => 'Vols d\'initiation',
            'description' => 'Gestion des bons de vol d\'initiation',
        ],
        'admin:export' => [
            'name'        => 'Export CSV',
            'description' => 'Export CSV configurable des utilisateurs',
        ],
    ];
}
