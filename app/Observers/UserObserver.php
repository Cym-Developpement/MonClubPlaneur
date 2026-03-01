<?php

namespace App\Observers;

use App\Helpers\AuditLog;
use App\Models\User;

class UserObserver
{
    public function created(User $model): void
    {
        AuditLog::observe($model, AuditLog::CREATED);
    }

    public function updated(User $model): void
    {
        AuditLog::observe($model, AuditLog::UPDATED);
    }

    public function deleted(User $model): void
    {
        AuditLog::observe($model, AuditLog::DELETED);
    }
}
