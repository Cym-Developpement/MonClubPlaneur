<?php

namespace App\Observers;

use App\Helpers\AuditLog;
use App\Models\flight;

class FlightObserver
{
    public function created(flight $model): void
    {
        AuditLog::observe($model, AuditLog::CREATED);
    }

    public function updated(flight $model): void
    {
        AuditLog::observe($model, AuditLog::UPDATED);
    }

    public function deleted(flight $model): void
    {
        AuditLog::observe($model, AuditLog::DELETED);
    }
}
