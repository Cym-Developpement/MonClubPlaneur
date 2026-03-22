<?php

namespace App\Notifications;

use App\Models\parametre;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $nomCourt   = parametre::getValue('club-nom_court', '');
        $nomComplet = parametre::getValue('club-nom_complet', '');
        $logo       = parametre::getValue('club-logo', '');
        $expire     = config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe')
            ->view('emails.reset_password', [
                'url'         => $url,
                'userName'    => $notifiable->name,
                'nomCourt'    => $nomCourt,
                'nomComplet'  => $nomComplet,
                'logo'        => $logo,
                'expire'      => $expire,
            ]);
    }
}
