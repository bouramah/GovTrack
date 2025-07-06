<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class ResetPasswordNotification extends BaseNotification
{
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe')
            ->line("Vous recevez cet e-mail car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.")
            ->action('Réinitialiser le mot de passe', $url)
            ->line('Ce lien expire dans :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')])
            ->line("Si vous n'êtes pas à l'origine de cette demande, aucune action n'est requise.");
    }

    /**
     * Génère l'URL de réinitialisation vers le frontend
     */
    protected function resetUrl($notifiable)
    {
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', env('APP_URL')));

        $query = http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return rtrim($frontendUrl, '/').'/reset-password?'.$query;
    }

    /**
     * Override pour Laravel 10 (method toMail)
     */
    public function toMail($notifiable)
    {
        $url = $this->resetUrl($notifiable);
        return $this->buildMailMessage($url);
    }
}
