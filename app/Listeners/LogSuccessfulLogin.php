<?php

namespace App\Listeners;

use App\Helpers\AuditLog;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        AuditLog::log('connexion de ' . $event->user->name);
    }
}
