<?php

declare(strict_types=1);

namespace App\Notifications\Companies;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompanyAssignedManager extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Company $company,
        public bool $isNew = false,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject(__('Vous avez été désigné gestionnaire de :company', ['company' => $this->company->name]))
            ->greeting(__('Bonjour :name', ['name' => $notifiable->name]));

        if ($this->isNew) {
            $message->line(__('Vous avez été désigné gestionnaire de l’entreprise :company.', ['company' => $this->company->name]));
        } else {
            $message->line(__('Votre rôle de gestionnaire a été mis à jour pour l’entreprise :company.', ['company' => $this->company->name]));
        }

        $message
            ->line(__('En tant que gestionnaire, vous serez le point de contact principal pour cette entreprise.'))
            ->action(__('Voir l’entreprise'), route('company.profile', $this->company))
            ->line(__('Merci de votre engagement.'));

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
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
