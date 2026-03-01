<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AuditLog
{
    const CREATED = 'créé';
    const UPDATED = 'modifié';
    const DELETED = 'supprimé';

    /**
     * Enregistre une action depuis un observer de modèle.
     *
     * Utilise l'attribut `audit_line` du modèle s'il existe pour enrichir le log.
     * Le modèle peut définir un attribut `audit_name` pour personnaliser son nom dans les logs.
     *
     * Exemple de résultat : (Yann Challet) transaction créée "Jean Dupont 17€"
     *
     * @param Model  $model Le modèle concerné
     * @param string $type  Type de modification : AuditLog::CREATED, UPDATED ou DELETED
     */
    public static function observe(Model $model, string $type): void
    {
        $user     = auth()->user();
        $userName = $user ? $user->name : 'Système';

        $modelName = $model->audit_name ?? strtolower(class_basename($model));

        $line = "({$userName}) {$modelName} #{$model->id} {$type}";

        $auditLine = $model->audit_line ?? null;
        if ($auditLine !== null && $auditLine !== '') {
            $line .= " \"{$auditLine}\"";
        }

        Log::channel('audit')->info($line);
    }

    /**
     * Enregistre un message libre dans le journal d'audit.
     *
     * @param string $message Texte décrivant l'action
     */
    public static function log(string $message): void
    {
        $user     = auth()->user();
        $userName = $user ? $user->name : 'Système';

        Log::channel('audit')->info("({$userName}) {$message}");
    }
}
