<?php

namespace App\Listeners;

use App\Helpers\AuditLog;
use Illuminate\Auth\Events\PasswordReset;

class LogPasswordReset
{
    public function handle(PasswordReset $event): void
    {
        AuditLog::log('réinitialisation du mot de passe de ' . $event->user->name);
    }
}
