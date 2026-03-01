<?php

namespace App\Observers;

use App\Helpers\AuditLog;
use App\Models\transaction;

class TransactionObserver
{
    public function created(transaction $model): void
    {
        AuditLog::observe($model, AuditLog::CREATED);
    }

    public function updated(transaction $model): void
    {
        AuditLog::observe($model, AuditLog::UPDATED);
    }

    public function deleted(transaction $model): void
    {
        AuditLog::observe($model, AuditLog::DELETED);
    }
}
