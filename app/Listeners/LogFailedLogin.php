<?php

namespace App\Listeners;

use App\Helpers\AuditLog;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    public function handle(Failed $event): void
    {
        $identifier = $event->credentials['email'] ?? $event->credentials['name'] ?? '?';
        AuditLog::log('tentative de connexion échouée pour ' . $identifier);
    }
}
