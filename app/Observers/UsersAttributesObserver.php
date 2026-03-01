<?php

namespace App\Observers;

use App\Helpers\AuditLog;
use App\Models\usersAttributes;

class UsersAttributesObserver
{
    public function created(usersAttributes $model): void
    {
        AuditLog::observe($model, AuditLog::CREATED);
    }

    public function deleted(usersAttributes $model): void
    {
        AuditLog::observe($model, AuditLog::DELETED);
    }
}
