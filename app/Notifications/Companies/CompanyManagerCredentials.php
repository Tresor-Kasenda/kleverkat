<?php

declare(strict_types=1);

namespace App\Notifications\Companies;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompanyManagerCredentials extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Company $company,
        public string $temporaryPassword,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Vos accès gestionnaire pour :company', ['company' => $this->company->name]))
            ->greeting(__('Bonjour :name', ['name' => $notifiable->name]))
            ->line(__('Votre espace gestionnaire pour l\'entreprise :company est prêt.', ['company' => $this->company->name]))
            ->line(__('Email : :email', ['email' => $notifiable->email]))
            ->line(__('Mot de passe temporaire : :password', ['password' => $this->temporaryPassword]))
            ->action(__('Se connecter'), route('login'))
            ->line(__('Pensez à changer votre mot de passe après votre première connexion.'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'company_id' => $this->company->id,
            'company_name' => $this->company->name,
        ];
    }
}
