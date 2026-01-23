<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant une tâche dans la todolist partagée
 * 
 * @property int $id Identifiant unique de la tâche
 * @property string $title Titre de la tâche
 * @property string $description Description détaillée de la tâche
 * @property int $created_by ID de l'utilisateur qui a créé la tâche
 * @property int $assigned_to ID de l'utilisateur assigné à la tâche
 * @property string $status Statut de la tâche (pending, in_progress, completed)
 * @property string $priority Priorité de la tâche (low, medium, high)
 * @property \DateTime $due_date Date limite de la tâche
 * @property \DateTime $completed_at Date de completion de la tâche
 * @property int $completed_by ID de l'utilisateur qui a complété la tâche
 * @property \DateTime $created_at Date de création
 * @property \DateTime $updated_at Date de dernière modification
 */
class todolist extends Model
{
    /**
     * Le nom de la table associée au modèle.
     *
     * @var string
     */
    protected $table = 'todolist';

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'created_by',
        'assigned_to',
        'status',
        'priority',
        'due_date',
        'completed_at',
        'completed_by'
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array
     */
    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relation avec l'utilisateur qui a créé la tâche
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec l'utilisateur assigné à la tâche
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Relation avec l'utilisateur qui a complété la tâche
     */
    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Scope pour les tâches en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope pour les tâches en cours
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope pour les tâches complétées
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope pour les tâches assignées à un utilisateur
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope pour les tâches créées par un utilisateur
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Vérifie si la tâche est en retard
     */
    public function isOverdue()
    {
        return $this->due_date && $this->due_date < now() && $this->status !== 'completed';
    }

    /**
     * Marque la tâche comme complétée
     */
    public function markAsCompleted($userId)
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => $userId
        ]);
    }

    /**
     * Marque la tâche comme en cours
     */
    public function markAsInProgress()
    {
        $this->update([
            'status' => 'in_progress',
            'completed_at' => null,
            'completed_by' => null
        ]);
    }

    /**
     * Marque la tâche comme en attente
     */
    public function markAsPending()
    {
        $this->update([
            'status' => 'pending',
            'completed_at' => null,
            'completed_by' => null
        ]);
    }

    /**
     * Retourne la couleur CSS selon la priorité
     */
    public function getPriorityColor()
    {
        switch ($this->priority) {
            case 'high':
                return 'danger';
            case 'medium':
                return 'warning';
            case 'low':
                return 'success';
            default:
                return 'secondary';
        }
    }

    /**
     * Retourne la couleur CSS selon le statut
     */
    public function getStatusColor()
    {
        switch ($this->status) {
            case 'completed':
                return 'success';
            case 'in_progress':
                return 'primary';
            case 'pending':
                return 'secondary';
            default:
                return 'secondary';
        }
    }
}